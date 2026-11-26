-- =====================================================================
-- Project module: create its 26 tables in db_das
--
-- Structure taken from db_das_test on 2026-11-26
-- (tr_project_message_read comes from database/sql/tr_project_message_read.sql).
--
-- HOW TO RUN: connect to db_das and run this whole file once.
--   * It runs in one transaction: if anything fails, nothing is created.
--   * It stops if the database is not db_das, or a table already exists.
--   * It does not drop or change any existing table.
--   * Run it as the database user the application connects with.
--
-- Test projects, tasks, teams and chat are NOT copied.
-- =====================================================================

BEGIN;

SET LOCAL search_path TO public;

DO $$
BEGIN
    IF current_database() <> 'db_das' THEN
        RAISE EXCEPTION 'Connected to %, expected db_das', current_database();
    END IF;
END $$;

-- ms_group : Project groups
CREATE TABLE ms_group (
    id bigserial NOT NULL,
    group_id character varying(20) NOT NULL,
    group_name character varying(255) NOT NULL,
    group_description text,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT ms_group_pkey PRIMARY KEY (id),
    CONSTRAINT ms_group_group_id_key UNIQUE (group_id)
);

-- tr_group_detail : Departments in a group
CREATE TABLE tr_group_detail (
    id bigserial NOT NULL,
    group_id character varying(20) NOT NULL,
    department_opr_id character varying(20) NOT NULL,
    cpny_id character varying(10),
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT tr_group_detail_pkey PRIMARY KEY (id)
);
CREATE INDEX idx_tr_group_detail_group ON tr_group_detail USING btree (group_id);

-- tr_group_member : Members of a group
CREATE TABLE tr_group_member (
    id bigserial NOT NULL,
    group_id character varying(20) NOT NULL,
    username character varying(50) NOT NULL,
    added_by character varying(50),
    added_at timestamp without time zone,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_group_member_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_group_member_group_username UNIQUE (group_id, username)
);
CREATE INDEX idx_tr_group_member_group ON tr_group_member USING btree (group_id);

-- ms_team : Teams
CREATE TABLE ms_team (
    id bigserial NOT NULL,
    team_id character varying(20) NOT NULL,
    team_name character varying(255) NOT NULL,
    team_description text,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT ms_team_pkey PRIMARY KEY (id),
    CONSTRAINT ms_team_team_id_key UNIQUE (team_id)
);

-- tr_team_member : Team captain and members
CREATE TABLE tr_team_member (
    id bigserial NOT NULL,
    team_id character varying(20) NOT NULL,
    username character varying(50) NOT NULL,
    member_role character varying(10) NOT NULL DEFAULT 'MEMBER'::character varying,
    added_by character varying(50),
    added_at timestamp without time zone,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_team_member_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_team_member_team_username UNIQUE (team_id, username)
);
CREATE INDEX idx_tr_team_member_team ON tr_team_member USING btree (team_id);
CREATE INDEX idx_tr_team_member_username ON tr_team_member USING btree (username);
CREATE UNIQUE INDEX uq_tr_team_member_one_captain ON tr_team_member USING btree (team_id) WHERE (((member_role)::text = 'CAPTAIN'::text) AND ((status)::text = 'A'::text));

-- ms_project_status : Project statuses
CREATE TABLE ms_project_status (
    id bigserial NOT NULL,
    status_id character varying(20) NOT NULL,
    status_name character varying(100) NOT NULL,
    color character varying(20),
    sort_order integer DEFAULT 0,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT ms_project_status_pkey PRIMARY KEY (id),
    CONSTRAINT ms_project_status_status_id_key UNIQUE (status_id)
);

-- tr_project_status_team : Which team uses which project status
CREATE TABLE tr_project_status_team (
    id bigserial NOT NULL,
    status_id character varying(20) NOT NULL,
    team_id character varying(20) NOT NULL,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    CONSTRAINT tr_project_status_team_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_project_status_team UNIQUE (status_id, team_id)
);
CREATE INDEX idx_tr_project_status_team_status ON tr_project_status_team USING btree (status_id);
CREATE INDEX idx_tr_project_status_team_team ON tr_project_status_team USING btree (team_id);

-- ms_task_tag : Task and project tags
CREATE TABLE ms_task_tag (
    id bigserial NOT NULL,
    tag_id character varying(30) NOT NULL,
    tag_name character varying(100) NOT NULL,
    color character varying(20),
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    CONSTRAINT ms_task_tag_pkey PRIMARY KEY (id),
    CONSTRAINT ms_task_tag_tag_id_key UNIQUE (tag_id)
);

-- ms_project : Projects
CREATE TABLE ms_project (
    id bigserial NOT NULL,
    project_id character varying(20) NOT NULL,
    group_id character varying(20),
    project_name character varying(255) NOT NULL,
    project_description text,
    start_date date,
    end_date date,
    status_id character varying(20),
    progress_percent numeric(5,2) DEFAULT 0,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT ms_project_pkey PRIMARY KEY (id),
    CONSTRAINT ms_project_project_id_key UNIQUE (project_id)
);
CREATE INDEX idx_ms_project_group ON ms_project USING btree (group_id);

-- tr_project : Links between projects
CREATE TABLE tr_project (
    id bigserial NOT NULL,
    project_id character varying(20) NOT NULL,
    linked_project_id character varying(20) NOT NULL,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT tr_project_pkey PRIMARY KEY (id),
    CONSTRAINT chk_tr_project_no_self_link CHECK (((project_id)::text <> (linked_project_id)::text))
);

-- tr_project_team : Teams on a project
CREATE TABLE tr_project_team (
    id bigserial NOT NULL,
    project_id character varying(20) NOT NULL,
    team_id character varying(20) NOT NULL,
    added_by character varying(50),
    added_at timestamp without time zone,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_project_team_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_project_team_project_team UNIQUE (project_id, team_id)
);
CREATE INDEX idx_tr_project_team_project ON tr_project_team USING btree (project_id);
CREATE INDEX idx_tr_project_team_team ON tr_project_team USING btree (team_id);

-- tr_project_pic : Project PIC
CREATE TABLE tr_project_pic (
    id bigserial NOT NULL,
    project_id character varying(20) NOT NULL,
    added_by character varying(50),
    added_at timestamp without time zone,
    status character varying(5) DEFAULT 'A'::character varying,
    pic_type character varying(10) DEFAULT 'USER'::character varying,
    ref_id character varying(50) NOT NULL,
    CONSTRAINT tr_project_pic_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_project_pic_project_ref UNIQUE (project_id, pic_type, ref_id)
);
CREATE INDEX idx_tr_project_pic_project ON tr_project_pic USING btree (project_id);

-- tr_project_tag : Project tags
CREATE TABLE tr_project_tag (
    id bigserial NOT NULL,
    project_id character varying(20) NOT NULL,
    tag_id character varying(30) NOT NULL,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_project_tag_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_project_tag UNIQUE (project_id, tag_id)
);
CREATE INDEX idx_tr_project_tag_project ON tr_project_tag USING btree (project_id);
CREATE INDEX idx_tr_project_tag_tag ON tr_project_tag USING btree (tag_id);

-- ms_project_task_status : Task status columns per project
CREATE TABLE ms_project_task_status (
    id bigserial NOT NULL,
    status_id character varying(20) NOT NULL,
    project_id character varying(20) NOT NULL,
    status_name character varying(100) NOT NULL,
    color character varying(20),
    sort_order integer DEFAULT 0,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT ms_project_task_status_pkey PRIMARY KEY (id),
    CONSTRAINT uq_ms_project_task_status_project_status UNIQUE (project_id, status_id)
);

-- tr_project_task : Project tasks and subtasks
CREATE TABLE tr_project_task (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    project_id character varying(20) NOT NULL,
    task_name character varying(255) NOT NULL,
    task_description text,
    start_date date,
    end_date date,
    status_id character varying(20),
    progress_percent numeric(5,2) DEFAULT 0,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    parent_task_id character varying(20),
    is_locked boolean NOT NULL DEFAULT false,
    locked_by character varying(50),
    locked_at timestamp without time zone,
    cover_attachment_id bigint,
    CONSTRAINT tr_project_task_pkey PRIMARY KEY (id),
    CONSTRAINT tr_project_task_task_id_key UNIQUE (task_id),
    CONSTRAINT chk_tr_project_task_no_self_parent CHECK (((task_id)::text <> (parent_task_id)::text))
);
CREATE INDEX idx_tr_project_task_parent ON tr_project_task USING btree (parent_task_id);
CREATE INDEX idx_tr_project_task_project ON tr_project_task USING btree (project_id);

-- tr_project_task_assignee : Task assignees
CREATE TABLE tr_project_task_assignee (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    username character varying(50) NOT NULL,
    assigned_by character varying(50),
    assigned_at timestamp without time zone,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_project_task_assignee_pkey PRIMARY KEY (id)
);

-- tr_project_task_team : Teams as task PIC
CREATE TABLE tr_project_task_team (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    team_id character varying(20) NOT NULL,
    assigned_by character varying(50),
    assigned_at timestamp without time zone,
    status character varying(5) NOT NULL DEFAULT 'A'::character varying,
    CONSTRAINT tr_project_task_team_pkey PRIMARY KEY (id)
);
CREATE INDEX tr_project_task_team_task_idx ON tr_project_task_team USING btree (task_id, status);

-- tr_project_task_tag : Task tags
CREATE TABLE tr_project_task_tag (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    tag_id character varying(30) NOT NULL,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_project_task_tag_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_project_task_tag UNIQUE (task_id, tag_id)
);
CREATE INDEX idx_tr_project_task_tag_tag ON tr_project_task_tag USING btree (tag_id);
CREATE INDEX idx_tr_project_task_tag_task ON tr_project_task_tag USING btree (task_id);

-- ms_team_task_status : Task status columns per team
CREATE TABLE ms_team_task_status (
    id bigserial NOT NULL,
    status_id character varying(20) NOT NULL,
    team_id character varying(20) NOT NULL,
    status_name character varying(100) NOT NULL,
    color character varying(20),
    sort_order integer DEFAULT 0,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT ms_team_task_status_pkey PRIMARY KEY (id),
    CONSTRAINT uq_ms_team_task_status_team_status UNIQUE (team_id, status_id)
);

-- tr_team_task : Team tasks
CREATE TABLE tr_team_task (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    team_id character varying(20) NOT NULL,
    parent_task_id character varying(20),
    task_name character varying(255) NOT NULL,
    task_description text,
    start_date date,
    end_date date,
    status_id character varying(20),
    progress_percent numeric(5,2) DEFAULT 0,
    status character varying(5) DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    cover_attachment_id bigint,
    CONSTRAINT tr_team_task_pkey PRIMARY KEY (id),
    CONSTRAINT tr_team_task_task_id_key UNIQUE (task_id),
    CONSTRAINT chk_tr_team_task_no_self_parent CHECK (((task_id)::text <> (parent_task_id)::text))
);
CREATE INDEX idx_tr_team_task_parent ON tr_team_task USING btree (parent_task_id);
CREATE INDEX idx_tr_team_task_team ON tr_team_task USING btree (team_id);

-- tr_team_task_assignee : Team task assignees
CREATE TABLE tr_team_task_assignee (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    username character varying(50) NOT NULL,
    assigned_by character varying(50),
    assigned_at timestamp without time zone,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_team_task_assignee_pkey PRIMARY KEY (id)
);
CREATE INDEX idx_tr_team_task_assignee_task ON tr_team_task_assignee USING btree (task_id);

-- tr_team_task_tag : Team task tags
CREATE TABLE tr_team_task_tag (
    id bigserial NOT NULL,
    task_id character varying(20) NOT NULL,
    tag_id character varying(30) NOT NULL,
    status character varying(5) DEFAULT 'A'::character varying,
    CONSTRAINT tr_team_task_tag_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_team_task_tag UNIQUE (task_id, tag_id)
);
CREATE INDEX idx_tr_team_task_tag_task ON tr_team_task_tag USING btree (task_id);

-- tr_favorite : User favorites
CREATE TABLE tr_favorite (
    id bigserial NOT NULL,
    username character varying(50) NOT NULL,
    fav_type character varying(10) NOT NULL,
    ref_id character varying(20) NOT NULL,
    created_at timestamp without time zone,
    CONSTRAINT tr_favorite_pkey PRIMARY KEY (id),
    CONSTRAINT uq_tr_favorite UNIQUE (username, fav_type, ref_id)
);
CREATE INDEX idx_tr_favorite_username ON tr_favorite USING btree (username);

-- tr_pm_activity : Activity history
CREATE TABLE tr_pm_activity (
    id bigserial NOT NULL,
    scope_type character varying(10) NOT NULL,
    scope_id character varying(20) NOT NULL,
    task_id character varying(20),
    action character varying(30) NOT NULL,
    description text NOT NULL,
    changes jsonb,
    created_by character varying(50),
    created_at timestamp without time zone NOT NULL DEFAULT now(),
    CONSTRAINT tr_pm_activity_pkey PRIMARY KEY (id)
);
CREATE INDEX idx_tr_pm_activity_scope ON tr_pm_activity USING btree (scope_type, scope_id, created_at DESC);
CREATE INDEX idx_tr_pm_activity_task ON tr_pm_activity USING btree (task_id);

-- tr_project_message : Chat messages
CREATE TABLE tr_project_message (
    id bigserial NOT NULL,
    refnbr character varying(20) NOT NULL,
    doctype character varying(10) NOT NULL,
    message_date timestamp without time zone NOT NULL DEFAULT now(),
    message_type character varying(10),
    cpny_id character varying(10),
    department_id character varying(25),
    username character varying(50),
    name character varying(255),
    message text,
    status character varying(5) NOT NULL DEFAULT 'A'::character varying,
    created_by character varying(50),
    created_at timestamp without time zone,
    updated_by character varying(50),
    updated_at timestamp without time zone,
    deleted_by character varying(50),
    deleted_at timestamp without time zone,
    CONSTRAINT tr_project_message_pkey PRIMARY KEY (id)
);
CREATE INDEX idx_tr_project_message_recent ON tr_project_message USING btree (doctype, message_date DESC);
CREATE INDEX idx_tr_project_message_thread ON tr_project_message USING btree (doctype, refnbr, message_date DESC);

-- tr_project_message_read : Chat read position (unread badges)
CREATE TABLE tr_project_message_read (
    id            BIGSERIAL PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    doctype       VARCHAR(10)  NOT NULL,
    refnbr        VARCHAR(20)  NOT NULL,
    last_read_id  BIGINT       NOT NULL DEFAULT 0,
    updated_at    TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_tr_project_message_read UNIQUE (username, doctype, refnbr)
);

-- Foreign keys (added after all tables exist)
ALTER TABLE tr_group_detail ADD CONSTRAINT tr_group_detail_group_id_fkey FOREIGN KEY (group_id) REFERENCES ms_group(group_id);
ALTER TABLE tr_group_member ADD CONSTRAINT tr_group_member_group_id_fkey FOREIGN KEY (group_id) REFERENCES ms_group(group_id);
ALTER TABLE tr_team_member ADD CONSTRAINT tr_team_member_team_id_fkey FOREIGN KEY (team_id) REFERENCES ms_team(team_id);
ALTER TABLE tr_project_status_team ADD CONSTRAINT tr_project_status_team_status_id_fkey FOREIGN KEY (status_id) REFERENCES ms_project_status(status_id);
ALTER TABLE tr_project_status_team ADD CONSTRAINT tr_project_status_team_team_id_fkey FOREIGN KEY (team_id) REFERENCES ms_team(team_id);
ALTER TABLE ms_project ADD CONSTRAINT ms_project_group_id_fkey FOREIGN KEY (group_id) REFERENCES ms_group(group_id);
ALTER TABLE ms_project ADD CONSTRAINT ms_project_status_id_fkey FOREIGN KEY (status_id) REFERENCES ms_project_status(status_id);
ALTER TABLE tr_project ADD CONSTRAINT tr_project_linked_project_id_fkey FOREIGN KEY (linked_project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project ADD CONSTRAINT tr_project_project_id_fkey FOREIGN KEY (project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project_team ADD CONSTRAINT tr_project_team_project_id_fkey FOREIGN KEY (project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project_team ADD CONSTRAINT tr_project_team_team_id_fkey FOREIGN KEY (team_id) REFERENCES ms_team(team_id);
ALTER TABLE tr_project_pic ADD CONSTRAINT tr_project_pic_project_id_fkey FOREIGN KEY (project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project_tag ADD CONSTRAINT tr_project_tag_project_id_fkey FOREIGN KEY (project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project_tag ADD CONSTRAINT tr_project_tag_tag_id_fkey FOREIGN KEY (tag_id) REFERENCES ms_task_tag(tag_id);
ALTER TABLE ms_project_task_status ADD CONSTRAINT ms_project_task_status_project_id_fkey FOREIGN KEY (project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project_task ADD CONSTRAINT fk_tr_project_task_status FOREIGN KEY (project_id, status_id) REFERENCES ms_project_task_status(project_id, status_id);
ALTER TABLE tr_project_task ADD CONSTRAINT tr_project_task_parent_task_id_fkey FOREIGN KEY (parent_task_id) REFERENCES tr_project_task(task_id);
ALTER TABLE tr_project_task ADD CONSTRAINT tr_project_task_project_id_fkey FOREIGN KEY (project_id) REFERENCES ms_project(project_id);
ALTER TABLE tr_project_task_assignee ADD CONSTRAINT tr_project_task_assignee_task_id_fkey FOREIGN KEY (task_id) REFERENCES tr_project_task(task_id);
ALTER TABLE tr_project_task_tag ADD CONSTRAINT tr_project_task_tag_tag_id_fkey FOREIGN KEY (tag_id) REFERENCES ms_task_tag(tag_id);
ALTER TABLE tr_project_task_tag ADD CONSTRAINT tr_project_task_tag_task_id_fkey FOREIGN KEY (task_id) REFERENCES tr_project_task(task_id);
ALTER TABLE ms_team_task_status ADD CONSTRAINT ms_team_task_status_team_id_fkey FOREIGN KEY (team_id) REFERENCES ms_team(team_id);
ALTER TABLE tr_team_task ADD CONSTRAINT fk_tr_team_task_status FOREIGN KEY (team_id, status_id) REFERENCES ms_team_task_status(team_id, status_id);
ALTER TABLE tr_team_task ADD CONSTRAINT tr_team_task_parent_task_id_fkey FOREIGN KEY (parent_task_id) REFERENCES tr_team_task(task_id);
ALTER TABLE tr_team_task ADD CONSTRAINT tr_team_task_team_id_fkey FOREIGN KEY (team_id) REFERENCES ms_team(team_id);
ALTER TABLE tr_team_task_assignee ADD CONSTRAINT tr_team_task_assignee_task_id_fkey FOREIGN KEY (task_id) REFERENCES tr_team_task(task_id);
ALTER TABLE tr_team_task_tag ADD CONSTRAINT tr_team_task_tag_tag_id_fkey FOREIGN KEY (tag_id) REFERENCES ms_task_tag(tag_id);
ALTER TABLE tr_team_task_tag ADD CONSTRAINT tr_team_task_tag_task_id_fkey FOREIGN KEY (task_id) REFERENCES tr_team_task(task_id);

-- Master data
INSERT INTO ms_project_status (status_id, status_name, color, sort_order, status, created_by, created_at, updated_by, updated_at, deleted_by, deleted_at) VALUES ('INPROGRESS', 'In progress', '#3B82F6', 1, 'A', 'system', now(), NULL, now(), NULL, NULL);
INSERT INTO ms_project_status (status_id, status_name, color, sort_order, status, created_by, created_at, updated_by, updated_at, deleted_by, deleted_at) VALUES ('DONE', 'Done', '#10B981', 2, 'A', 'system', now(), NULL, now(), NULL, NULL);
INSERT INTO ms_project_status (status_id, status_name, color, sort_order, status, created_by, created_at, updated_by, updated_at, deleted_by, deleted_at) VALUES ('NOTSTARTED', 'Not started', '#9CA3AF', 0, 'A', 'system', now(), NULL, now(), NULL, NULL);
INSERT INTO ms_task_tag (tag_id, tag_name, color, status, created_by, created_at, updated_by, updated_at) VALUES ('COMPLETE', 'Complete', '#10B981', 'A', 'system', now(), NULL, now());

COMMIT;
