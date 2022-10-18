<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');

$segundos = loadGroupSATS();
$segundos1 = loadGroupSATPD();
    
$times = [$segundos,$segundos1];

echo json_encode($times);
