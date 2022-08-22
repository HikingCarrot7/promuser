<!DOCTYPE html>

<head>
  <title>Gráfica con datos</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.6/nv.d3.css" rel="stylesheet" type="text/css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js" charset="utf-8"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.6/nv.d3.js"></script>
</head>

<body class="with-3d-shadow with-transitions">
  <div style="position: absolute; top: 0; left: 0">
  </div>
  <strong>Tiempo de acceso del estudiante por día</strong>
  <br />
  <span>
    Tiempo promedio del alumno por día basado en todas las fechas:
    <strong id="promTime"></strong>
  </span>
  <div id="chart1" style="height: 95vh"></div>

  <script>
    <?php
    include('index.js')
    ?>
  </script>
</body>
