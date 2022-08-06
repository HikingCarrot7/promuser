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
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.4.1.js"
    integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
    crossorigin="anonymous"></script>
    <script src="../blocks/promuser/views/js/activitiesByInterval.js"></script>
</head>
<body>
  <div class="modal fade hide" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Tiempo promedio por actividad</h5>
        </div>
        <div class="modal-body">
            <strong>Fecha de inicio: </strong><span class="first-date-interval"></span><br>
            <strong>Fecha final: </strong><span class="last-date-interval"></span><hr>
            <table class="table table-hover table-bordered table-sm">
              <thead class="thead-light">
                <tr>
                  <th scope="col">Actividad</th>
                  <th scope="col">Horas:Minutos:Segundos</th>
                </tr>
              </thead>
              <tbody id="tableDataProm">
                <tr>
                  <th>
                    Generando...
                  </th>
                </tr>
              </tbody>
            </table>
            <div class="text-center">
              <button id="updateTable" class="btn btn-sm btn-warning">Actualizar datos</button><br>
              <span style="font-size: 10px;">*Este proceso suele ocupar algunos minutos* (No cierre la ventana)</span>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
