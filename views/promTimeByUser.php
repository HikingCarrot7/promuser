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
    <script src="../blocks/promuser/views/js/promTimeByUserScript.js"></script>
</head>
<body>

<div class="modal fade hide" id="modalByUser" tabindex="-1" role="dialog" aria-labelledby="modalByUserLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalByUserLabel">Tiempo promedio por usuario</h5>
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
</html>
