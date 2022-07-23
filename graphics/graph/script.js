var info = JSON.parse(sessionStorage.getItem('classInformationByDay'));
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
  document.getElementById('zoomin').addEventListener('click', zoomin);
  document.getElementById('zoomout').addEventListener('click', zoomout);
}

function stabilizeNetwork() {
  network = new vis.Network(container, data, options);
}

$(document).ready(() => {
  $('.option-selected').change(function () {
    $('.options').show();

    if ($(this).val() == 0) {
      $('.prom-option').text(
        `Duración promedio del acceso por día del grupo: ${Math.floor(
          promTime / 60
        )} Minutos`
      );
      $('.min-value').val(Math.floor(promTime / 60));
    }

    if ($(this).val() == 1) {
      $('.prom-option').text(
        `El número de accesos promedio del grupo es de: ${Math.floor(
          numberAcceses
        )} accesos`
      );
      $('.min-value').val(Math.floor(numberAcceses));
    }
  });

  $('.make-difference').click(function () {
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
      label:
        element.substring(4).charAt(0).toUpperCase() +
        element.substring(4).slice(1),
      group: counterActivities,
      shape: 'circle',
      widthConstraint: {
        minimum:
          Math.pow(
            activitiesProms[element] / activitiesNumber[element],
            1 / 2
          ) * 1.8,
      },
      value: 1,
      title:
        'Tiempo promedio: ' +
        secondsToHms(`${activitiesProms[element] / activitiesNumber[element]}`),
    });
    counterActivities++;
  });

  for (const student in info) {
    if (info[student][2] >= min * 60) {
      nodesDataSet.push({
        id: student,
        label: student.replaceAll(',', ' '),
        group: 1,
        shape: 'icon',
        icon: {
          face: '"Font Awesome 5 Free"',
          code: '\uf007',
          color: 'green',
          size: Math.pow(info[student][2], 1 / 2) * 1.8,
        },
        title:
          'Tiempo promedio: ' +
          secondsToHms(`${info[student][2]}`) +
          '\n' +
          student.replaceAll(',', ' '),
      });
    } else {
      nodesDataSet.push({
        id: student,
        label: student.replaceAll(',', ' '),
        group: 1,
        shape: 'icon',
        icon: {
          face: '"Font Awesome 5 Free"',
          code: '\uf007',
          color: 'red',
          size: Math.pow(info[student][2], 1 / 2) * 1.8,
        },
        title:
          'Tiempo promedio: ' +
          secondsToHms(`${info[student][2]}`) +
          '\n' +
          student.replaceAll(',', ' '),
      });
    }
    for (const activity in info[student][0]) {
      edgesDataSet.push({
        to: activity,
        label: secondsToHms(info[student][0][activity]),
        from: student,
        length: info[student][0][activity] * 0.3,
        arrowStrikethrough: false,
      });
    }
  }
  var nodes = new vis.DataSet(nodesDataSet);

  var edges = new vis.DataSet(edgesDataSet);

  var container = document.getElementById('mynetwork');
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
        label: function (values, id, selected, hovering) {
          values.size = 30;
          values.color = 'red';
        },
        edge: function (values, id, selected, hovering) {
          values.color = 'blue';
        },
      },
      smooth: {
        enabled: true,
        roundness: 0.5,
      },
    },
    physics: {
      barnesHut: {
        springConstant: 0,
        avoidOverlap: 1,
      },
    },
  };
  network = new vis.Network(container, data, options);
  currentzoom = network.getScale();

  vis.Network.prototype.setScale = function (scale) {
    var animationOptions = {
      scale: scale,
      animation: options.animation,
    };
    this.view.moveTo(animationOptions);
  };

  network.stabilize();
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
      label:
        element.substring(4).charAt(0).toUpperCase() +
        element.substring(4).slice(1),
      group: counterActivities,
      shape: 'circle',
      widthConstraint: {
        minimum:
          Math.pow(
            activitiesProms[element] / activitiesNumber[element],
            1 / 2
          ) * 1.8,
      },
      value: 1,
      title:
        'Tiempo promedio: ' +
        secondsToHms(`${activitiesProms[element] / activitiesNumber[element]}`),
    });
    counterActivities++;
  });

  for (const student in info) {
    if (info[student][1] >= min) {
      nodesDataSet.push({
        id: student,
        label: student.replaceAll(',', ' '),
        group: 1,
        shape: 'icon',
        icon: {
          face: '"Font Awesome 5 Free"',
          code: '\uf007',
          color: 'green',
          size: Math.pow(info[student][2], 1 / 2) * 1.8,
        },
        title:
          'Tiempo promedio: ' +
          secondsToHms(`${info[student][2]}`) +
          '\n' +
          student.replaceAll(',', ' '),
      });
    } else {
      nodesDataSet.push({
        id: student,
        label: student.replaceAll(',', ' '),
        group: 1,
        shape: 'icon',
        icon: {
          face: '"Font Awesome 5 Free"',
          code: '\uf007',
          color: 'red',
          size: Math.pow(info[student][2], 1 / 2) * 1.8,
        },
        title:
          'Tiempo promedio: ' +
          secondsToHms(`${info[student][2]}`) +
          '\n' +
          student.replaceAll(',', ' '),
      });
    }
    for (const activity in info[student][0]) {
      edgesDataSet.push({
        to: activity,
        label: secondsToHms(info[student][0][activity]),
        from: student,
        length: info[student][0][activity] * 0.3,
        arrowStrikethrough: false,
      });
    }
  }
  var nodes = new vis.DataSet(nodesDataSet);

  var edges = new vis.DataSet(edgesDataSet);

  var container = document.getElementById('mynetwork');
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
        label: function (values, id, selected, hovering) {
          values.size = 30;
          values.color = 'red';
        },
        edge: function (values, id, selected, hovering) {
          values.color = 'blue';
        },
      },
      smooth: {
        enabled: true,
        roundness: 0.5,
      },
    },
    physics: {
      barnesHut: {
        springConstant: 0,
        avoidOverlap: 1,
      },
    },
  };
  network = new vis.Network(container, data, options);
  currentzoom = network.getScale();
  vis.Network.prototype.setScale = function (scale) {
    var animationOptions = {
      scale: scale,
      animation: options.animation,
    };
    this.view.moveTo(animationOptions);
  };
  network.stabilize();
}

function secondsToHms(d) {
  d = Number(d);

  var h = Math.floor(d / 3600);
  var m = Math.floor((d % 3600) / 60);
  var s = Math.floor((d % 3600) % 60);

  return (
    ('0' + h).slice(-2) + ':' + ('0' + m).slice(-2) + ':' + ('0' + s).slice(-2)
  );
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
    label:
      element.substring(4).charAt(0).toUpperCase() +
      element.substring(4).slice(1),
    group: counterActivities,
    shape: 'circle',
    widthConstraint: {
      minimum:
        Math.pow(activitiesProms[element] / activitiesNumber[element], 1 / 2) *
        1.8,
    },
    value: 1,
    title:
      'Tiempo promedio: ' +
      secondsToHms(`${activitiesProms[element] / activitiesNumber[element]}`),
  });
  counterActivities++;
});

for (const student in info) {
  nodesDataSet.push({
    id: student,
    label: student.replaceAll(',', ' '),
    group: 1,
    shape: 'icon',
    icon: {
      face: '"Font Awesome 5 Free"',
      code: '\uf007',
      color: 'blue',
      size: Math.pow(info[student][2], 1 / 2) * 3.8,
    },
    title:
      'Tiempo promedio: ' +
      secondsToHms(`${info[student][2]}`) +
      '\n' +
      student.replaceAll(',', ' '),
  });
  for (const activity in info[student][0]) {
    edgesDataSet.push({
      to: activity,
      label: secondsToHms(info[student][0][activity]),
      from: student,
      length: info[student][0][activity] * 0.3,
      arrowStrikethrough: false,
    });
  }
}
var nodes = new vis.DataSet(nodesDataSet);

var edges = new vis.DataSet(edgesDataSet);

var container = document.getElementById('mynetwork');
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
      interpolation: true, // 'true' for intensive zooming
    },
  },
  edges: {
    font: {
      size: 14,
    },
    width: 2,
    shadow: true,
    chosen: {
      label: function (values, id, selected, hovering) {
        values.size = 30;
        values.color = 'red';
      },
      edge: function (values, id, selected, hovering) {
        values.color = 'blue';
      },
    },
    smooth: {
      enabled: true,
      roundness: 0.5,
    },
  },
  physics: {
    barnesHut: {
      springConstant: 0,
      avoidOverlap: 1,
    },
  },
};
network = new vis.Network(container, data, options);
currentzoom = network.getScale();
vis.Network.prototype.setScale = function (scale) {
  var animationOptions = {
    scale: scale,
    animation: options.animation,
  };
  this.view.moveTo(animationOptions);
};

network.stabilize();
