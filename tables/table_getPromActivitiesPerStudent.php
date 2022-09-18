<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $COURSE;
global $USER;
include('../database/Queries.php');
include('../database/FilesChecker.php');
include('tables_functions.php');

$course_id = $_POST['idCourse'];
$user_id = $USER->id;

function getNumberAccessPerAlumno($idAlumno, $course_id, $user_id) {
    $resultado = getAccesses($idAlumno, $course_id);
    $sumAccess = 0;
    $loggedin = false;

    foreach ($resultado as $key => $rs) {
        if ($user_id != $rs->userid) {
            if ($rs->action == 'loggedin' && $loggedin == false) {
                $loggedin = true;
            } else {
                if ($rs->action == 'viewed' && $loggedin == true) {
                    $sumAccess += 1;
                    $loggedin = false;
                }
            }
        }
    }

    return $sumAccess;
}

function getPromByStudent($course_id, $user_id) {
    $arrayTiemposAlumnos = array();
    $resultado = getUsersInThisCourse($course_id);

    foreach ($resultado as $rs) {
        if ($user_id != $rs->userid) {
            $promedioTiempoAlumno = getPromPerAlumnoByDay($rs->userid, $course_id, $user_id);
            array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
        }
    }

    return $arrayTiemposAlumnos;
}

function getPromPerAlumnoByDay($idAlumno, $course_id) {
    $idCourse = $course_id;
    $resultado = loadLogs($idAlumno);

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

function array_combine2($arr1, $arr2) {
    $count = min(count($arr1), count($arr2));
    return array_combine(array_slice($arr1, 0, $count), array_slice($arr2, 0, $count));
}

$sumaPromediosGrupo = 0;
$arrayTiemposAlumnos = array();;

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
        $matrizResultado = getPromActivityPerDayPerAlumno($rs->userid, $course_id);

        $numberAccess = getNumberAccessPerAlumno($rs->userid, $course_id, $user_id);

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

$promTimes = getPromByStudent($course_id, $user_id);

$index_number = 0;
foreach ($totalValues as $index => $value) {
    $totalValues[$index] = [$value, $totalQuantities[$index], $promTimes[$index_number]];
    $index_number += 1;
}

echo json_encode($totalValues);
