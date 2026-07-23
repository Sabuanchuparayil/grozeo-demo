<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(EXTERNAL_LIBRARY_PATH);
switch ($op) {

case 'queued_jobs_store':
	loadQueuedJobs();
	break;
}