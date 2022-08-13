<?php

function getStudentRoleId () {
    global $DB;
    return $DB->get_record_sql("
        SELECT id 
        FROM mdl_role 
        WHERE shortname = 'student';
    ")->id;
}

function getCourseContextId () {
    global $DB;
    global $COURSE;
    return $DB->get_record_sql("
        SELECT id 
        FROM mdl_context 
        WHERE contextlevel = 50 
            AND 
            instanceid = " . getCourseId() . ";"
    )->id;
}

function getUsers () {
    global $DB;
    return $DB->get_records_sql("
        SELECT id, userid, username, firstname, lastname, email 
        FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
        GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
        WHERE user_role.by_role = 1 AND user_role.roles = " . getStudentRoleId() . " AND user_role.contextid = " . getCourseContextId() . ") data_role
        INNER JOIN mdl_user users ON data_role.userid = users.id;
    ");
}

function getCourseId () {
    global $COURSE;
    return $COURSE->id;
}

?>