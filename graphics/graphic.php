<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $COURSE;
global $USER;
include('../database/Queries.php');

$idCourse = $_GET['courseVar'];
$idUser = $_GET['var'];
$extra_indications = "ORDER BY timecreated ASC";
$resultado = getLogs($idUser, $USER->id, $extra_indications);


$anteriorIgual = false;
$anteriorCursoDistinto = true;
$sumaTotal = 0;
$diaVueltaAnterior = new DateTime();
$diaVueltaActual = new DateTime();

$arrayDiferencias = array();
$arrayFechas = array();

$contadorRegistro = 0;


foreach ($resultado as $rs) {
  if ($contadorRegistro == 0) {
    $diaVueltaAnterior = new DateTime(date('Y-m-d', $rs->timecreated));
    $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));
  }
  $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));


  $contadorRegistro += 1;
  $course = $rs->courseid;

  if ($course == $idCourse) {

    if ($anteriorIgual == true) {
      $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
      $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
      $sumaTotal += $diferencia;


      if ($contadorRegistro == sizeof($resultado) && $diaVueltaActual == $diaVueltaAnterior) {
        if ($sumaTotal <= 86400) {
          array_push($arrayDiferencias, $sumaTotal);
          array_push($arrayFechas, $diaVueltaAnterior);
        }
        $sumaTotal = 0;
        $anteriorCursoDistinto = true;
        $anteriorIgual = false;
      }
      $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
      $anteriorIgual = true;
    } else {
      $inicio = new DateTime(date('Y-m-d H:i:s', $rs->timecreated));
      $anteriorIgual = true;
    }
    $anteriorCursoDistinto = false;
  } else {
    if ($anteriorCursoDistinto == false) {
      $diaVueltaActual = new DateTime(date('Y-m-d', $rs->timecreated));

      $diferencia = $inicio->diff(new DateTime(date('Y-m-d H:i:s', $rs->timecreated)));
      $diferencia = (($diferencia->days * 24) * 60) + ($diferencia->i * 60) + $diferencia->s;
      $sumaTotal += $diferencia;

      if ($sumaTotal <= 86400) {
        array_push($arrayDiferencias, $sumaTotal);
        array_push($arrayFechas, $diaVueltaAnterior);
      }
      $sumaTotal = 0;
      $anteriorCursoDistinto = true;
      $anteriorIgual = false;
    }
  }
  if ($diaVueltaAnterior != $diaVueltaActual && $course == $idCourse) {
    if ($sumaTotal <= 86400) {
      array_push($arrayDiferencias, $sumaTotal);
      array_push($arrayFechas, $diaVueltaAnterior);
    }
    $sumaTotal = 0;
    $anteriorCursoDistinto = true;
    $anteriorIgual = false;
  }
  $diaVueltaAnterior = new DateTime(date('Y-m-d', $rs->timecreated));
}

$sumaPromediosTotal = 0;
$contadorProm = 0;
$arrayPromediosPorDia = array();

foreach ($arrayDiferencias as $key => $promedio) {
  if ($contadorProm == 0) {
    $fAnterior = $arrayFechas[$contadorProm];
  }
  $fActual = $arrayFechas[$contadorProm];

  if ($fActual == $fAnterior) {
    $sumaPromediosTotal += $promedio;
    if ($contadorProm == (sizeof($arrayDiferencias) - 1)) {
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
      $sumaPromediosTotal = 0;
    }
  } else {
    if ($key == (sizeof($arrayDiferencias) - 1)) {
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
      $sumaPromediosTotal = 0;
      $sumaPromediosTotal += $promedio;
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
    } else {
      array_push($arrayPromediosPorDia, $sumaPromediosTotal);
      $sumaPromediosTotal = 0;
      $sumaPromediosTotal += $promedio;
    }
  }

  $fAnterior = $fActual;
  $contadorProm += 1;
}

foreach ($arrayPromediosPorDia as $key => $value) {
  if ($value == 0) {
    unset($arrayPromediosPorDia[$key]);
  } else {
    $arrayPromediosPorDia[$key] = $value / 60;
  }
}

$arrayPromediosPorDia = array_values($arrayPromediosPorDia);

$beforeDay = 0;
foreach ($arrayFechas as $key => $value) {
  if ($beforeDay == $value) {
    unset($arrayFechas[$key]);
  }
  $beforeDay = $value;
}
$arrayFecha = array_values($arrayFechas);
$arrayTiempo = $arrayPromediosPorDia;
?>

<!DOCTYPE html>

<head>
  <title>Gráfica con datos</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.6/nv.d3.css" rel="stylesheet" type="text/css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js" charset="utf-8"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.6/nv.d3.js"></script>
</head>

<body class='with-3d-shadow with-transitions'>
  <div style="position:absolute; top: 0; left: 0;">
    <script>
      var expandLegend = function() {
        var exp = chart.legend.expanded();
        chart.legend.expanded(!exp);
        chart.update();
      }
    </script>
  </div>
  <strong>Tiempo de acceso del estudiante por día</strong><br>
  <span>Tiempo promedio del alumno por día basado en todas las fechas: <strong id="promTime"></strong> </span>
  <div id="chart1" style="height: 95vh"></div>

  <script>
    var jsObjectFecha = JSON.parse('<?= addslashes(json_encode($arrayFecha)) ?>');
    var jsObjectTiempo = JSON.parse('<?= addslashes(json_encode($arrayTiempo)) ?>');

    var arrayJson = new Array();
    for (let posicion = 0; posicion < jsObjectFecha.length; posicion++) {
      const elementoFecha = jsObjectFecha[posicion];
      const elementoTiempo = jsObjectTiempo[posicion];
      const anObject = new Object();
      anObject.timecreated = elementoFecha.date.substring(0, 10);
      anObject.average = elementoTiempo
      arrayJson.push(anObject);
    }

    arrayJson.forEach(function(d) {
      d.timecreated = d.timecreated
    });

    var chart;
    var data;
    var legendPosition = "top";

    nv.addGraph(function() {
      chart = nv.models.lineChart()
        .options({
          duration: 300,
          useInteractiveGuideline: true
        });

      chart.xAxis
        .axisLabel("Fechas")
        .tickFormat(function(d) {
          return d3.time.format('%d-%b-%Y')(new Date(d));
        })
        .staggerLabels(true);

      chart.yAxis
        .axisLabel('Minutos')
        .tickFormat(function(d) {
          if (d == null) {
            return 'N/A';
          }
          return d3.format(',.2f')(d);
        });

      data = sinAndCos(arrayJson);

      d3.select('#chart1').append('svg')
        .datum(data)
        .call(chart);

      nv.utils.windowResize(chart.update);

      return chart;
    });

    var prom = 0.0;

    function sinAndCos(arrayJson) {
      var minutes = [];
      var proms = [];
      var proms_class = [];
      var minutes_group = 0.0;
      var proms_minutes;

      console.log(arrayJson);

      for (var i = 0; i < arrayJson.length; i++) {
        minutes.push({
          x: new Date(arrayJson[i].timecreated),
          y: arrayJson[i].average
        })
        prom += arrayJson[i].average;
      }
      prom = prom / arrayJson.length;
      prom = Math.round(prom);

      let result2 = localStorage.getItem("totalPromResult2");
      if (result2 == null) {
        minutes_group = 0.0;
      } else {
        proms_minutes = result2.split(':');
        minutes_group += parseFloat(proms_minutes[2]);
        minutes_group += parseFloat(proms_minutes[1]) * 60;
        minutes_group += parseFloat(proms_minutes[0]) * 60 * 60;

        minutes_group = minutes_group / 60;
      }

      for (var i = 0; i < arrayJson.length; i++) {
        proms.push({
          x: new Date(arrayJson[i].timecreated),
          y: prom
        })
        if (result2 != null) {
          proms_class.push({
            x: new Date(arrayJson[i].timecreated),
            y: minutes_group
          })
        }
      }

      document.getElementById('promTime').innerHTML = prom + ' minutos.';

      if (result2 == null) {
        return [{
            values: minutes,
            key: "Total de minutos por día:",
            color: "#00A5E3"
          },
          {
            values: proms,
            key: "Tiempo promedio del alumno:",
            color: "#FF5768"
          }
        ];
      } else {
        return [{
            values: minutes,
            key: "Total de minutos por día:",
            color: "#00A5E3"
          },
          {
            values: proms,
            key: "Tiempo promedio del alumno:",
            color: "#FF5768"
          },
          {
            values: proms_class,
            key: "Tiempo promedio del grupo:",
            color: "#8DD7BF"
          }
        ];
      }
    }
  </script>
</body>
