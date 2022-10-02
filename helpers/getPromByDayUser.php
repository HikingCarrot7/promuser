<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');

global $COURSE;
global $USER;

$idCourse = $_POST['idCourse'];
$idUser = $_POST['idUser'];

$extra_indications = "ORDER BY timecreated ASC";
$resultado = loadLogs($idUser);

$anteriorIgual = false;
$anteriorCursoDistinto = true;
$sumaTotal = 0;
$diaVueltaAnterior = new DateTime();
$diaVueltaActual = new DateTime();

$arrayDiferencias = array();
$arrayFechas = array();

$contadorRegistro = 0;
$first_date;
$last_date;

foreach ($resultado as $rs) {
    if ($contadorRegistro == 0) {
        $first_date = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
        $diaVueltaAnterior = new DateTime(date('Y-m-d', $rs->timecreated));
        $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));
    }
    $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));

    $contadorRegistro += 1;
    if ($contadorRegistro == sizeof($resultado)) {
        $last_date = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
    }
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

$json_array['valor_total'] = $valorTotal;
$json_array['first_date'] = $first_date;
$json_array['last_date'] = $last_date;
$json_array['array_promedios_por_dia'] = $arrayPromediosPorDia;

echo json_encode($json_array);
