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

    public static function getSemesterAvgTimeSpentPerActivity($idStudent, $idCourse) {
        $resultado = loadLogs($idStudent);

        $anteriorIgual = false;
        $anteriorCursoDistinto = true;
        $sumaTotal = 0;

        $arrayDiferencias = array();
        $arrayFechasInicio = array();
        $arrayFechasFin = array();

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
        $firstId = NULL;
        $contadorMods = 0;

        $idMods = array();

        foreach ($resultado as $key => $rs) {
            if (strpos($rs->component, "mod") !== false) {
                if (is_null($beginActivity)) {
                    if ($idCourse == $rs->courseid) {
                        $beginActivity = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
                        $firstId = $rs->id;
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
                        if ($idCourse == $rs->courseid) {
                            $beginActivity = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
                            $firstId = $rs->id;
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
                }
            }
            $beforeActivity = $rs->component;
            $contadorMods += 1;
        }

        $namesTableActivities = array_unique($nameActivity);
        $namesTableActivities = array_values($namesTableActivities);
        $timesTableActivities = array();

        $size_tableactivities = sizeof($namesTableActivities);
        for ($i = 0; $i < $size_tableactivities; $i++) {
            $timesTableActivities[$i] = array();
        }

        $prueba1 = array();

        $conta = 0;
        foreach ($nameActivity as $keyAct => $activity) {
            foreach ($arrayFechasInicio as $key => $rs) {
                if ($dateBeginActivity[$keyAct] > $rs && $dateBeginActivity[$keyAct] < $arrayFechasFin[$key]) {
                    foreach ($namesTableActivities as $index => $name) {
                        if ($name == $activity) {
                            if ($timeActivity[$keyAct] != 0) {
                                array_push($timesTableActivities[$index], $timeActivity[$keyAct]);
                                array_push($prueba1, $index);
                                $conta += 1;
                            }
                        }
                    }
                }
            }
        }

        $finalTableValues = array();
        $sumActivity = 0;
        $valueProm = 0;

        foreach ($namesTableActivities as $index => $name) {
            foreach ($timesTableActivities[$index] as $activity) {
                $sumActivity += $activity;
            }
            $valueProm = $sumActivity / sizeof($timesTableActivities[$index]);

            $valueProm = round($valueProm);

            if (is_nan($valueProm)) {
                $valueProm = 0;
            }
            $finalTableValues[$index] = $valueProm;
            $valueProm = 0;
            $sumActivity = 0;
        }

        return array_combine($namesTableActivities, $finalTableValues);
    }

    public static function getSemesterAvgTimeSpentPerActivityPerDay($idStudent, $idCourse) {
        $resultado = loadLogs($idStudent);

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
        $firstId = NULL;
        $contadorMods = 0;

        $idMods = array();

        foreach ($resultado as $key => $rs) {
            if (strpos($rs->component, "mod") !== false) {
                if (is_null($beginActivity)) {
                    if ($idCourse == $rs->courseid) {
                        $beginActivity = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
                        $firstId = $rs->id;
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
                        if ($idCourse == $rs->courseid) {
                            $beginActivity = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
                            $firstId = $rs->id;
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
                }
            }
            $beforeActivity = $rs->component;
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

        $matrizActivityDay = [];

        foreach ($nameActivity as $keyAct => $activity) {
            foreach ($namesTableActivities as $keyName => $name) {
                if ($name == $activity) {
                    foreach ($allDays as $keyDay => $day) {
                        if ($dateBeginActivity[$keyAct]->format('Y-m-d') == $day) {
                            if ($timeActivity[$keyAct] != 0) {
                                if ($matrizActivityDay[$keyName][$keyDay] != NULL) {
                                    $matrizActivityDay[$keyName][$keyDay] += $timeActivity[$keyAct];
                                } else {
                                    $matrizActivityDay[$keyName][$keyDay] = 0;
                                    $matrizActivityDay[$keyName][$keyDay] += $timeActivity[$keyAct];
                                }
                            }
                        }
                    }
                }
            }
        }

        $finalTableValues = array();
        $sumActivity = 0;
        $valueProm = 0;

        foreach ($namesTableActivities as $keyName => $name) {
            foreach ($allDays as $keyDay => $day) {
                $sumActivity += $matrizActivityDay[$keyName][$keyDay];
            }
            $valueProm = ($sumActivity / sizeof($matrizActivityDay[$keyName]));
            $valueProm = round($valueProm);
            if (is_nan($valueProm)) {
                $valueProm = 0;
            }
            $finalTableValues[$keyName] = $valueProm;
            $valueProm = 0;
            $sumActivity = 0;
        }

        return array_combine($namesTableActivities, $finalTableValues);
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
