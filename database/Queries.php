<?php

function getStudentRoleId () {
    global $DB;
    return $DB->get_record_sql("
        SELECT id 
        FROM mdl_role 
        WHERE shortname = 'student';
    ")->id;
}

function getCourseContextId ($course_id) {
    global $DB;
    return $DB->get_record_sql("
        SELECT id 
        FROM mdl_context 
        WHERE contextlevel = 50 
            AND 
            instanceid = " . $course_id . ";"
    )->id;
}

function getUsers ($course_id) {
    global $DB;
    return $DB->get_records_sql("
        SELECT id, userid, username, firstname, lastname, email 
        FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
        GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
        WHERE user_role.by_role = 1 AND user_role.roles = " . getStudentRoleId() . " AND user_role.contextid = " . getCourseContextId($course_id) . ") data_role
        INNER JOIN mdl_user users ON data_role.userid = users.id;
    ");
}

function getLogs ($idAlumno, $user_id, $extra_indications) {
    global $DB;
    return $DB->get_records_sql("
        SELECT * 
        FROM mdl_logstore_standard_log 
        WHERE (userid = ".$idAlumno.") AND 
            (target != 'config_log') AND 
            (userid <> ".$user_id.") " . 
            $extra_indications . 
            ";"
        );
}



CLASE GRUPO {

    setAlumnos();

    getAlumnos();

    getAlumnosId(){
        return abreArrayYObtenSoloLosID();
    };

}

CLASE ALUMNO {

    $id;
    $prom;
    $logs;
    $promByDay;
    $promByActivity;


    calculateProm();

    calculatePromByDay();

    
}



function getUsersInThisCourse ($course_id) {
    global $DB;
    return $DB->get_records_sql("
        SELECT id, userid, username, firstname, lastname, email 
        FROM (
            SELECT * 
            FROM (
                SELECT 
                    userid, 
                    contextid,
                    COUNT(*) AS by_role,
                    GROUP_CONCAT(roleid) AS roles 
                FROM mdl_role_assignments 
                GROUP BY userid, contextid
            ) AS user_role
            WHERE 
                user_role.by_role = 1 
                    AND 
                user_role.roles = ".getStudentRoleId()." 
                    AND 
                user_role.contextid = ".getCourseContextId ($course_id)."
        ) AS data_role
        INNER JOIN mdl_user users ON data_role.userid = users.id;");
}

function getAccesses ($idAlumno, $course_id) {
    global $DB;
    return $DB->get_records_sql("
        SELECT 
            * 
        FROM 
            mdl_logstore_standard_log 
        WHERE 
            (userid = ".$idAlumno." 
                AND 
            action = 'loggedin') 
                    OR 
            (target = 'course' 
                AND 
            action = 'viewed' 
                AND 
            courseid = ".$course_id." 
                AND 
            userid = ".$idAlumno.") 
        ORDER BY 
            timecreated ASC;"
    );
}

function getUserData ($idUser) {
    global $DB;
    return $DB->get_record_sql("
        SELECT 
            id, 
            firstname, 
            lastname 
        FROM 
            mdl_user 
        WHERE 
            (id = " . $idUser . ")");
}
?>