<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;
global $USER;


function getPromByGroupPerInterval() {
  global $DB;
  global $USER;

  $sumaPromediosGrupo = 0;
  $arrayTiemposAlumnos = array();

  $id_role_student = $DB->get_record_sql("SELECT id FROM mdl_role WHERE shortname = 'student';")->id;
  $contextId = $DB->get_record_sql("SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = " . $_POST['idCourse'] . ";")->id;

  $resultado = $DB->get_records_sql("SELECT id, userid, username, firstname, lastname, email FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
    GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
    WHERE user_role.by_role = 1 AND user_role.roles = " . $id_role_student . " AND user_role.contextid = " . $contextId . ") data_role
    INNER JOIN mdl_user users ON data_role.userid = users.id;");

  foreach ($resultado as $rs) {
    if ($USER->id != $rs->userid) {
      $promedioTiempoAlumno = getPromPerAlumno($rs->userid);
      $sumaPromediosGrupo += $promedioTiempoAlumno;
      array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
    }
  }

  $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);

  $valorTotalGrupo = round($valorTotalGrupo);

  return $valorTotalGrupo;
}

function getPromByGroupPerDay() {
  global $DB;
  global $USER;

  $sumaPromediosGrupo = 0;
  $arrayTiemposAlumnos = array();

  $id_role_student = $DB->get_record_sql("SELECT id FROM mdl_role WHERE shortname = 'student';")->id;
  $contextId = $DB->get_record_sql("SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = " . $_POST['idCourse'] . ";")->id;


  $resultado = $DB->get_records_sql("SELECT id, userid, username, firstname, lastname, email FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
    GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
    WHERE user_role.by_role = 1 AND user_role.roles = " . $id_role_student . " AND user_role.contextid = " . $contextId . ") data_role
    INNER JOIN mdl_user users ON data_role.userid = users.id;");

  foreach ($resultado as $rs) {
    if ($USER->id != $rs->userid) {
      $promedioTiempoAlumno = getPromPerAlumnoByDay($rs->userid);
      $sumaPromediosGrupo += $promedioTiempoAlumno;
      array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
    }
  }

  $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);

  $valorTotalGrupo = round($valorTotalGrupo);

  return $valorTotalGrupo;
}

function getPromPerAlumno($idAlumno) {
  global $DB;
  global $USER;

  $idCourse = $_POST['idCourse'];
  $resultado = $DB->get_records_sql("SELECT * FROM mdl_logstore_standard_log where (userid = " . $idAlumno . ") AND (target != 'config_log') AND (userid <> " . $USER->id . ") ORDER BY timecreated ASC");


  $anteriorIgual = false;
  $anteriorCursoDistinto = true;
  $sumaTotal = 0;

  $arrayDiferencias = array();

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
  $sumaPromediosTotal = array_sum($arrayDiferencias);
  $valorTotal = $sumaPromediosTotal / sizeof($arrayDiferencias);
  $valorTotal = round($valorTotal);

  if (is_nan($valorTotal)) {
    $valorTotal = 0;
  }
  return $valorTotal;
}

function getPromPerAlumnoByDay($idAlumno) {
  global $DB;
  global $USER;

  $idCourse = $_POST['idCourse'];
  $resultado = $DB->get_records_sql("SELECT * FROM mdl_logstore_standard_log where (userid = " . $idAlumno . ") AND (target != 'config_log') AND (userid <> " . $USER->id . ") ORDER BY timecreated ASC");


  $anteriorIgual = false;
  $anteriorCursoDistinto = true;
  $sumaTotal = 0;
  $diaVueltaAnterior = new DateTime();
  $diaVueltaActual = new DateTime();

  $arrayDiferencias = array();
  $arrayFechas = array();

  $contadorRegistro = 0;


  foreach ($resultado as $rs) {
    if (contadorRegistro == 0) {
      $diaVueltaAnterior = new DateTime(date('Y-m-d', $rs->timecreated));
      $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));
    }
    $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));


    $contadorRegistro += 1;
    $course = $rs->courseid;

    if ($course == $idCourse) {

      if ($anteriorIgual == true) {
        $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        $sumaTotal += $diferencia;


        if ($contadorRegistro == sizeof($resultado) && $diaVueltaActual == $diaVueltaAnterior) {
          array_push($arrayDiferencias, $sumaTotal);
          array_push($arrayFechas, $diaVueltaAnterior);
          $sumaTotal = 0;
          $anteriorCursoDistinto = true;
          $anteriorIgual = false;
        }
        $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        $anteriorIgual = true;
      } else {
        $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        $anteriorIgual = true;
      }
      $anteriorCursoDistinto = false;
    } else {
      if ($anteriorCursoDistinto == false) {
        $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));

        $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
        $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
        $sumaTotal += $diferencia;

        array_push($arrayDiferencias, $sumaTotal);
        array_push($arrayFechas, $diaVueltaAnterior);
        $sumaTotal = 0;
        $anteriorCursoDistinto = true;
        $anteriorIgual = false;
      }
    }
    if ($diaVueltaAnterior != $diaVueltaActual && $course == $idCourse) {
      array_push($arrayDiferencias, $sumaTotal);
      array_push($arrayFechas, $diaVueltaAnterior);
      $sumaTotal = 0;
      $anteriorCursoDistinto = true;
      $anteriorIgual = false;
    }
    $diaVueltaAnterior = new DateTime(date('Y-m-d', $rs->timecreated));
  }

  $sumaPromediosTotal = 0;
  $contadorProm = 0;
  $arrayPromediosPorDia = array();


  foreach ($arrayDiferencias as $promedio) {
    if ($contadorProm == 0) {
      $fAnterior = $arrayFechas[$contadorProm];
    }
    $fActual = $arrayFechas[$contadorProm];

    if ($fActual == $fAnterior) {
      $sumaPromediosTotal += $promedio;
      if ($contadorProm == (sizeof($arrayDiferencias) - 1)) {
        array_push($arrayPromediosPorDia, $sumaPromediosTotal);
        $sumaPromediosTotal = 0;
      }
    } else {
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
      $sumaPromediosTotal = 0;
      $sumaPromediosTotal += $promedio;
    }

    $fAnterior = $fActual;
    $contadorProm += 1;
  }

  foreach ($arrayPromediosPorDia as $key => $value) {
    if ($value == 0) {
      unset($arrayPromediosPorDia[$key]);
    }
  }

  $arrayPromediosPorDia = array_values($arrayPromediosPorDia);

  $sumaPromDias = 0;
  foreach ($arrayPromediosPorDia as $key => $value) {
    $sumaPromDias += $value;
  }

  $valorTotal = 0;
  $valorTotal = $sumaPromDias / sizeof($arrayPromediosPorDia);
  $valorTotal = round($valorTotal);

  if (is_nan($valorTotal)) {
    $valorTotal = 0;
  }
  return $valorTotal;
}

$times = array();

$segundos = getPromByGroupPerInterval();
$segundos1 = getPromByGroupPerDay();
array_push($times, $segundos);
array_push($times, $segundos1);

echo json_encode($times);
