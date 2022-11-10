<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
include('../database/Queries.php');
include('../database/FilesChecker.php');



function getPromActivitiesPerOption($option) {
    $professorId = loadProfessorId();
    $variableCSV = array();
    $course_id = loadCourseId();
    //Se obtienen los usuarios de este curso con una función del archivo Queries.php
    $resultado = loadUsers();

    foreach ($resultado as $keyUser => $rs) {
        if ($professorId != $rs->userid) {
            if ($option == "day") {
                $variableCSV = array_merge($variableCSV, loadSATSPPDCSV($rs->userid));
            }
        }
    }

    return $variableCSV;
}

$variableCSV = getPromActivitiesPerOption("day");

$data = "";
$counter = 0;

foreach ($variableCSV as $key => $row) {
    if ($counter == 0) {
        $data .= "ID del Alumno" . "," . "Nombre del Alumno" . "," . "Recurso" . "," . "Fecha de inicio" . "," . "Duracion en Segundos" . "\n";
    }
    $value = json_decode($row);

    $data .= $value->idAlumno . "," . $value->nombre . "," . $value->herramienta . "," . $value->fechaInicio . "," . $value->duracion . "\n";
    $counter++;
}

header('Content-Type: application/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=datos_recursos.csv');

echo $data;
