<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;
global $USER;
$variableCSV = array();

function getPromActivitiesPerOption($option) {
  global $DB;
  global $USER;

  $id_role_student = $DB->get_record_sql("SELECT id FROM mdl_role WHERE shortname = 'student';")->id;
  $contextId = $DB->get_record_sql("SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = " . $_GET['idCourse'] . ";")->id;

  $resultado = $DB->get_records_sql("SELECT id, userid, username, firstname, lastname, email FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
    GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
    WHERE user_role.by_role = 1 AND user_role.roles = " . $id_role_student . " AND user_role.contextid = " . $contextId . ") data_role
    INNER JOIN mdl_user users ON data_role.userid = users.id;");


  foreach ($resultado as $keyUser => $rs) {
    if ($USER->id != $rs->userid) {
      $namesComplete = $rs->firstname . " " . $rs->lastname;
      $namesComplete = str_replace(" ", ";", $namesComplete);

      if ($option == "day") {
        getPromActivityPerDayPerAlumno($rs->userid, $namesComplete);
      }
    }
  }
}

function getPromActivityPerDayPerAlumno($idAlumno, $firstLastNames) {
  global $DB;
  global $USER;
  global $variableCSV;

  $idCourse = $_GET['idCourse'];
  $resultado = $DB->get_records_sql("SELECT * FROM mdl_logstore_standard_log where (userid = " . $idAlumno . ") AND (target != 'config_log') AND (userid <> " . $USER->id . ") ORDER BY timecreated ASC");


  $anteriorIgual = false;
  $anteriorCursoDistinto = true;
  $sumaTotal = 0;

  $arrayDiferencias = array();
  $arrayFechasInicio = array();
  $arrayFechasFin = array();

  $contadorRegistro = 0;

  $primerDiaCheck = NULL;
  $ultimoDiaCheck = NULL;

  foreach ($resultado as $rs) {
    $contadorRegistro += 1;
    $course = $rs->courseid;

    if ($contadorRegistro == 1) {
      $primerDiaCheck = new DateTime(date('Y-m-d', $rs->timecreated));
    }
    if ($contadorRegistro == sizeof($resultado)) {
      $ultimoDiaCheck = new DateTime(date('Y-m-d', $rs->timecreated));
    }

    if ($course == $idCourse) {
      if ($anteriorIgual == true) {
        $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        $sumaTotal += $diferencia;

        if ($contadorRegistro == sizeof($resultado)) {
          array_push($arrayDiferencias, $sumaTotal);
          array_push($arrayFechasFin, new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
          $sumaTotal = 0;
          $anteriorCursoDistinto = true;
          $anteriorIgual = false;
        }

        $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        $anteriorIgual = true;
      } else {
        $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        array_push($arrayFechasInicio, $inicio);
        $anteriorIgual = true;
      }
      $anteriorCursoDistinto = false;
    } else {
      if ($anteriorCursoDistinto == false) {
        $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        $sumaTotal += $diferencia;

        array_push($arrayDiferencias, $sumaTotal);
        array_push($arrayFechasFin, new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $sumaTotal = 0;
        $anteriorCursoDistinto = true;
        $anteriorIgual = false;
      }
    }
  }

  $beginActivity = NULL;
  $idActivity = array();
  $nameActivity = array();
  $timeActivity = array();
  $dateBeginActivity = array();

  $beforeActivity = NULL;
  $beforeId = NULL;
  $firstId = NULL;
  $firstActivity = NULL;
  $equalMod = false;
  $contadorMods = 0;

  $idMods = array();


  foreach ($resultado as $key => $rs) {
    if (strpos($rs->component, "mod") !== false) {
      if (is_null($beginActivity)) {
        if ($_GET['idCourse'] == $rs->courseid) {
          $beginActivity = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
          $firstId = $rs->id;
          $firstActivity = $rs->component;
        }
      } else {
        if ($beforeActivity != $rs->component) {
          $diferencia = $beginActivity->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
          $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
          array_push($idMods, $rs->id);
          array_push($idActivity, $firstId);
          array_push($nameActivity, $beforeActivity);
          array_push($timeActivity, $diferencia);
          array_push($dateBeginActivity, $beginActivity);
          $beginActivity = NULL;
          $diferencia = NULL;
          $firstId = NULL;
          $firstActivity = NULL;
          if ($_GET['idCourse'] == $rs->courseid) {
            $beginActivity = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
            $firstId = $rs->id;
            $firstActivity = $rs->component;
          }
        } else {
          if (sizeof($resultado) == ($contadorMods + 1)) {
            $diferencia = $beginActivity->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
            $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
            array_push($idMods, $rs->id);
            array_push($idActivity, $firstId);
            array_push($nameActivity, $beforeActivity);
            array_push($timeActivity, $diferencia);
            array_push($dateBeginActivity, $beginActivity);
            $beginActivity = NULL;
            $diferencia = NULL;
            $firstId = NULL;
            $firstActivity = NULL;
          }
        }
      }
    } else {
      if (!is_null($beginActivity)) {
        $diferencia = $beginActivity->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        array_push($idMods, $rs->id);
        array_push($idActivity, $firstId);
        array_push($nameActivity, $beforeActivity);
        array_push($timeActivity, $diferencia);
        array_push($dateBeginActivity, $beginActivity);
        $beginActivity = NULL;
        $diferencia = NULL;
        $firstId = NULL;
        $firstActivity = NULL;
      }
    }
    $beforeActivity = $rs->component;
    $beforeId = $rs->id;
    $contadorMods += 1;
  }

  $namesTableActivities = array_unique($nameActivity);
  $namesTableActivities = array_values($namesTableActivities);
  $timesTableActivities = array();

  $size_tableactivities = sizeof($namesTableActivities);
  for ($i = 0; $i < $size_tableactivities; $i++) {
    $timesTableActivities[$i] = array();
  }

  $date_from = $primerDiaCheck->format('Y-m-d');
  $date_from = strtotime($date_from);

  $date_to = $ultimoDiaCheck->format('Y-m-d');
  $date_to = strtotime($date_to);

  $allDays = array();
  for ($i = $date_from; $i <= $date_to; $i += 86400) {
    array_push($allDays, date("Y-m-d", $i));
  }

  $contadorNum = 0;

  foreach ($nameActivity as $valorRegistro) {
    $unRegistro->idAlumno = $idAlumno;
    $unRegistro->nombre = $firstLastNames;
    $unRegistro->herramienta = $nameActivity[$contadorNum];
    $unRegistro->fechaInicio = $dateBeginActivity[$contadorNum]->format('d/m/Y H:i:s');
    $unRegistro->duracion = $timeActivity[$contadorNum];

    array_push($variableCSV, json_encode($unRegistro));

    $contadorNum = $contadorNum + 1;
  }
}

getPromActivitiesPerOption("day");

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
