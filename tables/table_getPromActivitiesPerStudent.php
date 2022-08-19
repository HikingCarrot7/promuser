<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $COURSE;
global $USER;

$course_id = $_POST['idCourse'];
$user_id = $USER->id;

function getPromActivityPerDayPerAlumno($idAlumno,$firstLastNames, $course_id, $user_id){
    $idCourse = $course_id;
    $extra_indications = "ORDER BY timecreated ASC";
    $resultado = getLogs($idAlumno, $user_id, $extra_indications);

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
        
        if($contadorRegistro == 1){
            $primerDiaCheck = new DateTime(date('Y-m-d',$rs->timecreated));
        }
        if($contadorRegistro == sizeof($resultado)){
            $ultimoDiaCheck = new DateTime(date('Y-m-d',$rs->timecreated));
        }

        if($course == $idCourse){
            if($anteriorIgual == true){
                $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                $sumaTotal += $diferencia;

                if($contadorRegistro == sizeof($resultado)){
                    array_push($arrayDiferencias,$sumaTotal);
                    array_push($arrayFechasFin, new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                    $sumaTotal = 0;
                    $anteriorCursoDistinto = true;
                    $anteriorIgual = false;
                }

                $inicio = new DateTime(date('Y-m-d H:i:s',$rs->timecreated));
                $anteriorIgual = true;
            }else{
                $inicio = new DateTime(date('Y-m-d H:i:s',$rs->timecreated));
                array_push($arrayFechasInicio, $inicio);
                $anteriorIgual = true;
            }
            $anteriorCursoDistinto = false;
        }else{
            if($anteriorCursoDistinto == false){
                $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                $sumaTotal += $diferencia;

                array_push($arrayDiferencias,$sumaTotal);
                array_push($arrayFechasFin, new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
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

    foreach ($resultado as $key=>$rs) {
        if(strpos($rs->component,"mod") !== false){
            if(is_null($beginActivity)){
                if($course_id == $rs->courseid){
                    $beginActivity = new DateTime(date('Y-m-d H:i:s',$rs->timecreated));
                    $firstId = $rs->id;
                    $firstActivity = $rs->component;
                }
            }else{
                if($beforeActivity != $rs->component){
                    $diferencia = $beginActivity->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                    $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                    array_push($idMods,$rs->id);
                    array_push($idActivity, $firstId);
                    array_push($nameActivity, $beforeActivity);
                    array_push($timeActivity, $diferencia);
                    array_push($dateBeginActivity, $beginActivity);
                    $beginActivity = NULL;
                    $diferencia = NULL;
                    $firstId = NULL;
                    $firstActivity = NULL;
                    if($course_id == $rs->courseid){
                        $beginActivity = new DateTime(date('Y-m-d H:i:s',$rs->timecreated));
                        $firstId = $rs->id;
                        $firstActivity = $rs->component;
                    }
                }else{
                    if(sizeof($resultado) == ($contadorMods+1)){
                        $diferencia = $beginActivity->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                        $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                        array_push($idMods,$rs->id);
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
        }else{
            if(!is_null($beginActivity)){
                $diferencia = $beginActivity->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                array_push($idMods,$rs->id);
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
    for ($i=0; $i < $size_tableactivities; $i++) { 
        $timesTableActivities[$i] = array();    
    }
    
    $date_from = $primerDiaCheck->format('Y-m-d');   
    $date_from = strtotime($date_from); 
    
    $date_to = $ultimoDiaCheck->format('Y-m-d');  
    $date_to = strtotime($date_to);  
    
    $allDays = array();
    for ($i=$date_from; $i<=$date_to; $i+=86400) {  
        array_push($allDays,date("Y-m-d", $i));  
    }

    $matrizActivityDay = [];

    foreach($nameActivity as $keyAct=>$activity){
        foreach($namesTableActivities as $keyName=>$name){
            if($name == $activity){
                foreach($allDays as $keyDay=>$day){
                    if($dateBeginActivity[$keyAct]->format('Y-m-d') == $day){
                        if($timeActivity[$keyAct] != 0){
                            if($matrizActivityDay[$keyName][$keyDay] != NULL){
                                $matrizActivityDay[$keyName][$keyDay] += $timeActivity[$keyAct];
                            }else{
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

    foreach($namesTableActivities as $keyName=>$name){
        foreach($allDays as $keyDay=>$day){
            $sumActivity += $matrizActivityDay[$keyName][$keyDay];
        }
        $valueProm = ($sumActivity/sizeof($matrizActivityDay[$keyName]));
        $valueProm = round($valueProm);

        if(is_nan($valueProm)){
            $valueProm = 0;
        }
        $finalTableValues[$keyName] = $valueProm;
        $valueProm = 0;
        $sumActivity = 0;
    }

    $finalTable = array_combine($namesTableActivities, $finalTableValues);
    
    if($namesComplete == '') {
        
    }

    return $finalTable;
}

function getNumberAccessPerAlumno($idAlumno, $firstLastNames, $course_id, $user_id){
    $idCourse = $course_id;
    $resultado = getAccesses($idAlumno, $course_id);
    
    $sumAccess = 0;
    $loggedin = false;
    $viewed = false;

    foreach ($resultado as $key=>$rs){
        if($user_id != $rs->userid) {
            if($rs->action == 'loggedin' && $loggedin == false) {
                $loggedin = true;
            }else{
                if($rs->action == 'viewed' && $loggedin == true) {
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
    
    $id_role_student = getStudentRoleId ();
    $contextId = getCourseContextId ($course_id);
    
    $resultado = getUsersInThisCourse($course_id);

    foreach ($resultado as $rs){
        if($user_id != $rs->userid) {
            $promedioTiempoAlumno = getPromPerAlumnoByDay($rs->userid, $course_id, $user_id);             
            array_push($arrayTiemposAlumnos,$promedioTiempoAlumno);
        }
    }
    
    return $arrayTiemposAlumnos;
}

function getPromPerAlumnoByDay($idAlumno, $course_id, $user_id){
    $idCourse = $course_id;
    $extra_indications = "ORDER BY timecreated ASC";
    $resultado = getLogs ($idAlumno, $user_id, $extra_indications);

    $anteriorIgual = false;
    $anteriorCursoDistinto = true;
    $sumaTotal = 0;
    
    $diaVueltaAnterior = new DateTime();
    $diaVueltaActual = new DateTime();

    $arrayDiferencias = array();
    $arrayFechas = array();

    $contadorRegistro = 0;


    foreach ($resultado as $rs) {
        if(contadorRegistro == 0){
            $diaVueltaAnterior = new DateTime(date('Y-m-d',$rs->timecreated));
            $diaVueltaActual = new DateTime(date('Y-m-d',$rs->timecreated));
        }
        $diaVueltaActual = new DateTime(date('Y-m-d',$rs->timecreated));


        $contadorRegistro += 1;
        $course = $rs->courseid;

        if($course == $idCourse){
            if($anteriorIgual == true){
                $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                $sumaTotal += $diferencia;

                if($contadorRegistro == sizeof($resultado) && $diaVueltaActual == $diaVueltaAnterior){
                    array_push($arrayDiferencias,$sumaTotal);
                    array_push($arrayFechas,$diaVueltaAnterior);
                    $sumaTotal = 0;
                    $anteriorCursoDistinto = true;
                    $anteriorIgual = false;
                }
                $inicio = new DateTime(date('Y-m-d H:i:s',$rs->timecreated));
                $anteriorIgual = true;
            }else{
                $inicio = new DateTime(date('Y-m-d H:i:s',$rs->timecreated));
                $anteriorIgual = true;
            }
            $anteriorCursoDistinto = false;
        }else{
            if($anteriorCursoDistinto == false){
                $diaVueltaActual = new DateTime(date('Y-m-d',$rs->timecreated));

                $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s',$rs->timecreated)));
                $diferencia = (($diferencia->days * 24 ) * 60 ) + ( $diferencia->i * 60 ) + $diferencia->s;
                $sumaTotal += $diferencia;
                
                array_push($arrayDiferencias,$sumaTotal);
                array_push($arrayFechas,$diaVueltaAnterior);
                $sumaTotal = 0;
                $anteriorCursoDistinto = true;
                $anteriorIgual = false;
            }
        }                   
        if($diaVueltaAnterior != $diaVueltaActual && $course == $idCourse){
            array_push($arrayDiferencias,$sumaTotal);
            array_push($arrayFechas,$diaVueltaAnterior);
            $sumaTotal = 0;
            $anteriorCursoDistinto = true;
            $anteriorIgual = false;
            
        }
        $diaVueltaAnterior = new DateTime(date('Y-m-d',$rs->timecreated));
    }
    
    $sumaPromediosTotal = 0;
    $contadorProm = 0;
    $arrayPromediosPorDia = array();


    foreach ($arrayDiferencias as $promedio){
        if($contadorProm == 0){
            $fAnterior = $arrayFechas[$contadorProm];    
        }
        $fActual = $arrayFechas[$contadorProm];
        
        if($fActual == $fAnterior){
            $sumaPromediosTotal += $promedio;
            if($contadorProm == (sizeof($arrayDiferencias) - 1)){
                array_push($arrayPromediosPorDia, $sumaPromediosTotal);
                $sumaPromediosTotal = 0;
            }
        }else{
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
    $valorTotal = $sumaPromDias/sizeof($arrayPromediosPorDia);
    $valorTotal = round($valorTotal);

    if(is_nan($valorTotal)){
        $valorTotal = 0;
    }
    return $valorTotal;
}

function array_combine2($arr1, $arr2) {
    $count = min(count($arr1), count($arr2));
    return array_combine(array_slice($arr1, 0, $count), array_slice($arr2, 0, $count));
}

include ('../database/Queries.php');

$sumaPromediosGrupo = 0;
$arrayTiemposAlumnos = array();; 

//Se obtiene el rol de estudiante con una función del archivo Queries.php
$id_role_student = getStudentRoleId ();
//Se obtiene el contextId con una función del archivo Queries.php 
$contextId = getCourseContextId ($course_id);
//Se obtienen los usuarios de este curso con una función del archivo Queries.php
$resultado = getUsersInThisCourse($course_id);


$activityFound = false;

$matrizResultado = array();
$students = array();
$studentActivitiesProms = array();
$quantities = array();

foreach ($resultado as $keyUser=>$rs){
    if($user_id != $rs->userid) {
        $activities = array();
        $sumActivites = array();
        $numTimesPerActivity = array();

        $activitiesMatriz = array();

        $namesComplete = $rs->firstname." ".$rs->lastname;
        $namesComplete = str_replace(" ",",",$namesComplete);
        $matrizResultado = getPromActivityPerDayPerAlumno($rs->userid, $namesComplete, $course_id, $user_id);
        
        $numberAccess = getNumberAccessPerAlumno($rs->userid, $namesComplete, $course_id, $user_id);

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
foreach($totalValues as $index=>$value){
    $totalValues[$index] = [$value, $totalQuantities[$index], $promTimes[$index_number]];
    $index_number += 1;
}

echo json_encode($totalValues);
?>