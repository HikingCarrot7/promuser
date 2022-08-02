<?php
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;
global $COURSE;
global $USER;
?>

<!DOCTYPE html>

<head>
  <title>Gráfica de Redes</title>
  <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.1/css/all.css">
  <style type="text/css">
    #mynetwork {
      width: 90vw;
      margin: auto;
      margin-top: auto;
      height: 72vh;
      border: 3px solid black;
      border-radius: 5px;
    }

    details[data-popover]>summary:focus {
      outline: none;
    }

    details[data-popover]>summary::-webkit-details-marker {
      display: none;
    }

    details[data-popover]>summary {
      list-style: none;
    }

    details[data-popover]>summary {
      list-style: none;
      text-decoration: underline dotted teal;
    }

    details[data-popover] {
      position: relative;
    }

    details[data-popover]>summary+* {
      position: absolute;
    }

    details[data-popover="up"]>summary+* {
      top: calc(0.5rem + 100%);
      right: 50%;
      transform: translateX(50%);
    }
  </style>
</head>

<body>
  <div style="width: 100%; display: flex; margin-bottom: 10px;">
    <div style="width: 35%">
      <div style="text-align: center;">
        <h3 style="padding-left: 10%; padding-top: 5%; font: 100% sans-serif;">Tiempo de acceso del estudiante por recurso</h3>
        <details data-popover="up">
          <summary style="cursor: pointer;">Más información</summary>
          <div style="background: white; border: 1px solid black;">
            <p style="font: 80% sans-serif;">Se muestra la conexión entre los estudiantes del grupo y los recursos, así como los tiempos promedio de cada recurso y estudiante del grupo.</p>
          </div>
        </details>
      </div>
    </div>
    <div style="width: 75%; margin: auto; text-align: center; border: 2px solid blue; border-radius: 5px;">
      <p style="margin-top: 8px; margin-bottom: 2px; font-weight: bold; font: 100% sans-serif;">Mostrar alumnos con la siguiente condición:</p>
      <select style="margin-top: 4px; margin-bottom: 4px;" class="option-selected">
        <option selected disabled>Seleccione una opción...</option>
        <option value="0">Duración promedio</option>
        <option value="1">Número de accesos</option>
      </select><br>
      <span class="prom-option" style="font: 80% sans-serif;"></span><br>
      <span class="options" style="font: 80% sans-serif; display: none;">Modifique el punto de corte para clasificar alumnos:</span><br>
      <input type="number" class="options min-value" style="width: 30%; margin-bottom: 5px; text-align: center; display: none;" placeholder="Valor de corte">
      <button class="options make-difference" style="display: none;">Aplicar</button><br>
      <div class="options" style="display: flex; margin: auto; justify-content: center; display: none;">
        <div class="display: flex;">
          <i class="far fa-user" style="color:green;"></i>
          <span style="margin-right: 10px;">El estudiante está por <strong>encima</strong> del parámetro</span>
        </div>
        <div class="display: flex">
          <i class="far fa-user" style="color:red;"></i>
          <span>El estudiante está por <strong>debajo</strong> del parámetro</span>
        </div>
      </div>
    </div>
  </div>
  <button style="cursor: pointer; padding-top: 2px; padding-bottom: 2px; background: #365ABD; color: white; border-radius: 2px; border: 0px solid black; margin-left: 4%; padding-left: 5%; padding-right: 5%; margin-bottom: 5px;" onclick="stabilizeNetwork()">Regenerar</button><br>
  <input type="button" style="margin-left: 4%; width: 120px;" value="Acercar" id="zoomin" />
  <input type="button" style="margin-left: 10px; width: 120px; margin-bottom: 5px;" value="Alejar" id="zoomout" />
  <div id="mynetwork"></div>
  <script type="text/javascript">
    var info = JSON.parse(sessionStorage.getItem("classInformationByDay"));
    console.log(info);
    let promTime = 0;
    let quantityProm = 0;
    var network;

    let numberAcceses = 0;

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

    $(document).ready(() => {
      $('.option-selected').change(function() {
        $('.options').show();

        if ($(this).val() == 0) {
          $('.prom-option').text(`Duración promedio del acceso por día del grupo: ${Math.floor(promTime/60)} Minutos`);
          $('.min-value').val(Math.floor(promTime / 60));
        }

        if ($(this).val() == 1) {
          $('.prom-option').text(`El número de accesos promedio del grupo es de: ${Math.floor(numberAcceses)} accesos`);
          $('.min-value').val(Math.floor(numberAcceses));
        }
      });

      $('.make-difference').click(function() {
        if ($('.option-selected').val() == 0) {
          setNodesByPromTime($('.min-value').val());
        }
        if ($('.option-selected').val() == 1) {
          setNodesByNumAccess($('.min-value').val());
        }
      });
    });

    for (let student in info) {
      numberAcceses += info[student][1];
      promTime += info[student][2];
      quantityProm++;
    }
    promTime = promTime / quantityProm;
    numberAcceses = numberAcceses / quantityProm;

    function setNodesByPromTime(min) {
      nodesDataSet = [];
      edgesDataSet = [];
      counterActivities = 2;

      activitiesNumber = [];
      activitiesProms = [];

      for (const student in info) {
        activitiesNumber.push(...Object.keys(info[student][0]));
        activitiesProms.push(...Object.keys(info[student][0]));
        valueStudent = info[student][0];

        for (const activity in valueStudent) {
          if (activitiesNumber[activity] != null) {
            activitiesNumber[activity] += 1;
          } else {
            activitiesNumber[activity] = 1;
          }

          if (activitiesProms[activity] != null) {
            activitiesProms[activity] += valueStudent[activity];
          } else {
            activitiesProms[activity] = valueStudent[activity];
          }
        }
      }

      uniqueActivities = [...new Set(activitiesProms)];

      uniqueActivities.forEach((element) => {
        nodesDataSet.push({
          id: element,
          label: element.substring(4).charAt(0).toUpperCase() + element.substring(4).slice(1),
          group: counterActivities,
          shape: 'circle',
          widthConstraint: {
            minimum: Math.pow(activitiesProms[element] / activitiesNumber[element], 1 / 2) * 1.8,
          },
          value: 1,
          title: 'Tiempo promedio: ' + secondsToHms(`${activitiesProms[element] / activitiesNumber[element]}`),
        });
        counterActivities++;
      });

      for (const student in info) {
        if (info[student][2] >= (min * 60)) {
          nodesDataSet.push({
            id: student,
            label: student.replaceAll(',', ' '),
            group: 1,
            "shape": "icon",
            icon: {
              face: '"Font Awesome 5 Free"',
              code: '\uf007',
              color: "green",
              size: Math.pow(info[student][2], 1 / 2) * 1.8
            },
            title: 'Tiempo promedio: ' + secondsToHms(`${info[student][2]}`) + '\n' + student.replaceAll(',', ' '),
          });
        } else {
          nodesDataSet.push({
            id: student,
            label: student.replaceAll(',', ' '),
            group: 1,
            "shape": "icon",
            icon: {
              face: '"Font Awesome 5 Free"',
              code: '\uf007',
              color: "red",
              size: Math.pow(info[student][2], 1 / 2) * 1.8
            },
            title: 'Tiempo promedio: ' + secondsToHms(`${info[student][2]}`) + '\n' + student.replaceAll(',', ' '),
          });
        }
        for (const activity in info[student][0]) {
          edgesDataSet.push({
            to: activity,
            label: secondsToHms(info[student][0][activity]),
            from: student,
            length: (info[student][0][activity] * .3),
            arrowStrikethrough: false
          });
        };
      }
      var nodes = new vis.DataSet(nodesDataSet);

      var edges = new vis.DataSet(edgesDataSet);

      var container = document.getElementById("mynetwork");
      var data = {
        nodes: nodes,
        edges: edges,
      };
      var options = {
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
          chosen: {
            label: function(values, id, selected, hovering) {
              values.size = 30;
              values.color = 'red';
            },
            edge: function(values, id, selected, hovering) {
              values.color = 'blue';
            }
          },
          smooth: {
            enabled: true,
            roundness: 0.5
          }
        },
        "physics": {
          "barnesHut": {
            "springConstant": 0,
            "avoidOverlap": 1
          }
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

      network.stabilize()
    }

    function setNodesByNumAccess(min) {
      nodesDataSet = [];
      edgesDataSet = [];
      counterActivities = 2;

      activitiesNumber = [];
      activitiesProms = [];

      for (const student in info) {
        activitiesNumber.push(...Object.keys(info[student][0]));
        activitiesProms.push(...Object.keys(info[student][0]));
        valueStudent = info[student][0];

        for (const activity in valueStudent) {
          if (activitiesNumber[activity] != null) {
            activitiesNumber[activity] += 1;
          } else {
            activitiesNumber[activity] = 1;
          }

          if (activitiesProms[activity] != null) {
            activitiesProms[activity] += valueStudent[activity];
          } else {
            activitiesProms[activity] = valueStudent[activity];
          }
        }
      }
      uniqueActivities = [...new Set(activitiesProms)];

      uniqueActivities.forEach((element) => {
        nodesDataSet.push({
          id: element,
          label: element.substring(4).charAt(0).toUpperCase() + element.substring(4).slice(1),
          group: counterActivities,
          shape: 'circle',
          widthConstraint: {
            minimum: Math.pow(activitiesProms[element] / activitiesNumber[element], 1 / 2) * 1.8,
          },
          value: 1,
          title: 'Tiempo promedio: ' + secondsToHms(`${activitiesProms[element] / activitiesNumber[element]}`),
        });
        counterActivities++;
      });

      for (const student in info) {
        if (info[student][1] >= min) {
          nodesDataSet.push({
            id: student,
            label: student.replaceAll(',', ' '),
            group: 1,
            "shape": "icon",
            icon: {
              face: '"Font Awesome 5 Free"',
              code: '\uf007',
              color: "green",
              size: Math.pow(info[student][2], 1 / 2) * 1.8
            },
            title: 'Tiempo promedio: ' + secondsToHms(`${info[student][2]}`) + '\n' + student.replaceAll(',', ' '),
          });
        } else {
          nodesDataSet.push({
            id: student,
            label: student.replaceAll(',', ' '),
            group: 1,
            "shape": "icon",
            icon: {
              face: '"Font Awesome 5 Free"',
              code: '\uf007',
              color: "red",
              size: Math.pow(info[student][2], 1 / 2) * 1.8
            },
            title: 'Tiempo promedio: ' + secondsToHms(`${info[student][2]}`) + '\n' + student.replaceAll(',', ' '),
          });
        }
        for (const activity in info[student][0]) {
          edgesDataSet.push({
            to: activity,
            label: secondsToHms(info[student][0][activity]),
            from: student,
            length: (info[student][0][activity] * .3),
            arrowStrikethrough: false
          });
        };
      }
      var nodes = new vis.DataSet(nodesDataSet);

      var edges = new vis.DataSet(edgesDataSet);

      var container = document.getElementById("mynetwork");
      var data = {
        nodes: nodes,
        edges: edges,
      };
      var options = {
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
          chosen: {
            label: function(values, id, selected, hovering) {
              values.size = 30;
              values.color = 'red';
            },
            edge: function(values, id, selected, hovering) {
              values.color = 'blue';
            }
          },
          smooth: {
            enabled: true,
            roundness: 0.5
          }
        },
        "physics": {
          "barnesHut": {
            "springConstant": 0,
            "avoidOverlap": 1
          }
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
      network.stabilize()
    }

    function secondsToHms(d) {
      d = Number(d);

      var h = Math.floor(d / 3600);
      var m = Math.floor(d % 3600 / 60);
      var s = Math.floor(d % 3600 % 60);

      return ('0' + h).slice(-2) + ":" + ('0' + m).slice(-2) + ":" + ('0' + s).slice(-2);
    }

    nodesDataSet = [];
    edgesDataSet = [];
    counterActivities = 2;

    activitiesNumber = [];
    activitiesProms = [];

    for (const student in info) {
      activitiesNumber.push(...Object.keys(info[student][0]));
      activitiesProms.push(...Object.keys(info[student][0]));
      valueStudent = info[student][0];

      for (const activity in valueStudent) {
        if (activitiesNumber[activity] != null) {
          activitiesNumber[activity] += 1;
        } else {
          activitiesNumber[activity] = 1;
        }

        if (activitiesProms[activity] != null) {
          activitiesProms[activity] += valueStudent[activity];
        } else {
          activitiesProms[activity] = valueStudent[activity];
        }
      }
    }
    uniqueActivities = [...new Set(activitiesProms)];

    uniqueActivities.forEach((element) => {
      nodesDataSet.push({
        id: element,
        label: element.substring(4).charAt(0).toUpperCase() + element.substring(4).slice(1),
        group: counterActivities,
        shape: 'circle',
        widthConstraint: {
          minimum: Math.pow(activitiesProms[element] / activitiesNumber[element], 1 / 2) * 1.8,
        },
        value: 1,
        title: 'Tiempo promedio: ' + secondsToHms(`${activitiesProms[element] / activitiesNumber[element]}`),
      });
      counterActivities++;
    });

    for (const student in info) {
      nodesDataSet.push({
        id: student,
        label: student.replaceAll(',', ' '),
        group: 1,
        "shape": "icon",
        icon: {
          face: '"Font Awesome 5 Free"',
          code: '\uf007',
          color: "blue",
          size: Math.pow(info[student][2], 1 / 2) * 3.8
        },
        title: 'Tiempo promedio: ' + secondsToHms(`${info[student][2]}`) + '\n' + student.replaceAll(',', ' '),
      });
      for (const activity in info[student][0]) {
        edgesDataSet.push({
          to: activity,
          label: secondsToHms(info[student][0][activity]),
          from: student,
          length: (info[student][0][activity] * .3),
          arrowStrikethrough: false
        });
      };
    }
    var nodes = new vis.DataSet(nodesDataSet);

    var edges = new vis.DataSet(edgesDataSet);

    var container = document.getElementById("mynetwork");
    var data = {
      nodes: nodes,
      edges: edges,
    };
    var options = {
      nodes: {
        size: 30,
        font: {
          size: 15,
        },
        borderWidth: 1,
        shadow: true,
        shapeProperties: {
          interpolation: true // 'true' for intensive zooming
        }
      },
      edges: {
        font: {
          size: 14,
        },
        width: 2,
        shadow: true,
        chosen: {
          label: function(values, id, selected, hovering) {
            values.size = 30;
            values.color = 'red';
          },
          edge: function(values, id, selected, hovering) {
            values.color = 'blue';
          }
        },
        smooth: {
          enabled: true,
          roundness: 0.5
        }
      },
      "physics": {
        "barnesHut": {
          "springConstant": 0,
          "avoidOverlap": 1
        }
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
    network.stabilize()
  </script>
</body>
