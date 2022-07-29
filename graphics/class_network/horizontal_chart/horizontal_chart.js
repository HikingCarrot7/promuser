const studentsLabelColor = new Map();

function sortStudentsByTotalTimeSpent(students) {
  return students.sort((a, b) => {
    return calculateTotalTimeSpent(b) - calculateTotalTimeSpent(a);
  });
}

function calculateTotalTimeSpent(student) {
  let totalSpent = 0;
  student.modules.forEach((module) => {
    const timeSpentInModule = student.avgTimeForModule(module);
    totalSpent += timeSpentInModule;
  });
  return totalSpent;
}

function getStudentsTimeSpentFor(students, module) {
  return students.map((student) => student.avgTimeForModule(module));
}

function createDatasetFor(students) {
  return moodleModules.map(({ id, label, color }) => {
    return {
      label: label,
      backgroundColor: color,
      data: getStudentsTimeSpentFor(students, id),
    };
  });
}

const studentsSortedByTimeSpent = sortStudentsByTotalTimeSpent(allStudents);

const labels = [...extractNames(studentsSortedByTimeSpent)];
const data = {
  labels: labels,
  datasets: createDatasetFor(studentsSortedByTimeSpent),
};

const config = {
  type: 'bar',
  data: data,
  options: {
    plugins: {
      title: {
        display: true,
        text: 'Promedio de de cada alumno por recurso.',
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            return `${context.dataset.label} (${secondsToHms(context.raw)})`;
          },
        },
      },
    },
    tooltips: {
      enabled: true,
    },
    hover: {
      animationDuration: 1,
    },
    legend: {
      display: true,
    },
    animation: {
      duration: 0,
    },
    responsive: true,
    pointLabelFontFamily: 'Quadon Extra Bold',
    scaleFontFamily: 'Quadon Extra Bold',
    indexAxis: 'y',
    scales: {
      x: {
        ticks: {
          beginAtZero: true,
          fontFamily: "'Open Sans Bold', sans-serif",
          fontSize: 11,
          callback: function (label) {
            return secondsToHms(label);
          },
        },
        scaleLabel: {
          display: false,
        },
        gridLines: {},
        stacked: true,
      },
      y: {
        gridLines: {
          display: false,
          color: '#fff',
          zeroLineColor: '#fff',
          zeroLineWidth: 0,
        },
        ticks: {
          color: ({ tick }) => {
            return studentsLabelColor.get(tick.label) || STUDENT_LABEL_COLOR;
          },
          fontFamily: "'Open Sans Bold', sans-serif",
          fontSize: 11,
        },
        stacked: true,
      },
    },
  },
};

const ctx = document.getElementById('myNetworkChart').getContext('2d');
const myChart = new Chart(ctx, config);

function updateChart(selectedStudents) {
  clearArray(myChart.data.labels);
  myChart.data.labels.push(...selectedStudents.map(({ name }) => name));

  clearArray(myChart.data.datasets);
  myChart.data.datasets.push(...createDatasetFor(selectedStudents));

  myChart.update();
}

// Calculate access avg
$('#checkAccessFilter').click(function () {
  if (!this.checked) {
    applyLabelColorFor(allStudents, STUDENT_LABEL_COLOR);
    updateChart(getSelectedStudents());
  }
});

$('#applyAccessFilter').click(function () {
  applyAccessFilterForChart();
  updateChart(getSelectedStudents());
});

function applyAccessFilterForChart() {
  const cutPoint = $('#cutPointInput').val();
  const selectedStudents = getSelectedStudents();
  const { studentsAbove, studentsBellow } = classifyStudentsByAboveAndBellowFor(
    cutPoint,
    selectedStudents
  );
  applyLabelColorFor(studentsAbove, STUDENT_ABOVE_POINTCUT_COLOR);
  applyLabelColorFor(studentsBellow, STUDENT_BELLOW_POINTCUT_COLOR);
}

function applyLabelColorFor(students, color) {
  students.forEach(({ name }) => {
    studentsLabelColor.set(name, color);
  });
}

function onStudentSelectionForChart() {
  if ($('#checkAccessFilter').is(':checked')) {
    applyAccessFilterForChart();
  }
  const selectedStudents = getSelectedStudents();
  updateChart(sortStudentsByTotalTimeSpent(selectedStudents));
}
