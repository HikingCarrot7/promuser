<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $USER;
include('../database/Queries.php');
include('../database/FilesChecker.php');

function getPromByGroupPerInterval() {
  global $USER;
  $course_id = $_POST['idCourse'];

  $sumaPromediosGrupo = 0;
  $arrayTiemposAlumnos = array();

  //Se obtiene el rol de estudiante con una función del archivo Queries.php
  $id_role_student = getStudentRoleId ();
  //Se obtiene el contextId con una función del archivo Queries.php 
  $contextId = loadCourseContextId();
  //Se obtienen los usuarios de este curso con una función del archivo Queries.php
  $resultado = getUsersInThisCourse($course_id);

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
  $course_id = loadCourseId();
  $professorId = loadProfessorId();

  $sumaPromediosGrupo = 0;
  $arrayTiemposAlumnos = array();

  //Se obtiene el rol de estudiante con una función del archivo Queries.php
  $id_role_student = getStudentRoleId ();
  //Se obtiene el contextId con una función del archivo Queries.php 
  $contextId = loadCourseContextId();
  //Se obtienen los usuarios de este curso con una función del archivo Queries.php
  $users = loadUsers();

  foreach ($users as $aUser) {
    if ($professorId != $aUser->userid) {
      $promedioTiempoAlumno = getPromPerAlumnoByDay($aUser->userid);
      $sumaPromediosGrupo += $promedioTiempoAlumno;
      array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
    }
  }

  $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);

  $valorTotalGrupo = round($valorTotalGrupo);

  return $valorTotalGrupo;
}

function getPromPerAlumno($idAlumno) {
  global $USER;

  $idCourse = $_POST['idCourse'];
  $extra_indications = "ORDER BY timecreated ASC";
  $resultado = loadLogs ($idAlumno);


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
  global $USER;

  $idCourse = $_POST['idCourse'];
  $extra_indications = "ORDER BY timecreated ASC";
  $resultado = loadLogs ($idAlumno);


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

function generateTimes () {

  $times = array();

  $segundos = getPromByGroupPerInterval();
  $segundos1 = getPromByGroupPerDay();
  array_push($times, $segundos);
  array_push($times, $segundos1);

  return $times;
}

$times = generateTimes();

echo json_encode($times);

?>