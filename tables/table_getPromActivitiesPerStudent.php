<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');

global $COURSE;
global $USER;

$user_id = $USER->id;

function getSemesterAvgTimeSpentForAllStudents($idUser) {
    $arrayTiemposAlumnos = array();
    $resultado = loadUsers();

    foreach ($resultado as $rs) {
        if ($idUser != $rs->userid) {
            $promedioTiempoAlumno = loadSATSPD($rs->userid);
            array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
        }
    }

    return $arrayTiemposAlumnos;
}

$sumaPromediosGrupo = 0;
$arrayTiemposAlumnos = array();

//Se obtiene el rol de estudiante con una función del archivo Queries.php
$id_role_student = loadStudentRoleId();
//Se obtiene el contextId con una función del archivo Queries.php 
$contextId = loadCourseContextId();
//Se obtienen los usuarios de este curso con una función del archivo Queries.php
$resultado = loadUsers();

$activityFound = false;

$matrizResultado = array();
$students = array();
$studentActivitiesProms = array();
$quantities = array();

foreach ($resultado as $keyUser => $rs) {
    if ($user_id != $rs->userid) {
        $activities = array();
        $sumActivites = array();
        $numTimesPerActivity = array();

        $activitiesMatriz = array();

        $namesComplete = $rs->firstname . " " . $rs->lastname;
        $namesComplete = str_replace(" ", ",", $namesComplete);
        $matrizResultado = loadSATSPAPD($rs->userid);

        $numberAccess = loadSAC($rs->userid);

        $tablaFinal = array();
        $promFinal = 0;

        array_push($students, $namesComplete);
        array_push($studentActivitiesProms, $matrizResultado);
        array_push($quantities, $numberAccess);
    }
}

$totalValues = array();
$totalQuantities = array();

$totalValues = array_combine($students, $studentActivitiesProms);
$totalQuantities = array_combine($students, $quantities);

$promTimes = getSemesterAvgTimeSpentForAllStudents($user_id);

$index_number = 0;
foreach ($totalValues as $index => $value) {
    $totalValues[$index] = [$value, $totalQuantities[$index], $promTimes[$index_number]];
    $index_number += 1;
}

echo json_encode($totalValues);
