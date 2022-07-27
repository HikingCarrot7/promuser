const allStudentNodes = createStudentNodes();
const sortedStudents = sortStudentsByAvgTimeSpent();

enrichStudentNodesWithDiameter(allStudentNodes);
enrichMoodleNodesWithDiameter(allStudentNodes);

function createStudentNodes() {
  return Object.keys(info).map((student) => {
    return {
      data: {
        id: student,
        color: STUDENT_NODE_COLOR,
        label: student,
        accesses: info[student][1],
        avgTimeSpent: info[student][2],
        avgTimeSpentLabel: secondsToHms(info[student][2]),
      },
    };
  });
}

function enrichStudentNodesWithDiameter(studentNodes) {
  const { min, max } = extractMinAndMaxAvgTimeSpentFrom(studentNodes);
  studentNodes.forEach(({ data }) => {
    data.diameter = calculateDiameterForNode(data.avgTimeSpent, min, max);
  });
}

function enrichMoodleNodesWithDiameter(studentNodes) {
  const { min, max } = extractMinAndMaxAvgTimeSpentForModules(studentNodes);
  moodleModuleNodes.forEach(({ data }) => {
    data.diameter = calculateDiameterForNode(data.avgTimeSpent, min, max);
  });
}

function extractMinAndMaxAvgTimeSpentFrom(studentNodes) {
  function mapToAvgTimeSpent() {
    return studentNodes.map((studentNode) => studentNode.data.avgTimeSpent);
  }

  return {
    min: Math.min(...mapToAvgTimeSpent()),
    max: Math.max(...mapToAvgTimeSpent()),
  };
}

function extractMinAndMaxAvgTimeSpentForModules(studentNodes) {
  enrichMoodleModulesWithAvgTimeSpent(studentNodes);

  function mapToAvgTimeSpent() {
    return moodleModuleNodes.map(({ data }) => data.avgTimeSpent);
  }

  return {
    min: Math.min(...mapToAvgTimeSpent()),
    max: Math.max(...mapToAvgTimeSpent()),
  };
}

function enrichMoodleModulesWithAvgTimeSpent(studentNodes) {
  moodleModuleNodes.forEach(({ data }) => {
    data.avgTimeSpent = calculateAvgTimeSpentForModule(studentNodes, data.id);
    data.avgTimeSpentLabel = secondsToHms(data.avgTimeSpent);
  });
}

function sortStudentsByAvgTimeSpent() {
  return allStudentNodes.sort((a, b) => {
    return b.data.avgTimeSpent - a.data.avgTimeSpent;
  });
}

function calculateAvgTimeSpentForModule(studentNodes, module) {
  let result = 0;
  studentNodes.forEach(({ data }) => {
    const studentAvgTimeSpentModule = info[data.id][0][module];
    if (studentAvgTimeSpentModule) {
      result += studentAvgTimeSpentModule;
    }
  });
  return result / studentNodes.length;
}

function createEdges(studentNodes) {
  const edges = [];
  studentNodes.forEach(({ data }) => {
    const studentEdges = Object.keys(info[data.id][0]).map((module) => {
      return {
        data: {
          id: Math.random(),
          source: data.id,
          target: module,
          label: secondsToHms(info[data.id][0][module]), // Averate time spent in that module
        },
      };
    });

    edges.push(...studentEdges);
  });

  return edges;
}

const nodes = [...moodleModuleNodes, ...allStudentNodes];
const edges = [...createEdges(allStudentNodes)];
const elements = [...nodes, ...edges];

function graphLayout() {
  return {
    name: 'concentric',
    concentric: function (node) {
      return node.data('level') || 10;
    },
    minNodeSpacing: 50,
  };
}

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
  layout: graphLayout(),
});

function updateGraph(studentNodes) {
  enrichStudentNodesWithDiameter(studentNodes);
  enrichMoodleNodesWithDiameter(studentNodes);
  const edges = createEdges(studentNodes);
  cy.elements().remove();
  cy.add([...moodleModuleNodes, ...studentNodes, ...edges]);
  cy.makeLayout(graphLayout()).run();
  addGraphTooltips();
}

// Calculate access avg
$('#checkAccessFilter').click(function () {
  if (this.checked) {
    const accessAvg = calculateAccessAvg(getSelectedStudentNodes());
    $('#cutPointInput').val(accessAvg);
  } else {
    applyBgColorFor(allStudentNodes, STUDENT_NODE_COLOR);
  }
});

$('#applyAccessFilter').click(applyAccessFilter);

function applyAccessFilter() {
  const cutPoint = $('#cutPointInput').val();
  const selectedStudentNodes = getSelectedStudentNodes();
  const { studentsAbove, studentsBellow } = classifyStudentsByAboveAndBellowFor(
    cutPoint,
    selectedStudentNodes
  );
  applyBgColorFor(studentsAbove, STUDENT_ABOVE_POINTCUT_COLOR);
  applyBgColorFor(studentsBellow, STUDENT_BELLOW_POINTCUT_COLOR);
}

function classifyStudentsByAboveAndBellowFor(cutPoint, studentNodes) {
  return {
    studentsAbove: studentNodes.filter(({ data }) => data.accesses >= cutPoint),
    studentsBellow: studentNodes.filter(({ data }) => data.accesses < cutPoint),
  };
}

function calculateAccessAvg(studentNodes) {
  let totalAccesses = 0;
  studentNodes.forEach(({ data }) => {
    totalAccesses += data.accesses;
  });
  return parseInt(totalAccesses / studentNodes.length);
}

function applyBgColorFor(nodes, color) {
  nodes.forEach(({ data }) => {
    cy.nodes(`[id = "${data.id}"]`).style('background-color', color);
  });
}

function onStudentSelectionForGraph() {
  const selectedStudentNodes = getSelectedStudentNodes();
  updateGraph(selectedStudentNodes);
  if ($('#checkAccessFilter').is(':checked')) {
    applyAccessFilter();
  }
}
