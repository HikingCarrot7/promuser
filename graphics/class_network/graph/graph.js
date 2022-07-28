const nodes = [
  ...createMoodleNodes(allStudents),
  ...createStudentNodes(allStudents),
];
const edges = [...createEdges(allStudents)];
const elements = [...nodes, ...edges];

const cy = cytoscape({
  container: $('#myNetworkGraph'),
  elements,
  style: [
    {
      selector: 'node',
      style: {
        shape: 'circle',
        'background-color': 'data(color)',
        label: 'data(label)',
        width: 'data(diameter)',
        height: 'data(diameter)',
      },
    },
    {
      selector: 'edge',
      style: {
        'target-arrow-shape': 'triangle',
      },
    },
  ],
  wheelSensitivity: 0.3,
  layout: {
    name: 'concentric',
    concentric: function (node) {
      return node.data('level') || 10;
    },
    minNodeSpacing: 50,
  },
});

function createStudentNodes(students) {
  const { min, max } = extractMinAndMaxAvgTimeSpentFrom(students);
  return students.map(({ name, avgTimeSpent }) => {
    return {
      data: {
        id: name,
        color: STUDENT_NODE_COLOR,
        label: name,
        avgTimeSpentLabel: secondsToHms(avgTimeSpent),
        diameter: calculateDiameterFor(avgTimeSpent, min, max),
      },
    };
  });
}

function createMoodleNodes(students) {
  const moodleNodes = moodleModules.map(({ id, ...rest }) => {
    const avgTimeSpent = calculateAvgTimeSpentForModule(students, id);
    return {
      data: {
        id,
        level: 100,
        avgTimeSpent,
        avgTimeSpentLabel: secondsToHms(avgTimeSpent),
        ...rest,
      },
    };
  });
  enrichMoodleNodesWithDiameter(moodleNodes);
  return moodleNodes;
}

function enrichMoodleNodesWithDiameter(moodleNodes) {
  const { min, max } = extractMinAndMaxAvgTimeSpentForModules(moodleNodes);
  moodleNodes.forEach(({ data }) => {
    data.diameter = calculateDiameterFor(data.avgTimeSpent, min, max);
  });
}

function createEdges(students) {
  const edges = [];
  students.forEach((student) => {
    const studentEdges = student.modules.map((module) => {
      return {
        data: {
          id: Math.random(),
          source: student.name,
          target: module,
          label: secondsToHms(student.avgTimeForModule(module)), // Averate time spent in that module.
        },
      };
    });

    edges.push(...studentEdges);
  });
  return edges;
}

function extractMinAndMaxAvgTimeSpentFrom(students) {
  function mapToAvgTimeSpent() {
    return students.map(({ avgTimeSpent }) => avgTimeSpent);
  }

  return {
    min: Math.min(...mapToAvgTimeSpent()),
    max: Math.max(...mapToAvgTimeSpent()),
  };
}

function extractMinAndMaxAvgTimeSpentForModules(moodleNodes) {
  function mapToAvgTimeSpent() {
    return moodleNodes.map(({ data }) => data.avgTimeSpent);
  }

  return {
    min: Math.min(...mapToAvgTimeSpent()),
    max: Math.max(...mapToAvgTimeSpent()),
  };
}

function calculateAvgTimeSpentForModule(students, module) {
  let result = 0;
  students.forEach((student) => {
    result += student.avgTimeForModule(module);
  });
  return result / students.length;
}

function updateGraph(students) {
  const studentNodes = createStudentNodes(students);
  const moodleNodes = createMoodleNodes(students);
  const edges = createEdges(students);
  cy.elements().remove();
  cy.add([...moodleNodes, ...studentNodes, ...edges]);
  cy.makeLayout(graphLayout()).run();
  addGraphTooltips();
}

// Calculate access avg
$('#checkAccessFilter').click(function () {
  if (this.checked) {
    const accessAvg = calculateAccessAvg(getSelectedStudents());
    $('#cutPointInput').val(accessAvg);
  } else {
    applyBgColorFor(allStudents, STUDENT_NODE_COLOR);
  }
});

$('#applyAccessFilter').click(applyAccessFilter);

function applyAccessFilter() {
  const cutPoint = $('#cutPointInput').val();
  const selectedStudents = getSelectedStudents();
  const { studentsAbove, studentsBellow } = classifyStudentsByAboveAndBellowFor(
    cutPoint,
    selectedStudents
  );
  applyBgColorFor(studentsAbove, STUDENT_ABOVE_POINTCUT_COLOR);
  applyBgColorFor(studentsBellow, STUDENT_BELLOW_POINTCUT_COLOR);
}

function applyBgColorFor(students, color) {
  students.forEach(({ name }) => {
    cy.nodes(`[id = "${name}"]`).style('background-color', color);
  });
}

function onStudentSelectionForGraph() {
  const selectedStudents = getSelectedStudents();
  updateGraph(selectedStudents);
  if ($('#checkAccessFilter').is(':checked')) {
    applyAccessFilter();
  }
}
