<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $COURSE;
global $USER;
include('../database/Queries.php');
include('../database/FilesChecker.php');

$idCourse = $_GET['courseVar'];
$idUser = $_GET['var'];
$extra_indications = "ORDER BY timecreated ASC";
$resultado = loadLogsFileASC($idUser, $USER->id, $extra_indications);

$anteriorIgual = false;
$anteriorCursoDistinto = true;
$sumaTotal = 0;
$diaVueltaAnterior = new DateTime();
$diaVueltaActual = new DateTime();

$arrayDiferencias = array();
$arrayFechas = array();

$contadorRegistro = 0;

foreach ($resultado as $rs) {
  if ($contadorRegistro == 0) {
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
        if ($sumaTotal <= 86400) {
          array_push($arrayDiferencias, $sumaTotal);
          array_push($arrayFechas, $diaVueltaAnterior);
        }
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

      if ($sumaTotal <= 86400) {
        array_push($arrayDiferencias, $sumaTotal);
        array_push($arrayFechas, $diaVueltaAnterior);
      }
      $sumaTotal = 0;
      $anteriorCursoDistinto = true;
      $anteriorIgual = false;
    }
  }
  if ($diaVueltaAnterior != $diaVueltaActual && $course == $idCourse) {
    if ($sumaTotal <= 86400) {
      array_push($arrayDiferencias, $sumaTotal);
      array_push($arrayFechas, $diaVueltaAnterior);
    }
    $sumaTotal = 0;
    $anteriorCursoDistinto = true;
    $anteriorIgual = false;
  }
  $diaVueltaAnterior = new DateTime(date('Y-m-d', $rs->timecreated));
}

$sumaPromediosTotal = 0;
$contadorProm = 0;
$arrayPromediosPorDia = array();

foreach ($arrayDiferencias as $key => $promedio) {
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
    if ($key == (sizeof($arrayDiferencias) - 1)) {
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
      $sumaPromediosTotal = 0;
      $sumaPromediosTotal += $promedio;
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
    } else {
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
      $sumaPromediosTotal = 0;
      $sumaPromediosTotal += $promedio;
    }
  }

  $fAnterior = $fActual;
  $contadorProm += 1;
}

foreach ($arrayPromediosPorDia as $key => $value) {
  if ($value == 0) {
    unset($arrayPromediosPorDia[$key]);
  } else {
    $arrayPromediosPorDia[$key] = $value / 60;
  }
}

$arrayPromediosPorDia = array_values($arrayPromediosPorDia);

$beforeDay = 0;
foreach ($arrayFechas as $key => $value) {
  if ($beforeDay == $value) {
    unset($arrayFechas[$key]);
  }
  $beforeDay = $value;
}
$arrayFecha = array_values($arrayFechas);
$arrayTiempo = $arrayPromediosPorDia;

include('individual_graphic/index.html.php');
