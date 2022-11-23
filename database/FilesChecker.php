<?php
require_once(dirname(__FILE__) . '/../models/ContextData.php');
require_once(dirname(__FILE__) . '/../models/Group.php');
require_once(dirname(__FILE__) . '/../models/Student.php');

//Funciones de inicialización ---------------------------------------------------------------------

function initializeContext ($courseId, $professorId) {
    mkdir(dirname(__FILE__) . '/../files');
    $context = new ContextData($courseId);
    $context->courseContextId = getCourseContextId($courseId);
    $context->studentRoleId = getStudentRoleId();
    $context->professorId = $professorId;
    writeContextFile($context);
}

function loadUsers () {
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

//-------------------------------------------------------------------------------------------------

//Funciones para obtener datos del contexto -------------------------------------------------------


function loadCourseContextId () {
    $context = readContextFile();
    return $context->courseContextId;
}


function loadStudentRoleId () {
    $context = readContextFile();
    return $context->studentRoleId;
}


function loadCourseId () {
    $context = readContextFile();
    return $context->courseId;
}


function loadProfessorId () {
    $context = readContextFile();
    return $context->professorId;
}


//-------------------------------------------------------------------------------------------------

//Funciones para obtener datos del grupo ----------------------------------------------------------

function getUsersIds () {
    $ids = array();
    $users = loadUsers();
    foreach ($users as $key => $aUser) {
        $ids[$key] = $aUser->id;
    }
    return $ids;
}

function loadGroupSATS () {
    $group = readGroupFile();
    $context = readContextFile();

    if ($group->SATS == null) {
        $group->getSemesterAvgTimeSpent($context->professorId);
        writeGroupFile($group);
    }

    return $group->SATS;
}

function loadGroupSATPD () {
    $group = readGroupFile();
    $context = readContextFile();

    if ($group->SATPD == null) {
        $group->getSemesterAvgTimePerDay($context->professorId);
        writeGroupFile($group);
    }

    return $group->SATPD;
}

//-------------------------------------------------------------------------------------------------

//Funciones para obtener datos de estudiantes -----------------------------------------------------

function getAnStudent ($id) {
    $students = readStudentsFile();
    
    if ($students[$id] == null) {
        $student = new Student($id);
        $data = getUserData($id);

        $student->firstname = $data->firstname;
        $student->lastname = $data->lastname;
    }else {
        $student = $students[$id];
    }

    return $student;
}

function loadLogs ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->logs == null) {
        $extraIndications = "ORDER BY timecreated ASC"; // Indicacines para la búsqueda en BD
        $student->logs = getLogs($id, $context->professorId, $extraIndications);
        updateAnStudent($student);
    }

    return $student->logs;
}

function loadFirstLog ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->firstLog == null) {
        $extraIndications = "ORDER BY timecreated ASC LIMIT 1";// Indicacines para la búsqueda en BD
        $student->firstLog = getLogs($id, $context->professorId, $extraIndications);
        updateAnStudent($student);
    }

    return $student->firstLog;
}

function loadLastLog ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->lastLog == null) {
        $extraIndications = "ORDER BY timecreated DESC LIMIT 1";// Indicacines para la búsqueda en BD
        $student->lastLog = getLogs($id, $context->professorId, $extraIndications);
        updateAnStudent($student);
    }

    return $student->lastLog;
}

function loadLogins ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->logins == null) {
        $student->logins = getLogins($id, $context->courseId);
        updateAnStudent($student);
    }

    return $student->logins;
}

function loadSAC ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SAC == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAccessesCount($professorId);
        updateAnStudent($student);
    }

    return $student->SAC;
}

function loadSATSPPDCSV ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SATSPPDCSV == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAvgTimeSpentPerActivityPerDayCSV($context->courseId);
        updateAnStudent($student);
    }

    return $student->SATSPPDCSV;
}

function loadSATSPAPD ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SATSPAPD == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAvgTimeSpentPerActivityPerDay($context->courseId);
        updateAnStudent($student);
    }

    return $student->SATSPAPD;
}

function loadSATSPA ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SATSPA == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAvgTimeSpentPerActivity($context->courseId);
        updateAnStudent($student);
    }

    return $student->SATSPA;
}

function loadSATSPD ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SATSPD == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAvgTimeSpentPerDay($context->courseId);
        updateAnStudent($student);
    }

    return $student->SATSPD;
}

function loadSATSCSV ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SATSCSV == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAvgTimeSpentCSV($context->courseId);
        updateAnStudent($student);
    }

    return $student->SATSCSV;
}

function loadSATS ($id) {
    $context = readContextFile();
    $student = getAnStudent($id);

    if ($student->SATS == null) {
        if ($student->logs == null) {
            $student->logs = loadLogs($id);
        }
        $student->getSemesterAvgTimeSpent($context->courseId);
        updateAnStudent($student);
    }

    return $student->SATS;
}

function updateAnStudent ($student) {
    $students = readStudentsFile();
    $students[$student->id] = $student;
    writeStudentsFile($students);
}

//-------------------------------------------------------------------------------------------------

//Funciones de lectura y escritura para archivos ContextData, Group y Student ---------------------

function writeContextFile ($data) {
    $pathFile = dirname(__FILE__) . '/../files/ContextData.txt';
    writeDataFile($pathFile, $data);
}

function readContextFile () {
    $pathFile = dirname(__FILE__) . '/../files/ContextData.txt';
    $data = readDataFile($pathFile);
    $context = new ContextData($data->courseId);

    $context->courseContextId = $data->courseContextId;
    $context->studentRoleId = $data->studentRoleId;
    $context->professorId = $data->professorId;

    return $context;
}

function readGroupFile () {
    $pathFile = dirname(__FILE__) . '/../files/Group.txt';
    $group = new Group();
    $data = readDataFile($pathFile);

    $group->users = $data->users;
    $group->SATS = $data->SATS;
    $group->SATPD = $data->SATPD;

    return $group;
}

function writeGroupFile ($data) {
    $pathFile = dirname(__FILE__) . '/../files/Group.txt';
    writeDataFile($pathFile, $data);
}

function readStudentsFile () {
    $pathFile = dirname(__FILE__) . '/../files/Students.txt';
    $students = array();
    $data = readDataFile($pathFile);

    foreach ($data as $key => $aStudent) {
        $student = new Student($aStudent->id);

        $student->firstname = $aStudent->firstname;
        $student->lastname = $aStudent->lastname;
        $student->logs = $aStudent->logs;
        $student->firstLog = $aStudent->firstLog;
        $student->lastLog = $aStudent->lastLog;
        $student->logins = $aStudent->logins;

        $student->SATS = $aStudent->SATS;
        $student->SATSCSV = $aStudent->SATSCSV;
        $student->SATSPD = $aStudent->SATSPD;
        $student->SATSPA = $aStudent->SATSPA;
        $student->SATSPAPD = $aStudent->SATSPAPD;
        $student->SATSPPDCSV = $aStudent->SATSPPDCSV;
        $student->SAC = $aStudent->SAC;

        $students[$key] = $student;
    }

    return $students;
}

function writeStudentsFile ($data) {
    $pathFile = dirname(__FILE__) . '/../files/Students.txt';
    writeDataFile($pathFile, $data);
}

//-------------------------------------------------------------------------------------------------

//Funciones genéricas de lectura y escritura de archivos ------------------------------------------

function writeDataFile ($pathFile, $data) {
    $writtenBytes = file_put_contents($pathFile, json_encode($data));
    return $writtenBytes;
}

function readDataFile ($pathFile) {
    $data = json_decode(file_get_contents($pathFile));
    return $data;
}

//-------------------------------------------------------------------------------------------------