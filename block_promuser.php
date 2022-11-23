<?php
require_once(dirname(__FILE__) . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

class block_promuser extends block_base {

    public function init() {
        $this->title = get_string('promuser', 'block_promuser');
    }

    public function get_content() {
        global $COURSE;
        global $USER;
        //Si ya existe el contenido lo vuelve a mostrar, sino, crea nuevo contenido
        if ($this->content !== null) {
            return $this->content;
        }

        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

        //Se incluyen las vistas tipo "modal"
        include('database/Queries.php');
        include('database/FilesChecker.php');
        include('views/activitiesByInterval.php');
        include('views/promTimeByUser.php');
        include('main/main.php');
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

        //Se declara la variable courseId para el JavaScript
        echo ("<script> const courseId = " . $COURSE->id . "</script>");

        //Se genera el enlace con el CSS correspondiente
        echo ('<link rel="stylesheet" href="../blocks/promuser/main/main.css">');
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

        initializeContext($COURSE->id, $USER->id);

        $users = loadUsers();

        //Se genera el código HTML para el select de alumnos
        $selectOptions = "";
        foreach ($users as $aUser) {
            $selectOptions .= '<option value="' . $aUser->id . '">' . $aUser->firstname . " " . $aUser->lastname . "</option>";
        }

        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

        $contentPromUser = str_replace('%selectOptions%', $selectOptions, $contentPromUser);

        //Se retorna el contenido declarado para el bloque de PromUser
        $this->content = new stdClass;
        $this->content->text = $contentPromUser;
        return $this->content;
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
    }
}
