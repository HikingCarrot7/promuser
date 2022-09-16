<?php

function loadUsersFile($courseid) {

    //Se declara la ruta del archivo de datos
    $pathFile = '../blocks/promuser/files/users.txt';

    //Si el archivo no tiene datos...
    if (filesize($pathFile) == false) {
        //Se obtienen los datos
        $users = json_encode(getUsers($courseid));
        writeDataFile($pathFile,$users);
    } else {
        echo ('<script>console.log(' . readDataFile($pathFile) . ')</script>');
        $users = readDataFile($pathFile);
    }

    return $users;
}

function loadLogsFileASC($idAlumno, $user_id, $extra_indications) {

    //Se declara la ruta del archivo de datos
    $pathFile = '../files/logs.txt';
    if array.contains $user_id {
        return array[$user_id].logs;
    }
    //Si el archivo no tiene datos...
    if (filesize($pathFile) == false) {
        //Se obtienen los datos
        $logs = getLogs($idAlumno, $user_id, $extra_indications);
        writeDataFile($pathFile,$logs);
    } else {
        $logs = readDataFile($pathFile);
    }

    return $logs;
}

function loadTimes () {
    //Se declara la ruta del archivo de datos
    $pathFile = '../files/times.txt';

    //Si el archivo no tiene datos...
    if (filesize($pathFile) == false) {
        //Se obtienen los datos
        $times = generateTimes();
        writeDataFile($pathFile,$times);
    } else {
        $times = readDataFile($pathFile);
    }

    return $times;
}

function loadProm () {
    //Se declara la ruta del archivo de datos
    $pathFile = '../files/prom.txt';

    //Si el archivo no tiene datos...
    if (filesize($pathFile) == false) {
        //Se obtienen los datos
        $prom = getProm();
        writeDataFile($pathFile,$prom);
    } else {
        $prom = readDataFile($pathFile);
    }

    return $prom;
}

function loadPromPerAlumno () {
    //Se declara la ruta del archivo de datos
    $pathFile = '../files/prom.txt';

    //Si el archivo no tiene datos...
    if (filesize($pathFile) == false) {
        //Se obtienen los datos
        $prom = getPromPerAlumno();
        $user = new $User();
        $User.setProm($prom);
        
        writeDataFile($pathFile,$User);
    } else {
        $prom = readDataFile($pathFile);
    }

    return $prom;
}

if (textoTieneDatos) {
    if ($arrayName.contains($id_alumno)){
        if (alumno.hasPromedio()){
            return alumno.getPromedio();
        }else {
            alumno.setPromedio();
        }
    } else {
        $alumno = new Alumno();
        $alumno.setPromedio($promedio)
        array.push($arrayName,$alumno);
    }
} else {
    $arrayName = array();
    $alumno = new Alumno();
    $alumno.setPromedio($promedio)
    array.push($arrayName,$alumno);
}

function clearData () {
    $pathFile = '../files/prom.txt';
    $pathFile = '../files/prom.txt';
    $pathFile = '../files/prom.txt';
    $pathFile = '../files/prom.txt';
    $pathFile = '../files/prom.txt';
    $pathFile = '../files/prom.txt';

    writeDataFile($pathFile,"");
}


function writeDataFile ($pathFile, $data) {
    $archivo = fopen($pathFile,'w+');
    fwrite($archivo,json_encode($data));
    fclose($archivo);
}

function readDataFile ($pathFile) {
    $archivo = fopen($pathFile,'r');
    $lectura = fread($archivo,filesize($pathFile));
    $data = json_decode($lectura);
    fclose($archivo);
    return $data;
}


?>