<?php

class Student {
    public $id;
    public $name;
    public $logs;
    public $average;
    public $averagePerActivity;
    public $averagePerDay;
    public $averagePerActivityPerDay;

    public function __construct($id) {
        $this->id = $id;
    }

    public static function getSemesterAvgTimeSpent($idStudent, $idCourse) {
        $resultado = loadLogs($idStudent);

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

    public static function getSemesterAvgTimeSpentPerDay($idStudent, $idCourse) {
        $resultado = loadLogs($idStudent);

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

    public static function getSemesterAccessesCount($idStudent, $idCourse, $idUser) {
        $resultado = getAccesses($idStudent, $idCourse);
        $sumAccess = 0;
        $loggedin = false;

        foreach ($resultado as $key => $rs) {
            if ($idUser != $rs->userid) {
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
}
