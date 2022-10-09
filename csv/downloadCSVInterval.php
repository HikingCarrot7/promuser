<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');

global $USER;
$variableCSV = array();

function getPromByGroupPerInterval() {
    global $USER;
    global $variableCSV;
    $course_id = $_GET['idCourse'];
    //Se obtienen los usuarios de este curso con una función del archivo Queries.php
    $resultado = getUsersInThisCourse($course_id);

    foreach ($resultado as $rs) {
        if ($USER->id != $rs->userid) {
            $namesComplete = $rs->firstname . " " . $rs->lastname;
            $namesComplete = str_replace(" ", ";", $namesComplete);
            Student::getSemesterAvgTimeSpentCSV($variableCSV, $rs->userid, $namesComplete, $course_id);
        }
    }
}

getPromByGroupPerInterval();

$data = "";
$counter = 0;

foreach ($variableCSV as $key => $row) {
    if ($counter == 0) {
        $data .= "ID del Alumno" . "," . "Nombre del Alumno" . "," . "Fecha de inicio" . "," . "Duracion en segundos" . "\n";
    }
    $value = json_decode($row);
    $data .= $value->idAlumno . "," . $value->nombre . "," . $value->fechaInicio . "," . $value->duracion . "\n";
    $counter++;
}

header('Content-Type: application/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=datos_intervalos.csv');

echo $data;
