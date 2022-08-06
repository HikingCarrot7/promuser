<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;
global $USER;
$variableCSV = array();

function getPromByGroupPerInterval() {
  global $DB;
  global $variableCSV;
  global $USER;

  $sumaPromediosGrupo = 0;
  $arrayTiemposAlumnos = array();
  $id_role_student = $DB->get_record_sql("SELECT id FROM mdl_role WHERE shortname = 'student';")->id;
  $contextId = $DB->get_record_sql("SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = " . $_GET['idCourse'] . ";")->id;

  $resultado = $DB->get_records_sql("SELECT id, userid, username, firstname, lastname, email FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
    GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
    WHERE user_role.by_role = 1 AND user_role.roles = " . $id_role_student . " AND user_role.contextid = " . $contextId . ") data_role
    INNER JOIN mdl_user users ON data_role.userid = users.id;");

  foreach ($resultado as $rs) {
    if ($USER->id != $rs->userid) {
      $namesComplete = $rs->firstname . " " . $rs->lastname;
      $namesComplete = str_replace(" ", ";", $namesComplete);
      getPromPerAlumno($rs->userid, $namesComplete);
    }
  }
}

function getPromPerAlumno($idAlumno, $firstLastNames) {
  global $DB;
  global $USER;
  global $variableCSV;

  $idCourse = $_GET['idCourse'];
  $resultado = $DB->get_records_sql("SELECT * FROM mdl_logstore_standard_log where (userid = " . $idAlumno . ") AND (target != 'config_log') AND (userid <> " . $USER->id . ") ORDER BY timecreated ASC");

  $anteriorIgual = false;
  $anteriorCursoDistinto = true;
  $sumaTotal = 0;

  $beginActivity = NULL;
  $arrayDiferencias = array();
  $dateBeginActivity = array();

  $contadorRegistro = 0;

  foreach ($resultado as $rs) {
    $contadorRegistro += 1;
    $course = $rs->courseid;

    if ($course == $idCourse) {
      if ($anteriorIgual == true) {
        $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        $sumaTotal += $diferencia;

        if ($contadorRegistro == sizeof($resultado)) {
          array_push($arrayDiferencias, $sumaTotal);
          $sumaTotal = 0;
          $anteriorCursoDistinto = true;
          $anteriorIgual = false;
        }

        $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        $anteriorIgual = true;
      } else {
        $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        array_push($dateBeginActivity, $inicio);
        $anteriorIgual = true;
      }
      $anteriorCursoDistinto = false;
    } else {
      if ($anteriorCursoDistinto == false) {
        $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        $sumaTotal += $diferencia;

        array_push($arrayDiferencias, $sumaTotal);
        $sumaTotal = 0;
        $anteriorCursoDistinto = true;
        $anteriorIgual = false;
      }
    }
  }

  foreach ($arrayDiferencias as $key => $value) {
    if ($value == 0) {
      unset($arrayDiferencias[$key]);
    }
  }

  $arrayDiferencias = array_values($arrayDiferencias);

  $contadorNum = 0;
  foreach ($arrayDiferencias as $valorRegistro) {
    $unRegistro->idAlumno = $idAlumno;
    $unRegistro->nombre = $firstLastNames;
    $unRegistro->fechaInicio = $dateBeginActivity[$contadorNum]->format('d/m/Y H:i:s');
    $unRegistro->duracion = $arrayDiferencias[$contadorNum];

    array_push($variableCSV, json_encode($unRegistro));

    $contadorNum = $contadorNum + 1;
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
