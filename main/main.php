<?php

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


    %selectOptions%


    </select>
    <div style='margin-top:10px;' id='buttonPromByUser'>
    <button id="modalButton3" style="width: 100%;" onclick="showUserSelectedAverages()" type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalByUser">
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
