const allStudentNames = Object.keys(info);

function sortStudentsByTotalTimeSpent(studentNames) {
  return studentNames.sort((a, b) => {
    return calculateTotalTimeSpent(b) - calculateTotalTimeSpent(a);
  });
}

function calculateTotalTimeSpent(studentName) {
  const modules = info[studentName][0];
  let totalSpent = 0;
  Object.keys(modules).forEach((module) => {
    const timeSpentInModule = modules[module];
    totalSpent += timeSpentInModule;
  });
  return totalSpent;
}

function getStudentsTimeSpentFor(studentNames, module) {
  return studentNames.map((studentName) => info[studentName][0][module] || 0);
}

function createDatasetFor(studentNames) {
  return moodleModuleNodes.map(({ data }) => {
    return {
      label: data.label,
      backgroundColor: data.color,
      data: getStudentsTimeSpentFor(studentNames, data.id),
    };
  });
}

const labels = [...sortStudentsByTotalTimeSpent(allStudentNames)];
const data = {
  labels: labels,
  datasets: createDatasetFor(sortStudentsByTotalTimeSpent(allStudentNames)),
};

const config = {
  type: 'horizontalBar',
  data: data,
  options: {
    plugins: {
      title: {
        display: true,
        text: 'Chart.js Bar Chart - Stacked',
      },
    },
    responsive: true,
    pointLabelFontFamily: 'Quadon Extra Bold',
    scaleFontFamily: 'Quadon Extra Bold',
    tooltips: {
      enabled: true,
    },
    hover: {
      animationDuration: 1,
    },
    scales: {
      xAxes: [
        {
          ticks: {
            beginAtZero: true,
            fontFamily: "'Open Sans Bold', sans-serif",
            fontSize: 11,
          },
          scaleLabel: {
            display: false,
          },
          gridLines: {},
          stacked: true,
        },
      ],
      yAxes: [
        {
          gridLines: {
            display: false,
            color: '#fff',
            zeroLineColor: '#fff',
            zeroLineWidth: 0,
          },
          ticks: {
            fontFamily: "'Open Sans Bold', sans-serif",
            fontSize: 11,
          },
          stacked: true,
        },
      ],
    },
    legend: {
      display: true,
    },
  },
};

const ctx = document.getElementById('myNetworkChart').getContext('2d');
const myChart = new Chart(ctx, config);

function updateChart(selectedStudentNames) {
  clearArray(myChart.data.labels);
  myChart.data.labels.push(...selectedStudentNames);

  clearArray(myChart.data.datasets);
  myChart.data.datasets.push(...createDatasetFor(selectedStudentNames));

  myChart.update();
}

function onStudentSelectionForChart() {
  const selectedStudentNames = getSelectedStudentNames();
  updateChart(sortStudentsByTotalTimeSpent(selectedStudentNames));
}
