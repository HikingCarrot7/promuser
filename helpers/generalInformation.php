<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');

global $USER;

function generateTimes($idCourse, $idUser) {
    $times = array();
    $segundos = Group::getSemesterAvgTimeSpent($idCourse, $idUser);
    $segundos1 = Group::getSemesterAvgTimePerDay();
    array_push($times, $segundos);
    array_push($times, $segundos1);
    return $times;
}

$times = generateTimes($_POST['idCourse'], $USER->id);

echo json_encode($times);
