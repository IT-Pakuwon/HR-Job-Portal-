-- v_job_apply_with_posting (mysql3 / jobportal)
-- Fix: the job-posting join was on docid only. hr_trx_jobposting.docid is unique only WITHIN a group_cpny_id
-- (e.g. JOB26110002 exists as JKT/EP and SBY/TP/PROMOTION), so every apply fanned out into one row per group and
-- the approval dashboard could show the wrong company/department (JAP261100103 showed TP / PROMOTION instead of
-- TP / OPERATION). Join on group_cpny_id as well.
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`user_it`@`%` SQL SECURITY DEFINER VIEW `v_job_apply_with_posting` AS
select
    `hr_trx_job_apply`.`id` AS `id`,
    `hr_trx_job_apply`.`docid` AS `docid`,
    `hr_trx_job_apply`.`apply_date` AS `docdate`,
    'JAP' AS `doctype`,
    `hr_trx_jobposting`.`cpnyid` AS `cpnyid`,
    `hr_trx_jobposting`.`departementid` AS `departementid`,
    'Candidate' AS `infohd`,
    `hr_trx_job_apply`.`status` AS `status`,
    `hr_trx_job_apply`.`created_user` AS `created_user`,
    '/showcareers' AS `url`
from `hr_trx_job_apply`
left join `hr_trx_jobposting`
    on `hr_trx_job_apply`.`jobid` = `hr_trx_jobposting`.`docid`
   and `hr_trx_job_apply`.`group_cpny_id` = `hr_trx_jobposting`.`group_cpny_id`;
