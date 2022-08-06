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

    if ($this->content !== null) {
      return $this->content;
    }

    include('views/activitiesByInterval.php');
    include('views/promTimeByUser.php');
    $this->content = new stdClass;

    $id_role_student = $DB->get_record_sql("SELECT id FROM mdl_role WHERE shortname = 'student';")->id;
    $contextId = $DB->get_record_sql("SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = " . $COURSE->id . ";")->id;

    $resultadoUsers = $DB->get_records_sql("SELECT id, userid, username, firstname, lastname, email FROM (SELECT * FROM (SELECT userid, contextid,COUNT(*) AS by_role,
        GROUP_CONCAT(roleid) AS roles FROM mdl_role_assignments GROUP BY userid, contextid) user_role
        WHERE user_role.by_role = 1 AND user_role.roles = " . $id_role_student . " AND user_role.contextid = " . $contextId . ") data_role
        INNER JOIN mdl_user users ON data_role.userid = users.id;");

    echo ('<script>console.log(' . json_encode($resultadoUsers) . ')</script>');

    $cdnLinks = "";
    $promByLogInOut = "<style>
          .popover__title {
            font-size: 24px;
            text-decoration: none;
            text-align: center;
            margin: 0px;
          }
          
          .popover__wrapper {
            position: relative;
            display: inline-block;
          }
          .popover__content {
            opacity: 0;
            visibility: hidden;
            position: absolute;
            transform: translate(0, 10px);
            left: -32px;
            margin-top: 30px;
            background-color: white;
            padding: 2px;
            padding-top: 4px;
            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
            width: 200px;
            font-size: 12px;
            font-weight: normal;
            border-radius: 3px;
          }
          .popover__content p {
              margin: 0px;
          }
          .popover__content:before {
            position: absolute;
            z-index: -1;
            content: \"\";
            right: calc(80% - 10px);
            top: -8px;
            border-style: solid;
            border-width: 0 10px 10px 10px;
            border-color: transparent transparent white transparent;
            transition-duration: 0.3s;
            transition-property: transform;
          }
          .popover__wrapper:hover .popover__content {
            z-index: 10;
            opacity: 1;
            visibility: visible;
            transform: translate(0, -20px);
            transition: all 0.5s cubic-bezier(0.75, -0.02, 0.2, 0.97);
          }
          .popover__message {
            text-align: center;
          }
        </style>
        <br><span style='color:blue'>El tiempo promedio que estuvo el grupo en las actividades del curso en una sesión es de: <strong id='generalInformation1'>-:-:-</strong> hrs/min/seg.</span><br><br><span style='color: green'>El tiempo promedio que estuvo el grupo en las actividades del curso por día es de: <strong id='generalInformation2'>-:-:-</strong> hrs/min/seg.</span><hr>";
    $promByLogInOut = $promByLogInOut . "<h6>Tiempo promedio del grupo por actividad</h6><div id='buttonActivitiesPerInterval'></div><div id='buttonCSV'></div><div id='buttonCSVInterval'></div><hr><div id='buttonActivitiesPerInterval'></div>
        <script>
        function setCSV(){
            var windowLocationS = window.location;
            var pathnameS = windowLocationS.pathname.split('/');
            var directoryS = windowLocationS.origin + '/' + pathnameS[1] + '/blocks/promuser/csv/downloadCSV.php?idCourse=' + " . $courseId . ";
            var directorySInterval = windowLocationS.origin + '/' + pathnameS[1] + '/blocks/promuser/csv/downloadCSVInterval.php?idCourse=' + " . $courseId . ";
            document.getElementById('buttonCSV').innerHTML = '<a href='+directoryS+' class=\"btn btn-sm btn-success\" style=\"margin-top:3px;\">Exportar .csv de Recursos</a>';
            document.getElementById('buttonCSVInterval').innerHTML = '<a href='+directorySInterval+' class=\"btn btn-sm btn-success\" style=\"margin-top:3px;\">Exportar .csv de Accesos</a>';
        }
        setCSV();
        </script>
        ";
    $promByDay = "";
    $promByDay = $promByDay . "<style>.image a img {width: 100%;}</style><h6>Tiempo promedio del grupo por día</h6><div id='buttonActivitiesPerDay'></div><div id='graphic-class' class='btn btn-sm btn-primary mt-1' onclick='showGraphicClassNetworks()'>Mostrar gráfico</div><hr>";

    $selectUsers = $cdnLinks . $promByLogInOut . $promByDay;


    $selectUsers = $selectUsers . "<h6>Seleccione un usuario para desplegar sus estadísticas de acceso:</h6>";

    $selectUsers = $selectUsers . '<select style="max-width: 100%; min-width: 100%;" id="selectUserId">';

    foreach ($resultadoUsers as $ru) {
      $selectUsers = $selectUsers . '<option value="' . $ru->id . '">' . $ru->firstname . " " . $ru->lastname . "</option>";
    }
    $selectUsers = $selectUsers . "</select>";
    $selectUsers = $selectUsers . "<div style='margin-top:10px;' id='buttonPromByUser'></div>";

    $selectUsers = '<button onclick="showInformation()" id="buttonShowInformation" style="width: 100%;" class="btn btn-sm btn-warning">Obtener tiempos promedio</button><div id="loading" style="display:none; width: 100%;"><span>Generando...</span></div>' . $selectUsers . '<script>
        function showInformation() {
            let results = getTotalResults();
            if(results[0] == null) {
                document.getElementById("loading").style.display = "block";
            }else{
                document.getElementById("generalInformation1").innerHTML = results[0];
                document.getElementById("generalInformation2").innerHTML = results[1];
            }
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("loading").style.display = "none";
                    jsonResponse = JSON.parse(this.responseText);
                    time1 = jsonResponse[0];
                    let hours = Math.floor( time1 / 3600 );  
                    let minutes = Math.floor( (time1 % 3600) / 60 );
                    let seconds = time1 % 60;
                    
                    //Anteponiendo un 0 a los minutos si son menos de 10 
                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    
                    //Anteponiendo un 0 a los segundos si son menos de 10 
                    seconds = seconds < 10 ? "0" + seconds : seconds;
                    
                    let result1 = hours + ":" + minutes + ":" + seconds;
                    localStorage.setItem("totalPromResult1", result1);
                    document.getElementById("generalInformation1").innerHTML = result1;

                    time2 = jsonResponse[1];
                    let hours1 = Math.floor( time2 / 3600 );  
                    let minutes1 = Math.floor( (time2 % 3600) / 60 );
                    let seconds1 = time2 % 60;
                    
                    //Anteponiendo un 0 a los minutos si son menos de 10 
                    minutes1 = minutes1 < 10 ? "0" + minutes1 : minutes1;
                    
                    //Anteponiendo un 0 a los segundos si son menos de 10 
                    seconds1 = seconds1 < 10 ? "0" + seconds1 : seconds1;
                    
                    let result2 = hours1 + ":" + minutes1 + ":" + seconds1;
                    localStorage.setItem("totalPromResult2", result2);
                    document.getElementById("generalInformation2").innerHTML = result2;
                }
            };
            var e = document.getElementById("selectUserId");
            var strUser = e.options[e.selectedIndex].value;
            var params = "idUser="+ strUser + "&idCourse=" + ' . $courseId . ';
            var windowLocation = window.location;
            var pathname = windowLocation.pathname.split("/");
            var directory = windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/helpers/generalInformation.php";
            console.log(directory);
            xhttp.open("POST", directory , true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        }
        function getTotalResults() {
            let result1 = localStorage.getItem("totalPromResult1");
            let result2 = localStorage.getItem("totalPromResult2");

            return [result1, result2]
        };
        function setProm(){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    generateTableUser(JSON.parse(this.responseText));
                }
            };
            var e = document.getElementById("selectUserId");
            var strUser = e.options[e.selectedIndex].value;
            var params = "idUser="+ strUser + "&idCourse=" + ' . $courseId . ';
            var windowLocation = window.location;
            var pathname = windowLocation.pathname.split("/");
            var directory = windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/helpers/getProm.php";

            xhttp.open("POST", directory , true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        };
        function setPromUser(){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    setPromByDay(JSON.parse(this.responseText));
                }
            };
            var e = document.getElementById("selectUserId");
            var strUser = e.options[e.selectedIndex].value;
            var params = "idUser="+ strUser + "&idCourse=" + ' . $courseId . ';
            var windowLocationPromDay = window.location;
            var pathnamePromDay = windowLocationPromDay.pathname.split("/");
            var directoryPromByDay = windowLocationPromDay.origin + "/" + pathnamePromDay[1] + "/blocks/promuser/helpers/getPromByDayUser.php";

            xhttp.open("POST", directoryPromByDay, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        }
        function showGraphic(){
            var windowLocation = window.location;
            var pathname = windowLocation.pathname.split("/");
            var directory = windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/helpers/getProm.php";
            window.open(windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/graphics/graphic.php?var="+ document.getElementById(\'selectUserId\').options[document.getElementById("selectUserId").selectedIndex].value +  \'&courseVar=' . $courseId . '\');
        }
        function showGraphicNetworks(){
            var windowLocation = window.location;
            var pathname = windowLocation.pathname.split("/");
            window.open(windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/graphics/graphicNetwork.php?var="+ document.getElementById(\'selectUserId\').options[document.getElementById("selectUserId").selectedIndex].value +  \'&courseVar=' . $courseId . '\');
        }
        function showGraphicClassNetworks() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    setPromActivitiesPerStudentTable(JSON.parse(this.responseText));
                    document.getElementById(\'graphic-class\').innerHTML = \'Mostrar gráfico\';
                }
            };
            var windowLocationPromDay = window.location;
            var pathnamePromDay = windowLocationPromDay.pathname.split("/");
            var directoryPromByDay = windowLocationPromDay.origin + "/" + pathnamePromDay[1] + "/blocks/promuser/tables/table_getPromActivitiesPerStudent.php";
            var params = "idCourse=" + ' . $courseId . ';
            document.getElementById(\'graphic-class\').innerHTML = \'Generando...\';
            xhttp.open("POST", directoryPromByDay, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
            //-----------------------------------------------------------------------------            
        }
        function setLoadingGraph() {
            document.getElementById(\'graphic-class\').innerHTML = \'Generando...\';
        }
        function setPromActivitiesPerStudentTable(json_data) {
            sessionStorage.setItem("classInformationByDay", JSON.stringify(json_data));

            var windowLocation = window.location;
            var pathname = windowLocation.pathname.split("/");
            window.open(windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/graphics/graphicClassNetwork.php?classInformationByDay");
        }
        function showTableActivitiesPerInterval(){
            setEmptyTable("interval");
        }
        function updateTableActivitiesPerInterval() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    setPromActivitiesPerOptionInterval(JSON.parse(this.responseText));
                    $("#updateTable").text("Actualizar datos");
                }
            };
            $("#updateTable").text("Cargando...");
            var windowLocationPromDay = window.location;
            var pathnamePromDay = windowLocationPromDay.pathname.split("/");
            var directoryPromByDay = windowLocationPromDay.origin + "/" + pathnamePromDay[1] + "/blocks/promuser/tables/table_getPromActivitiesPerOption.php";
            var params = "option=interval&idCourse=" + ' . $courseId . ';
            xhttp.open("POST", directoryPromByDay, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        }

        function showTableActivitiesPerDay(){
            setEmptyTable("day");
        }
        function updateTableActivitiesPerDay() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    setPromActivitiesPerOptionDay(JSON.parse(this.responseText));
                    $("#updateTable").text("Actualizar datos");
                }
            };
            $("#updateTable").text("Cargando...");
            var windowLocationPromDay = window.location;
            var pathnamePromDay = windowLocationPromDay.pathname.split("/");
            var directoryPromByDay = windowLocationPromDay.origin + "/" + pathnamePromDay[1] + "/blocks/promuser/tables/table_getPromActivitiesPerOption.php";
            var params = "option=day&idCourse=" + ' . $courseId . ';
            xhttp.open("POST", directoryPromByDay, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        }

        function setPromActivitiesPerOptionInterval(json_data){
            localStorage.setItem("PromActivitiesPerOptionInterval", JSON.stringify(json_data));
            changeTitleModal("Tiempo promedio del grupo por actividad");
            let first_date = new Date(json_data["first_date"]["date"]);
            first_date = first_date.getDay() + "/" + (first_date.getMonth() + 1) + "/" + first_date.getFullYear() + " " + first_date.getHours() + ":" + first_date.getMinutes();
            delete json_data["first_date"];
            let last_date = new Date(json_data["last_date"]["date"]);
            last_date = last_date.getDay() + "/" + (last_date.getMonth() + 1) + "/" + last_date.getFullYear() + " " + last_date.getHours() + ":" + last_date.getMinutes();
            delete json_data["last_date"];
            generateTable(json_data, first_date, last_date);
        }
        function setPromActivitiesPerOptionDay(json_data){
            localStorage.setItem("PromActivitiesPerOptionDay", JSON.stringify(json_data));
            changeTitleModal("Tiempo promedio del grupo por día");
            let first_date = new Date(json_data["first_date"]["date"]);
            first_date = first_date.getDay() + "/" + (first_date.getMonth() + 1) + "/" + first_date.getFullYear() + " " + first_date.getHours() + ":" + first_date.getMinutes();
            delete json_data["first_date"];
            let last_date = new Date(json_data["last_date"]["date"]);
            last_date = last_date.getDay() + "/" + (last_date.getMonth() + 1) + "/" + last_date.getFullYear() + " " + last_date.getHours() + ":" + last_date.getMinutes();
            delete json_data["last_date"];
            generateTable(json_data, first_date, last_date);
        }
        function showGraphicByUser(){
            var userName = document.getElementById(\'selectUserId\').options[document.getElementById("selectUserId").selectedIndex].text;
            var userId = document.getElementById(\'selectUserId\').options[document.getElementById("selectUserId").selectedIndex].value;
            setProm();
            setPromUser();
            changeTitleModalUser("Tiempo promedio por día");
            changeStudentNameModalUser(userName)
        }
        </script>';


    $this->content->text = $selectUsers;
    return $this->content;
  }
}
