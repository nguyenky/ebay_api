SELECT
	MIN(id) id_min,
	MAX(id) id_max,
	SUM(IF(attempts>0,1,0)) attempted,
	SUM(IF(attempts<1,1,0)) queued,
	COUNT(*) all_jobs
FROM
	ebay_api.jobs
;