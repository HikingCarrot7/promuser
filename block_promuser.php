<?php
require_once(dirname(__FILE__) . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

class block_promuser extends block_base {

    public function init() {
        $this->title = get_string('promuser', 'block_promuser');
        $this->$variableCSV = array();
    }

    public function setIdUserPHP() {
        return 'document.getElementById(\'selectUserId\').options[document.getElementById("selectUserId").selectedIndex].value';
    }

    public function get_content() {
        global $DB;
        global $COURSE;
        global $USER;
        $courseId = $COURSE->id;


        //Si ya existe el contenido lo vuelve a mostrar, sino, crea nuevo contenido
        if ($this->content !== null) {
            return $this->content;
        }else{
            $this->content = new stdClass;
        }
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

        //Se incluyen las vistas tipo "modal"
        include('views/activitiesByInterval.php');
        include('views/promTimeByUser.php');
        include('main/main.php');
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


        //Se obtienen los datos necesarios de la BD
        $id_role_student = $DB->get_record_sql("
            SELECT id 
            FROM mdl_role 
            WHERE shortname = 'student';"
        )->id;

        $contextId = $DB->get_record_sql("
            SELECT id 
            FROM mdl_context 
            WHERE contextlevel = 50 
                AND 
                instanceid = " . $COURSE->id . ";"
        )->id;

        $resultadoUsers = $DB->get_records_sql("
            SELECT id, userid, username, firstname, lastname, email 
            FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
            GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
            WHERE user_role.by_role = 1 AND user_role.roles = " . $id_role_student . " AND user_role.contextid = " . $contextId . ") data_role
            INNER JOIN mdl_user users ON data_role.userid = users.id;"
        );
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


        //Se referencian los documentos externos
        echo ('<script>console.log(' . json_encode($resultadoUsers) . ')</script>');
        echo ("<script> var courseId = " . $courseId . "</script>");
        echo ('<link rel="stylesheet" href="../blocks/promuser/main/main.css">');
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


        //Se genera el código HTML para el select de alumnos
        $selectOptions = "";
        foreach ($resultadoUsers as $aUser) {
            $selectOptions .= '<option value="' . $aUser->id . '">' . $aUser->firstname . " " . $aUser->lastname . "</option>";
        }
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


        $contentPromUser = str_replace('%selectOptions%',$selectOptions,$contentPromUser);

        //Se retorna el contenido declarado para el bloque de PromUser
        $this->content->text = $contentPromUser;
        return $this->content;
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
    }
}
?>