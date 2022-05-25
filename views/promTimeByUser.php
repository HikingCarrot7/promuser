<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>

<div class="modal fade hide" id="modalByUser" tabindex="-1" role="dialog" aria-labelledby="modalByUserLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalByUserLabel">****</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <span><strong>Estudiante: </strong><span class="student-name">Moisés B.</span></span><hr>
          <strong>Fecha de inicio: </strong><span class="first-date-user"></span><br>
          <strong>Fecha final: </strong><span class="last-date-user"></span><hr>
          <table class="table table-hover table-bordered table-sm">
            <thead class="thead-light">
              <tr>
                <th scope="col">Actividad</th>
                <th scope="col">Horas:Minutos:Segundos</th>
              </tr>
            </thead>
            <tbody id="tableDataPromUser">
            </tbody>
          </table>
          <span><strong>Tiempo promedio del alumno al día: </strong></span>
          <span id="modalByUserPromDay"></span>
          <br><div id="mostrarGrafica"><a style="color:green;cursor:pointer" onclick="showGraphic()">Mostrar gráfico de las sesiones</a></div>
          <div id="mostrarGraficaRedes"><a style="color:blue;cursor:pointer" onclick="showGraphicNetworks()">Mostrar gráfico de relaciones</a></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

</body>
<script>
    function generateTableUser(data_json){
        $('#tableDataPromUser').html("");
        let sortedData = sortProperties(data_json);
        sortedData.reverse().forEach(function(element) {
          let claveString = element[0].substring(4);
          
          let concept = claveString.charAt(0).toUpperCase() + claveString.slice(1);
          let definition = "";
          switch (concept) {
            case "Resource":
              definition = "Elementos que el profesor puede utilizar para apoyar el aprendizaje, como un archivo o un enlace.";
              break;
            case "Assign":
              definition = "Recurso donde los profesores califican y comentan los archivos subidos y las tareas creadas en línea y fuera de línea.";
              break;
            case "Chat":
              definition = "Permite a los participantes mantener un debate sincrónico en tiempo real.";
              break;
            case "Choice":
              definition = "Un profesor formula una pregunta y especifica una opción de múltiples respuestas.";
              break;
            case "Database":
              definition = "Permite a los participantes crear, mantener y buscar en un banco de registros.";
              break;
            case "Feedback":
              definition = "Permite crear y realizar encuestas para recoger opiniones.";
              break;
            case "Forum":
              definition = "Permite a los participantes mantener debates asíncronos.";
              break;
            case "Glossary":
              definition = "Permite a los participantes crear y mantener una lista de definiciones, como un diccionario.";
              break;
            case "Lesson":
              definition = "Para impartir contenidos de forma flexible.";
              break;
            case "Quiz":
              definition = "Permite al profesor diseñar y establecer pruebas de tipo test, que pueden ser puntuadas automáticamente y en las que se muestra la retroalimentación y/o las respuestas correctas.";
              break;
            case "Survey":
              definition = "Permite recoger datos de los alumnos para ayudar a los profesores a conocer su clase y reflexionar sobre su propia enseñanza.";
              break;
            case "Wiki":
              definition = "Una colección de páginas web que cualquiera puede añadir o editar.";
              break;
            case "Workshop":
              definition = "Permite la evaluación entre pares.";
              break;
            case "Book":
              definition = " Recursos de varias páginas con un formato similar al de un libro.";
              break;
            case "File":
              definition = "Una imagen, un documento pdf, una hoja de cálculo, un archivo de sonido, un archivo de vídeo.";
              break;
            case "Folder":
              definition = "Para ayudar a organizar los archivos y una carpeta puede contener otras carpetas.";
              break;
            case "IMS content package":
              definition = "Añade material estático de otras fuentes en el formato estándar del paquete de contenidos IMS.";
              break;
            case "Label":
              definition = "Puede ser unas pocas palabras mostradas o una imagen utilizada para separar los recursos y las actividades en una sección temática, o puede ser una larga descripción o instrucciones.";
              break;
            case "Page":
              definition = "El estudiante ve una única pantalla desplazable que el profesor crea con el robusto editor HTML.";
              break;
            case "Url":
              definition = "Puede enviar al alumno a cualquier lugar al que pueda acceder en su navegador web, por ejemplo Wikipedia.";
              break;
          }

          $('#tableDataPromUser').append("<tr><th style='display: flex; justify-content: space-between;'>"+ claveString.charAt(0).toUpperCase() + 
            claveString.slice(1) + 
            '<div style="margin-left: 5px;" class="popover__wrapper">'+
              '<a href="#">'+
                '<h2 class="popover__title" style="margin-top: -6px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"></path></svg></h2>'+
              '</a>'+
              '<div class="popover__content">'+
                '<p class="popover__message">'+ definition +'</p>'+
              '</div>'+
            '</div>'+
            "</th><td>"+ secondsToHms(element[1]) +
            "</td></tr>");
        })
    }

    function setPromByDay(promUserDay){
        let first_date = new Date(promUserDay["first_date"]["date"]);
        first_date = first_date.getDay() + "/" + (first_date.getMonth() + 1) + "/" + first_date.getFullYear() + " " + first_date.getHours() + ":" + first_date.getMinutes();
        let last_date = new Date(promUserDay["last_date"]["date"]);
        last_date = last_date.getDay() + "/" + (last_date.getMonth() + 1) + "/" + last_date.getFullYear() + " " + last_date.getHours() + ":" + last_date.getMinutes();

        promUserDay = promUserDay['valor_total'];
        var horas = Math.floor(promUserDay/ 3600);
        var minutos = Math.floor((promUserDay % 3600) / 60);
        var segundos = horas % 60;
        minutos = minutos < 10 ? '0' + minutos : minutos;
        segundos = segundos < 10 ? '0' + segundos : segundos;
        var resultado = horas + ":" + minutos + ":" + segundos;
        $('#modalByUserPromDay').html(resultado)
        $('.first-date-user').text(first_date);
        $('.last-date-user').text(last_date);
    }

    function changeTitleModalUser(title){
      $('#modalByUserLabel').html(title);
    }

    function changeStudentNameModalUser(userName){
      $('.student-name').html(userName);
    }

    function secondsToHms(d) {
      d = Number(d);

      var h = Math.floor(d / 3600);
      var m = Math.floor(d % 3600 / 60);
      var s = Math.floor(d % 3600 % 60);

      return ('0' + h).slice(-2) + ":" + ('0' + m).slice(-2) + ":" + ('0' + s).slice(-2);
    }
    $(document).ready(function(){
        $('#buttonPromByUser').html('<button id="modalButton3" style="width: 100%;" onclick="showGraphicByUser()" type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalByUser"><i class="far fa-chart-bar"></i> Ver tiempo promedio por día</button>');
    })
</script>
</html>