<?php

function getPromActivityPerDayPerAlumno($idAlumno,$firstLastNames, $course_id, $user_id){
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


    return $finalTable;
}


?>