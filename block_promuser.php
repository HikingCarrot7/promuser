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
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

    //Se incluyen las vistas tipo "modal"
    include('database/Queries.php');
    include('views/activitiesByInterval.php');
    include('views/promTimeByUser.php');
    //include('main/main.php');
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


    //Se imprimen los usuarios obtenidos
    $usuarios = getUsers();
    echo ('<script>console.log(' . json_encode($usuarios) . ')</script>');

    //Se declara la variable courseId para el JavaScript
    echo ("<script> var courseId = " . getCourseId() . "</script>");

    //Se genera el enlace con el CSS correspondiente
    echo ('<link rel="stylesheet" href="../blocks/promuser/main/main.css">');
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


    //Se genera el código HTML para el select de alumnos
    /*$selectOptions = "";
    foreach ($usuarios as $aUser) {
      $selectOptions .= '<option value="' . $aUser->id . '">' . $aUser->firstname . " " . $aUser->lastname . "</option>";
    }*/
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


    //$contentPromUser = str_replace('%selectOptions%', $selectOptions, $contentPromUser);

    $lineas = file('main/main.php');
    $output = "";
    foreach ($lineas as $line_num => $linea) { 
      //recorremos todas las líneas HTML devueltas por la página
      $output.= "Line #{$line_num} : " . htmlspecialchars($linea) . "\n";
    }

    //Se retorna el contenido declarado para el bloque de PromUser
    
    $this->content->text = $output;
    return $this->content;
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
  }
}
