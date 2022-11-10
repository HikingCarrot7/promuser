<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');


function getPromByGroupPerInterval() {
    $professorId = loadProfessorId();
    $resultado = loadUsers();
    $variableCSV = array();

    foreach ($resultado as $rs) {
        if ($professorId != $rs->userid) {
            $variableCSV = array_merge($variableCSV, loadSATSCSV($rs->userid));
        }
    }

    return $variableCSV;
}

$variableCSV = getPromByGroupPerInterval();

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
