-- Dashboard PGTrek materialized views (connection: pgsql6)
--
-- These replace the pre-existing vwm_*/vw_*_rev2 views for this dashboard.
-- Reasons for the rebuild (see conversation history for full investigation):
--   1. vwm_vendor_duration_detail_fix computed "duration" as MAX(last_seen) -
--      MIN(last_seen) per time_range — i.e. first-to-last-ping span, not
--      summed presence time. Any gap in detections got counted as if the
--      person was continuously present, inflating actual duration (could
--      exceed 100% of target for no real reason).
--   2. Point-completion target counting (my first rebuild attempt) double-
--      counted gateway checkpoints across time-ranges — the correct model
--      (confirmed from vw_vendor_log_total_target_activity_rev2's own SQL)
--      is COUNT(DISTINCT time_range), not COUNT(DISTINCT gateway).
--   3. Naively grouping live_presence_log pings by "consecutive same
--      gateway" (like the original mega-query did) merges pings across
--      *separate* scheduled windows that happen to reuse the same physical
--      gateway (e.g. StandBy 08:00-09:30 and StandBy 10:00-14:15 both on
--      gateway 1366), so one window "steals" all the credit. Fixed by
--      matching each individual ping to its window first (time+gateway),
--      then reconstructing continuous-presence sessions *within* that
--      window only (gap > 180s = new session, i.e. a real absence).
--   4. Alert Point: ms_alert.alert_reasonid is a human-assigned
--      justification code, not a direct classification. The correct rule
--      (reverse-engineered from vwm_alert_reason_detail's actual view
--      definition): unreviewed alerts (alert_reasonid IS NULL) with
--      alerttype LATE/LEFT_EARLY/GATEWAY_DOWN default to
--      Negligence/Negligence/Technical respectively; reviewed alerts are
--      classified by whatever reason the reviewer picked (via
--      ct_alert_reason).
--   5. Personnel/Absent Discipline sourced from ct_ms_track_sc for
--      consistency with Point/Time — note this uses a different
--      schedule-to-calendar join path than the old vwm_work_day_total, so
--      roster counts won't match it exactly (documented judgment call).
--
-- Refreshed by: php artisan pgtrek:refresh-views (App\Console\Commands\PgTrekRefreshViews)
-- Scheduled: every 30 minutes (see app/Console/Kernel.php)

-- ============================================================
-- 1. Personnel / Absent Discipline — per employee per scheduled day
-- ============================================================
DROP MATERIALIZED VIEW IF EXISTS pgtrek_personnel_daily;

CREATE MATERIALIZED VIEW pgtrek_personnel_daily AS
WITH sched AS (
    SELECT DISTINCT employeeid, vendor_name, siteid, date_calendar
    FROM ct_ms_track_sc
    WHERE status = 'A'
),
actual_days AS (
    SELECT DISTINCT employee_id AS employeeid, last_seen::date AS date_calendar
    FROM live_presence_log
    UNION
    SELECT DISTINCT employeeid, detected_at::date
    FROM ms_alert
)
SELECT s.employeeid, s.vendor_name, s.siteid, s.date_calendar,
       (ad.date_calendar IS NOT NULL) AS logged
FROM sched s
LEFT JOIN actual_days ad ON ad.employeeid = s.employeeid AND ad.date_calendar = s.date_calendar;

CREATE UNIQUE INDEX ON pgtrek_personnel_daily (employeeid, vendor_name, siteid, date_calendar);

-- ============================================================
-- 2. Alert Point — per employee per day per classified reason
-- ============================================================
DROP MATERIALIZED VIEW IF EXISTS pgtrek_alert_point_daily;

CREATE MATERIALIZED VIEW pgtrek_alert_point_daily AS
WITH classified AS (
    SELECT
        a.employeeid, a.site AS siteid, a.detected_at::date AS date,
        CASE
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'LATE' THEN 'Quality of Work Performance'
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'LEFT_EARLY' THEN 'Quality of Work Performance'
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'GATEWAY_DOWN' THEN 'Quality of Work Performance'
            ELSE car.aspect
        END AS aspect,
        CASE
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'LATE' THEN 'NEGLIGANCE ALERT'
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'LEFT_EARLY' THEN 'NEGLIGANCE ALERT'
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'GATEWAY_DOWN' THEN 'TECHNICAL PROBLEM ALERT'
            ELSE car.sub_aspect
        END AS sub_aspect,
        CASE
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'LATE' THEN 'Late'
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'LEFT_EARLY' THEN 'Out Of Area'
            WHEN a.alert_reasonid IS NULL AND a.alerttype = 'GATEWAY_DOWN' THEN 'Tech. Problem - Gateway Error'
            ELSE car.reason_name
        END AS reason_name
    FROM ms_alert a
    LEFT JOIN ct_alert_reason car ON car.reason_id = a.alert_reasonid
)
SELECT c.employeeid, c.siteid, v.vendor_name, c.date, c.aspect, c.sub_aspect, c.reason_name, COUNT(*) AS qty
FROM classified c
LEFT JOIN ms_employee e ON e.employee_id = c.employeeid
LEFT JOIN ms_vendor v ON e.vendor_id::integer = v.id
WHERE c.aspect = 'Quality of Work Performance'
GROUP BY c.employeeid, c.siteid, v.vendor_name, c.date, c.aspect, c.sub_aspect, c.reason_name;

CREATE UNIQUE INDEX ON pgtrek_alert_point_daily (employeeid, date, sub_aspect, reason_name);

-- ============================================================
-- 3. Point Completion + Time Implement — per employee per activity per day
-- ============================================================
DROP MATERIALIZED VIEW IF EXISTS pgtrek_point_time_daily;

CREATE MATERIALIZED VIEW pgtrek_point_time_daily AS
WITH master AS (
    SELECT DISTINCT employeeid, vendor_name, siteid, activity, time_range, date_calendar,
           duration_minutes::numeric AS duration_minutes
    FROM ct_ms_track_sc
    WHERE status = 'A' AND activity IS NOT NULL AND upper(trim(activity)) <> 'BREAK'
),
target AS (
    SELECT employeeid, vendor_name, siteid, activity, date_calendar,
           COUNT(DISTINCT time_range) AS target_count,
           SUM(duration_minutes) AS target_minutes
    FROM master
    GROUP BY employeeid, vendor_name, siteid, activity, date_calendar
),
master_full AS (
    SELECT DISTINCT employeeid, trackid, id_gateway, activity, time_range, date_calendar,
        (date_calendar + start_time::time) AS win_start,
        CASE WHEN end_time::time < start_time::time
             THEN date_calendar + end_time::time + INTERVAL '1 day'
             ELSE date_calendar + end_time::time END AS win_end
    FROM ct_ms_track_sc
    WHERE status = 'A' AND activity IS NOT NULL AND upper(trim(activity)) <> 'BREAK'
),
matched_pings AS (
    -- Match each individual ping (not pre-grouped intervals!) to the one
    -- master window it falls inside, by time + gateway. Matching pre-grouped
    -- "consecutive same gateway" intervals instead would bleed pings across
    -- separate windows sharing a gateway — see note #3 above.
    SELECT p.last_seen, mf.activity, mf.time_range, mf.date_calendar, mf.employeeid
    FROM live_presence_log p
    JOIN LATERAL (
        SELECT mf2.activity, mf2.time_range, mf2.date_calendar, mf2.employeeid
        FROM master_full mf2
        WHERE mf2.employeeid = p.employee_id
          AND mf2.trackid = p.track_id
          AND (mf2.id_gateway = p.gateway_id OR mf2.id_gateway IS NULL)
          AND p.last_seen >= mf2.win_start AND p.last_seen < mf2.win_end
        ORDER BY CASE WHEN mf2.id_gateway = p.gateway_id THEN 0 ELSE 1 END
        LIMIT 1
    ) mf ON TRUE
),
gapped AS (
    SELECT *,
        EXTRACT(EPOCH FROM (last_seen - LAG(last_seen) OVER (
            PARTITION BY employeeid, activity, time_range, date_calendar ORDER BY last_seen
        ))) AS gap_seconds
    FROM matched_pings
),
sessioned AS (
    -- Reconstruct continuous-presence sessions: pings are heartbeats every
    -- ~30-90s observed in the data; a gap over 180s means a real absence,
    -- not a bridgeable heartbeat jitter.
    SELECT *,
        SUM(CASE WHEN gap_seconds IS NULL OR gap_seconds > 180 THEN 1 ELSE 0 END)
            OVER (PARTITION BY employeeid, activity, time_range, date_calendar ORDER BY last_seen) AS session_id
    FROM gapped
),
sessions AS (
    SELECT employeeid, activity, time_range, date_calendar, session_id,
           MIN(last_seen) AS s, MAX(last_seen) AS e
    FROM sessioned
    GROUP BY employeeid, activity, time_range, date_calendar, session_id
),
actual AS (
    SELECT employeeid, activity, date_calendar,
           COUNT(DISTINCT time_range) AS actual_count,
           SUM(EXTRACT(EPOCH FROM (e - s)) / 60.0) AS actual_minutes
    FROM sessions
    GROUP BY employeeid, activity, date_calendar
)
SELECT
    t.date_calendar, t.employeeid, t.vendor_name, t.siteid, t.activity,
    t.target_count AS target_total_activity,
    COALESCE(a.actual_count, 0) AS actual_total_activity,
    t.target_minutes AS target_duration_minutes,
    COALESCE(a.actual_minutes, 0) AS actual_duration_minutes
FROM target t
LEFT JOIN actual a
  ON a.employeeid = t.employeeid AND a.activity = t.activity AND a.date_calendar = t.date_calendar;

CREATE UNIQUE INDEX ON pgtrek_point_time_daily (employeeid, vendor_name, siteid, activity, date_calendar);
