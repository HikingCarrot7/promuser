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
    

    //Si ya existe el contenido lo vuelve a mostrar, sino, crea nuevo contenido
    if ($this->content !== null) {
      return $this->content;
    }
    $this->content = new stdClass;
    global $COURSE;
    $courseid = $COURSE->id;

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

    //Se incluyen las vistas tipo "modal"
    include('database/Queries.php');
    include('database/FilesChecker.php');
    include('views/activitiesByInterval.php');
    include('views/promTimeByUser.php');
    include('main/main.php');
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


    //Se declara la variable courseId para el JavaScript
    echo ("<script> var courseId = " . $courseid . "</script>");

    //Se genera el enlace con el CSS correspondiente
    echo ('<link rel="stylesheet" href="../blocks/promuser/main/main.css">');
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
    
    $users = json_decode(loadUsersFile($courseid));
    

    //Se genera el código HTML para el select de alumnos
    $selectOptions = "";
    foreach ($users as $aUser) {
      $selectOptions .= '<option value="' . $aUser->id . '">' . $aUser->firstname . " " . $aUser->lastname . "</option>";
    }
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


    $contentPromUser = str_replace('%selectOptions%', $selectOptions, $contentPromUser);


    //Se retorna el contenido declarado para el bloque de PromUser
      
    $this->content->text = $contentPromUser;
    return $this->content;
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
  }
}
