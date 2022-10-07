<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

include('../database/Queries.php');
include('../database/FilesChecker.php');

global $USER;

$course_id = $_POST['idCourse'];
$option_selected = $_POST['option'];
$user_id = $USER->id;

$sumaPromediosGrupo = 0;
$arrayTiemposAlumnos = array();

//Se obtiene el rol de estudiante con una función del archivo Queries.php
$id_role_student = loadStudentRoleId();
//Se obtiene el contextId con una función del archivo Queries.php 
$contextId = loadCourseContextId();
//Se obtienen los usuarios de este curso con una función del archivo Queries.php
$resultado = loadUsers();

$activities = array();
$promActivity = array();
$numTimesPerActivity = array();
$first_date = '';
$last_date = '';

$activityFound = false;

$matrizResultado = array();

foreach ($resultado as $keyUser => $rs) {
    if ($user_id != $rs->userid) {
        $namesComplete = $rs->firstname . " " . $rs->lastname;
        $namesComplete = str_replace(" ", ",", $namesComplete);
        if ($option_selected == "interval") {
            $matrizResultado = Student::getSemesterAvgTimeSpentPerActivity($rs->userid, $course_id);
            //FIRST DATE
            $extra_indications = "ORDER BY timecreated ASC LIMIT 1";
            $rows = getLogs($rs->userid, $user_id, $extra_indications);
            foreach ($rows as $row => $row_s) {
                if ($first_date == '') {
                    $first_date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                } else {
                    $date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                    if ($date < $first_date) {
                        $first_date = $date;
                    }
                }
            }
            // LAST DATE
            $extra_indications = "ORDER BY timecreated DESC LIMIT 1";
            $rows = getLogs($rs->userid, $user_id, $extra_indications);
            foreach ($rows as $row => $row_s) {
                if ($last_date == '') {
                    $last_date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                } else {
                    $date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                    if ($date > $last_date) {
                        $last_date = $date;
                    }
                }
            }
        } else {
            if ($option_selected == "day") {
                $matrizResultado = Student::getSemesterAvgTimeSpentPerActivityPerDay($rs->userid, $course_id);
                //FIRST_DATE
                $extra_indications = "ORDER BY timecreated ASC LIMIT 1";
                $rows = getLogs($rs->userid, $user_id, $extra_indications);
                foreach ($rows as $row => $row_s) {
                    if ($first_date == '') {
                        $first_date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                    } else {
                        $date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                        if ($date < $first_date) {
                            $first_date = $date;
                        }
                    }
                }
                //LAST_DATE
                $extra_indications = "ORDER BY timecreated DESC LIMIT 1";
                $rows = getLogs($rs->userid, $user_id, $extra_indications);
                foreach ($rows as $row => $row_s) {
                    if ($last_date == '') {
                        $last_date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                    } else {
                        $date = new DateTime(date('Y-m-d H:i:s', $row_s->timecreated));
                        if ($date > $last_date) {
                            $last_date = $date;
                        }
                    }
                }
            }
        }

        foreach ($matrizResultado as $nameAct => $promAct) {
            $activityFound = false;
            foreach ($activities as $keyAct => $act) {
                if ($act == $nameAct) {
                    if ($promActivity[$keyAct] != NULL && $promActivity[$keyAct] != 0) {
                        $promActivity[$keyAct] += $promAct;
                    } else {
                        $promActivity[$keyAct] = 0;
                        $promActivity[$keyAct] += $promAct;
                    }
                    if ($numTimesPerActivity[$keyAct] == NULL) {
                        $numTimesPerActivity[$keyAct] = 0;
                    }
                    if ($promAct != 0) {
                        $numTimesPerActivity[$keyAct] += 1;
                    }
                    $activityFound = true;
                }
            }

            if ($activityFound == false) {
                array_push($activities, $nameAct);
                foreach ($activities as $keyAct => $act) {
                    if ($act == $nameAct) {
                        if ($promActivity[$keyAct] != NULL && $promActivity[$keyAct] != 0) {
                            $promActivity[$keyAct] += $promAct;
                        } else {
                            $promActivity[$keyAct] = 0;
                            $promActivity[$keyAct] += $promAct;
                        }
                        if ($numTimesPerActivity[$keyAct] == NULL) {
                            $numTimesPerActivity[$keyAct] = 0;
                        }
                        if ($promAct != 0) {
                            $numTimesPerActivity[$keyAct] += 1;
                        }
                    }
                }
            }
        }
    }
}

$tablaFinal = array();
$promFinal = 0;

foreach ($promActivity as $indexProm => $promedio) {
    $promFinal = round($promedio / $numTimesPerActivity[$indexProm]);
    if (is_nan($promFinal)) {
        $promFinal = 0;
    }
    array_push($tablaFinal, $promFinal);
}

$resultTable = array();
$resultTable = array_combine($activities, $tablaFinal);
$resultTable['first_date'] = $first_date;
$resultTable['last_date'] = $last_date;

echo json_encode($resultTable);
