CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW viewtrxcareer AS
SELECT
    ja.id,
    ja.docid,
    ja.applicant_id,
    ja.apply_date,
    ja.apply_step,
    ja.prev_apply_step,
    ja.is_read,
    ja.status,
    ja.created_user,
    ja.created_at,
    ja.updated_user,
    ja.updated_at,
    ja.completed_user,
    ja.completed_at,
    jp.job_title,
    jp.subgrade_id,
    jp.job_type,
    jp.cpnyid,
    jp.group_cpny_id,
    jp.departementid,
    jp.job_level,
    jp.docid AS docidposting,
    jp.refid,
    a.full_name AS fullname,
    a.religion,
    a.height,
    a.weight,
    a.mobile_phone,
    a.status AS status_app,
    edu.education_name,
    edu.education_type,
    edu.end_year,
    edu.education_score,
    work.company_name,
    work.job_title AS work_job_title,
    DATE_FORMAT(work.end_date, '%Y-%m-%d') AS end_date
FROM hr_trx_job_apply AS ja
LEFT JOIN hr_trx_jobposting AS jp
    ON jp.docid = ja.jobid
    AND jp.group_cpny_id = ja.group_cpny_id
LEFT JOIN hr_ms_applicant AS a
    ON a.applicant_id = ja.applicant_id
    AND a.group_cpny_id = ja.group_cpny_id
LEFT JOIN (
    SELECT
        e1.id,
        e1.applicant_id,
        e1.group_cpny_id,
        e1.education_name,
        e1.education_type,
        e1.start_year,
        e1.end_year,
        e1.education_score,
        e1.status,
        e1.created_user,
        e1.created_at,
        e1.updated_user,
        e1.updated_at,
        e1.completed_user,
        e1.completed_at
    FROM hr_ms_applicant_education AS e1
    WHERE NOT EXISTS (
        SELECT 1
        FROM hr_ms_applicant_education AS e2
        WHERE e2.applicant_id = e1.applicant_id
          AND e2.group_cpny_id = e1.group_cpny_id
          AND (
              IFNULL(e2.end_year, 9999) > IFNULL(e1.end_year, 9999)
              OR (
                  IFNULL(e2.end_year, 9999) = IFNULL(e1.end_year, 9999)
                  AND e2.id > e1.id
              )
          )
        LIMIT 1
    )
) AS edu
    ON edu.applicant_id = a.applicant_id
    AND edu.group_cpny_id = a.group_cpny_id
LEFT JOIN (
    SELECT
        w1.id,
        w1.applicant_id,
        w1.group_cpny_id,
        w1.company_name,
        w1.job_title,
        w1.start_date,
        w1.end_date,
        w1.superior_name,
        w1.reason_for_leaving,
        w1.status,
        w1.created_user,
        w1.created_at,
        w1.updated_user,
        w1.updated_at,
        w1.completed_user,
        w1.completed_at
    FROM hr_ms_applicant_working_exp AS w1
    WHERE NOT EXISTS (
        SELECT 1
        FROM hr_ms_applicant_working_exp AS w2
        WHERE w2.applicant_id = w1.applicant_id
          AND w2.group_cpny_id = w1.group_cpny_id
          AND (
              IFNULL(w2.end_date, '9999-12-31') > IFNULL(w1.end_date, '9999-12-31')
              OR (
                  IFNULL(w2.end_date, '9999-12-31') = IFNULL(w1.end_date, '9999-12-31')
                  AND w2.id > w1.id
              )
          )
        LIMIT 1
    )
) AS work
    ON work.applicant_id = a.applicant_id
    AND work.group_cpny_id = a.group_cpny_id;
