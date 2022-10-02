<?php
require_once(dirname(__FILE__) . '/../models/ContextData.php');
require_once(dirname(__FILE__) . '/../models/Group.php');
require_once(dirname(__FILE__) . '/../models/Student.php');

function initializeContext($courseId, $professorId) {
    $context = new ContextData($courseId);
    $context->courseContextId = getCourseContextId($courseId);
    $context->studentRoleId = getStudentRoleId();
    $context->professorId = $professorId;
    writeContextFile($context);
}

function loadUsers() {
    //Se lee el objeto Group
    $group = readGroupFile();
    $context = readContextFile();

    //Si no tiene usuarios, se piden a la BD y se carga en el objeto Group
    if ($group->users == null) {
        $users = getUsersInThisCourse($context->courseId);
        $group->users = $users;
        writeGroupFile($group);
    }

    return $group->users;
}

function loadLogs($id) {
    $students = readStudentsFile(); // Se obtiene el array de students
    $context = readContextFile();
    $extraIndications = "ORDER BY timecreated ASC"; // Indicacines para la búsqueda en BD

    if ($students[$id] == null) {
        $student = new Student($id);
        $student->logs = getLogs($id, $context->professorId, $extraIndications); // Se guardan los logs en el student
        $students[$id] = $student; // Se guarda el student en el array de students
        writeStudentsFile($students);
    } else {
        if ($students[$id]->logs == null) {
            $students[$id]->logs = getLogs($id, $context->professorId, $extraIndications);
            writeStudentsFile($students);
        }
        $student = $students[$id];
    }
    return $student->logs;
}

function getAnStudent($id) {
    $students = readStudentsFile();
    return $students[$id];
}

function getUsersIds() {
    $ids = array();
    $users = loadUsers();
    foreach ($users as $key => $aUser) {
        $ids[$key] = $aUser->id;
    }
    return $ids;
}

function loadCourseContextId() {
    $context = readContextFile();
    return $context->courseContextId;
}

function loadStudentRoleId() {
    $context = readContextFile();
    return $context->studentRoleId;
}

function loadCourseId() {
    $context = readContextFile();
    return $context->courseId;
}

function loadProfessorId() {
    $context = readContextFile();
    return $context->professorId;
}

function loadProm() {
    //Se declara la ruta del archivo de datos
    $pathFile = '../files/prom.txt';

    //Si el archivo no tiene datos...
    if (filesize($pathFile) == false) {
        //Se obtienen los datos
        $prom = getProm();
        writeDataFile($pathFile, $prom);
    } else {
        $prom = readDataFile($pathFile);
    }

    return $prom;
}

//Funciones de lectura y escritura de archivos ------------------------------------------
function writeContextFile($data) {
    $pathFile = dirname(__FILE__) . '/../files/ContextData.txt';
    writeDataFile($pathFile, $data);
}

function readContextFile() {
    $pathFile = dirname(__FILE__) . '/../files/ContextData.txt';
    $data = readDataFile($pathFile);
    $context = new ContextData($data->courseId);

    $context->courseContextId = $data->courseContextId;
    $context->studentRoleId = $data->studentRoleId;
    $context->professorId = $data->professorId;

    return $context;
}

function readGroupFile() {
    $pathFile = dirname(__FILE__) . '/../files/Group.txt';
    $group = new Group();
    $data = readDataFile($pathFile);

    $group->averagePerSession = $data->averagePerSession;
    $group->averagePerDay = $data->averagePerDay;
    $group->users = $data->users;
    $group->averagePerActivity = $data->averagePerActivity;
    $group->averagePerActivityPerDay = $data->averagePerActivityPerDay;

    return $group;
}

function writeGroupFile($data) {
    $pathFile = dirname(__FILE__) . '/../files/Group.txt';
    writeDataFile($pathFile, $data);
}

function readStudentsFile() {
    $pathFile = dirname(__FILE__) . '/../files/Students.txt';
    $students = array();
    $data = readDataFile($pathFile);

    foreach ($data as $key => $aStudent) {
        $student = new Student($aStudent->id);

        $student->name = $aStudent->name;
        $student->logs = $aStudent->logs;
        $student->average = $aStudent->average;
        $student->averagePerActivity = $aStudent->averagePerActivity;
        $student->averagePerDay = $aStudent->averagePerDay;
        $student->averagePerActivityPerDay = $aStudent->averagePerActivityPerDay;

        $students[$key] = $student;
    }

    return $students;
}

function writeStudentsFile($data) {
    $pathFile = dirname(__FILE__) . '/../files/Students.txt';
    writeDataFile($pathFile, $data);
}

function writeDataFile($pathFile, $data) {
    $writtenBytes = file_put_contents($pathFile, json_encode($data));
    return $writtenBytes;
}

function readDataFile($pathFile) {
    $data = json_decode(file_get_contents($pathFile));
    return $data;
}
