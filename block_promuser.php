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
        $this->content = new stdClass;
        $courseId = $COURSE->id;


        //Si ya existe el contenido, volverlo a mostrar
        if ($this->content !== null) {
            return $this->content;
        }
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------

        //Se incluyen las vistas tipo "modal"
        include('views/activitiesByInterval.php');
        include('views/promTimeByUser.php');
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


        //Se declara el código HTML del bloque PromUser
        $contentPromUser = <<< EOT
            <button onclick="showInformation()" id="buttonShowInformation" style="width: 100%;" class="btn btn-sm btn-warning">
                Obtener tiempos promedio
            </button>

            <br>
            <span style='color:blue'>
                El tiempo promedio que estuvo el grupo en las actividades del curso en una sesión es de: <strong id='generalInformation1'>-:-:-</strong> hrs/min/seg.
            </span>
            <br>

            <br>
            <span style='color: green'>
                El tiempo promedio que estuvo el grupo en las actividades del curso por día es de: <strong id='generalInformation2'>-:-:-</strong> hrs/min/seg.
            </span>
            <hr>

            <h6>Tiempo promedio del grupo por actividad</h6>

            <div id='buttonActivitiesPerInterval'>
                <button id="modalButton1" onclick="showTableActivitiesPerInterval()" style="width: 100%;" type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">
                    Ver detalle
                </button>
            </div>
            
            <div id='buttonCSV'></div>
            <div id='buttonCSVInterval'></div>
            <hr>

            <div id='buttonActivitiesPerInterval'></div>

            <h6>Tiempo promedio del grupo por día</h6>

            <div id='buttonActivitiesPerDay'>
                <button id="modalButton2" onclick="showTableActivitiesPerDay()" style="width: 100%;" type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal"> 
                    Ver detalle
                </button>
            </div>

            <div id='graphic-class' class='btn btn-sm btn-primary mt-1' onclick='showGraphicClassNetworks()'>
                Mostrar gráfico
            </div>
            <hr>

            <h6>Seleccione un usuario para desplegar sus estadísticas de acceso:</h6>
            
            <select style="max-width: 100%; min-width: 100%;" id="selectUserId">
            
            $selectOptions

            </select>
            <div style='margin-top:10px;' id='buttonPromByUser'>
                <button id="modalButton3" style="width: 100%;" onclick="showGraphicByUser()" type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalByUser">
                    <i class="far fa-chart-bar"></i>
                    Ver tiempo promedio por día
                </button>
            </div> 
            
            <div id="loading" style="display:none; width: 100%;">
                <span>Generando...</span>
            </div>


            <script src='../blocks/promuser/main/main.js'></script>
        EOT;
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------


        //Se retorna el contenido declarado para el bloque de PromUser
        $this->content->text = $contentPromUser;
        return $this->content;
        //--------------------------------------------------------------------------------------------------------------------------------------------------------------------
    }
}
?>