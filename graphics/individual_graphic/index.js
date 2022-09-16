var expandLegend = function () {
  var exp = chart.legend.expanded();
  chart.legend.expanded(!exp);
  chart.update();
};

var jsObjectFecha = JSON.parse('<?= addslashes(json_encode($arrayFecha)) ?>');
var jsObjectTiempo = JSON.parse('<?= addslashes(json_encode($arrayTiempo)) ?>');

var arrayJson = new Array();
for (let posicion = 0; posicion < jsObjectFecha.length; posicion++) {
  const elementoFecha = jsObjectFecha[posicion];
  const elementoTiempo = jsObjectTiempo[posicion];
  const anObject = new Object();
  anObject.timecreated = elementoFecha.date.substring(0, 10);
  anObject.average = elementoTiempo;
  arrayJson.push(anObject);
}

arrayJson.forEach(function (d) {
  d.timecreated = d.timecreated;
});

var chart;
var data;
var legendPosition = 'top';

nv.addGraph(function () {
  chart = nv.models.lineChart().options({
    duration: 300,
    useInteractiveGuideline: true,
  });

  chart.xAxis
    .axisLabel('Fechas')
    .tickFormat(function (d) {
      return d3.time.format('%d-%b-%Y')(new Date(d));
    })
    .staggerLabels(true);

  chart.yAxis.axisLabel('Minutos').tickFormat(function (d) {
    if (d == null) {
      return 'N/A';
    }
    return d3.format(',.2f')(d);
  });

  data = sinAndCos(arrayJson);

  d3.select('#chart1').append('svg').datum(data).call(chart);

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
      y: arrayJson[i].average,
    });
    prom += arrayJson[i].average;
  }
  prom = prom / arrayJson.length;
  prom = Math.round(prom);

  //let result2 = localStorage.getItem('totalPromResult2');
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
      y: prom,
    });
    if (result2 != null) {
      proms_class.push({
        x: new Date(arrayJson[i].timecreated),
        y: minutes_group,
      });
    }
  }

  document.getElementById('promTime').innerHTML = prom + ' minutos.';

  if (result2 == null) {
    return [
      {
        values: minutes,
        key: 'Total de minutos por día:',
        color: '#00A5E3',
      },
      {
        values: proms,
        key: 'Tiempo promedio del alumno:',
        color: '#FF5768',
      },
    ];
  } else {
    return [
      {
        values: minutes,
        key: 'Total de minutos por día:',
        color: '#00A5E3',
      },
      {
        values: proms,
        key: 'Tiempo promedio del alumno:',
        color: '#FF5768',
      },
      {
        values: proms_class,
        key: 'Tiempo promedio del grupo:',
        color: '#8DD7BF',
      },
    ];
  }
}
