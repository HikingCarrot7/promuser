<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $USER;
include('../database/Queries.php');
include('../database/FilesChecker.php');
include ('tables_functions.php');

$course_id = $_POST['idCourse'];
$option_selected = $_POST['option'];
$user_id = $USER->id;

function getPromActivityPerAlumno($course_id, $idAlumno,$firstLastNames,$user_id){
    $idCourse = $course_id;
    $extra_indications = "ORDER BY timecreated ASC";
    $resultado = loadLogs($idAlumno);


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
    
    $prueba1 = array();

    $conta = 0;
    foreach($nameActivity as $keyAct=>$activity){
        foreach($arrayFechasInicio as $key=>$rs){
            if($dateBeginActivity[$keyAct] > $rs && $dateBeginActivity[$keyAct] < $arrayFechasFin[$key]){
                foreach($namesTableActivities as $index=>$name){
                    if($name == $activity){
                        if($timeActivity[$keyAct] != 0){
                            array_push($timesTableActivities[$index], $timeActivity[$keyAct]);
                            array_push($prueba1, $index);
                            $conta+=1;
                        }
                    }
                }
            }
        }
    }

    $finalTableValues = array();
    $sumActivity = 0;
    $valueProm = 0;

    foreach($namesTableActivities as $index=>$name){
        foreach($timesTableActivities[$index] as $activity){
            $sumActivity += $activity;
        }
        $valueProm = $sumActivity/sizeof($timesTableActivities[$index]);
        
        $valueProm = round($valueProm);

        if(is_nan($valueProm)){
                $valueProm = 0;
        }
        $finalTableValues[$index] = $valueProm;
        $valueProm = 0;
        $sumActivity = 0;
    }

    $finalTable = array_combine($namesTableActivities, $finalTableValues);

    return $finalTable;
}

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

foreach ($resultado as $keyUser=>$rs){
    if($user_id != $rs->userid) {
        $namesComplete = $rs->firstname." ".$rs->lastname;
        $namesComplete = str_replace(" ",",",$namesComplete);
        
        if($option_selected == "interval"){
            $matrizResultado = getPromActivityPerAlumno($course_id, $rs->userid,$namesComplete,$user_id);
            //FIRST DATE
            $extra_indications = "ORDER BY timecreated ASC LIMIT 1";
            $rows = getLogs ($rs->userid, $user_id, $extra_indications);
            foreach($rows as $row=>$row_s){
                if($first_date == '') {
                    $first_date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                }else{
                    $date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                    if($date < $first_date) {
                        $first_date = $date;
                    }
                }
            }
            // LAST DATE
            $extra_indications = "ORDER BY timecreated DESC LIMIT 1";
            $rows = getLogs ($rs->userid, $user_id, $extra_indications);
            foreach($rows as $row=>$row_s){
                if($last_date == '') {
                    $last_date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                }else{
                    $date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                    if($date > $last_date) {
                        $last_date = $date;
                    }
                }
            }
        }else{
            if($option_selected == "day"){
                $matrizResultado = getPromActivityPerDayPerAlumno($rs->userid,$namesComplete,$course_id,$user_id);
                //FIRST_DATE
                $extra_indications = "ORDER BY timecreated ASC LIMIT 1";
                $rows = getLogs ($rs->userid, $user_id, $extra_indications);
                foreach($rows as $row=>$row_s){
                    if($first_date == '') {
                        $first_date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                    }else{
                        $date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                        if($date < $first_date) {
                            $first_date = $date;
                        }
                    }
                }
                //LAST_DATE
                $extra_indications = "ORDER BY timecreated DESC LIMIT 1";
                $rows = getLogs ($rs->userid, $user_id, $extra_indications);
                foreach($rows as $row=>$row_s){
                    if($last_date == '') {
                        $last_date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                    }else{
                        $date = new DateTime(date('Y-m-d H:i:s',$row_s->timecreated));
                        if($date > $last_date) {
                            $last_date = $date;
                        }
                    }
                }
            }
        }
        
        foreach($matrizResultado as $nameAct=>$promAct){
            $activityFound = false;
            foreach($activities as $keyAct=>$act){
                if($act == $nameAct){
                    if($promActivity[$keyAct] != NULL && $promActivity[$keyAct] != 0){
                        $promActivity[$keyAct] += $promAct;
                    }else{
                        $promActivity[$keyAct] = 0;
                        $promActivity[$keyAct] += $promAct;
                    }
                    if($numTimesPerActivity[$keyAct] == NULL){
                        $numTimesPerActivity[$keyAct] = 0;
                    }
                    if($promAct != 0){
                        $numTimesPerActivity[$keyAct] += 1;
                    }
                    $activityFound = true;
                }    
            }
            if($activityFound == false){
                array_push($activities, $nameAct);
                foreach($activities as $keyAct=>$act){
                    if($act == $nameAct){
                        if($promActivity[$keyAct] != NULL && $promActivity[$keyAct] != 0){
                            $promActivity[$keyAct] += $promAct;
                        }else{
                            $promActivity[$keyAct] = 0;
                            $promActivity[$keyAct] += $promAct;
                        }
                        if($numTimesPerActivity[$keyAct] == NULL){
                            $numTimesPerActivity[$keyAct] = 0;
                        }
                        if($promAct != 0){
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

foreach($promActivity as $indexProm=>$promedio){

    $promFinal = round($promedio/$numTimesPerActivity[$indexProm]);
    if(is_nan($promFinal)){
        $promFinal = 0;
    }
    array_push($tablaFinal,$promFinal);
}

$resultTable = array();
$resultTable = array_combine($activities, $tablaFinal);
$resultTable['first_date'] = $first_date;
$resultTable['last_date'] = $last_date;



echo json_encode($resultTable);

?>