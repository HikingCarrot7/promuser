<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;
global $COURSE;
global $USER;

$idCourse = $_GET['courseVar'];
$user = $DB->get_record_sql("SELECT id, firstname, lastname FROM mdl_user where (id = " . $_GET['var'] . ")");
$total_sum = 0;
$counter_record = 0;
$counter_id = 2;
$counter_group = 2;

$mods = array();

?>

<!DOCTYPE html>

<head>
  <title>Gráfica de Redes</title>
  <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.1/css/all.css">
  <style type="text/css">
    #mynetwork {
      width: 90vw;
      margin: auto;
      margin-top: auto;
      height: 83vh;
      border: 3px solid black;
      border-radius: 5px;
    }
  </style>
</head>

<body>
  <div style="text-align: center; margin: auto; margin-bottom: 5px;">
    <h3 style="margin: auto; width: 100%; text-align: center; padding-top: .5%; padding-bottom: .5%; font: 100% sans-serif; font-weight: bold;">Gráfico de relaciones de recursos por estudiante</h3>
    <details data-popover="up">
      <summary style="cursor: pointer;">Más información</summary>
      <div style="background: white; border: 1px solid black;">
        <p style="font: 80% sans-serif;">Se muestra la conexión entre el estudiante y los recursos, así como los tiempos promedio que el estudiante le dedica a dicho recurso.</p>
      </div>
    </details>
  </div>
  <button style="cursor: pointer; padding-top: 2px; padding-bottom: 2px; background: #365ABD; color: white; border-radius: 2px; border: 0px solid black; margin-left: 4.4%; padding-left: 5%; padding-right: 5%; margin-bottom: 5px;" onclick="stabilizeNetwork()">Regenerar</button><br>
  <input type="button" style="margin-left: 4.5%; width: 120px;" value="Acercar" id="zoomin" />
  <input type="button" style="margin-left: 10px; width: 120px; margin-bottom: 5px;" value="Alejar" id="zoomout" />
  <div id="mynetwork"></div>
  <script type="text/javascript">
    var dataComplete;
    var network;
    var container;
    var data;
    var options;

    initHandlers();

    var zoomstep = 0.3;
    var currentzoom;

    function zoomin() {
      currentzoom += zoomstep;
      network.setScale(currentzoom);
    }

    function zoomout() {
      currentzoom -= zoomstep;
      network.setScale(currentzoom);
    }

    function initHandlers() {
      document.getElementById("zoomin").addEventListener("click", zoomin);
      document.getElementById("zoomout").addEventListener("click", zoomout);
    }

    function stabilizeNetwork() {
      network = new vis.Network(container, data, options);
    }

    function secondsToHms(d) {
      d = Number(d);

      var h = Math.floor(d / 3600);
      var m = Math.floor(d % 3600 / 60);
      var s = Math.floor(d % 3600 % 60);

      return ('0' + h).slice(-2) + ":" + ('0' + m).slice(-2) + ":" + ('0' + s).slice(-2);
    }

    function getPromsByUser(data_json) {
      dataComplete = data_json;
      for (var clave in data_json) {
        if (data_json.hasOwnProperty(clave)) {
          let claveString = clave.substring(4);
          let secondsValue = secondsToHms(data_json[clave]);
        }
      }
    }

    function setProm() {
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          getPromsByUser(JSON.parse(this.responseText));
          setGrafo(dataComplete);
        }
      };
      var idUser = <?= $user->id ?>;
      var idCourse = <?= $idCourse ?>;
      var params = "idUser=" + idUser + "&idCourse=" + idCourse;
      var windowLocation = window.location;
      var pathname = windowLocation.pathname.split("/");
      var directory = windowLocation.origin + "/" + pathname[1] + "/blocks/promuser/helpers/getProm.php";

      xhttp.open("POST", directory, true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send(params);
    };
    setProm();

    function setGrafo(dataComplete) {
      let dataSetNodes = [];
      let dataSetEdges = [];
      let studentNode = {
        id: 1,
        label: "<?= $user->firstname . ' ' . $user->lastname ?>",
        group: 1,
        "shape": "icon",
        icon: {
          face: '"Font Awesome 5 Free"',
          code: '\uf007',
          color: "blue"
        },
      };
      dataSetNodes.push(studentNode);

      let counter = 2;

      let counter_group = 2;
      let counter_id = 1;

      for (const property in dataComplete) {
        let newNode = {
          id: counter,
          label: `${property}`.substring(4).charAt(0).toUpperCase() + `${property}`.substring(5),
          title: 'Tiempo promedio: ' + secondsToHms(`${dataComplete[property]}`),
          group: counter,
          shape: 'circle',
          widthConstraint: {
            minimum: Math.pow(dataComplete[property], 1 / 2) * 1.8,
          },
          value: 1,
        }
        dataSetNodes.push(newNode);

        let newEdge = {
          to: 1,
          label: secondsToHms(`${dataComplete[property]}`),
          from: counter_group,
          length: 600,
        }
        dataSetEdges.push(newEdge);

        counter++;
        counter_group++;
        counter_id++;
      };

      var nodes = new vis.DataSet(dataSetNodes);
      var edges = new vis.DataSet(dataSetEdges);

      // create a network
      container = document.getElementById("mynetwork");
      data = {
        nodes: nodes,
        edges: edges,
      };
      options = {
        nodes: {
          size: 30,
          font: {
            size: 15,
          },
          borderWidth: 1,
          shadow: true,
        },
        edges: {
          font: {
            size: 14,
          },
          width: 2,
          shadow: true,
        }
      };
      network = new vis.Network(container, data, options);
      currentzoom = network.getScale();

      vis.Network.prototype.setScale = function(scale) {
        var animationOptions = {
          scale: scale,
          animation: options.animation
        };
        this.view.moveTo(animationOptions);
      };

    }
  </script>
</body>
