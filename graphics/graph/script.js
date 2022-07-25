// const info = JSON.parse(sessionStorage.getItem("classInformationByDay"));

const MIN_DIAMETER = 50;
const MAX_DIAMETER = 300;

const allStudentNodes = createStudentNodes();
const sortedStudents = sortStudentsByAvgTimeSpent();
const moodleModuleNodes = createMoodleNodes();

enrichStudentNodesWithDiameter(allStudentNodes);
enrichMoodleNodesWithDiameter(allStudentNodes);

function createStudentNodes() {
  return Object.keys(info).map((student) => {
    return {
      data: {
        id: student,
        color: 'green',
        label: student,
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
  container: document.getElementById('mynetwork'),
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

$(function () {
  $('#ms').multipleSelect({
    onClose: function () {
      const selectedStudentNames = getSelectedStudentNames();
      const selectedStudentNodes = selectStudentNodes(selectedStudentNames);
      updateGraph(selectedStudentNodes);
    },
    width: '100%',
    filter: true,
  });

  refreshStudentDropdown();
});

function getSelectedStudentNames() {
  return $('#ms').multipleSelect('getSelects', 'text');
}

function selectStudentNodes(studentNames) {
  if (studentNames.length == 0) {
    return allStudentNodes; // All nodes are selected by default.
  }
  return allStudentNodes.filter(({ data }) => studentNames.includes(data.id));
}

function refreshStudentDropdown() {
  populateStudentDropdown();
  $('#ms').multipleSelect('refreshOptions', {});
}

function populateStudentDropdown() {
  const studentDropdown = $('#ms');
  $.each(allStudentNodes, function () {
    const { id } = this.data;
    studentDropdown.append($('<option />').val(Math.random()).text(id));
  });
}

cy.ready(addGraphTooltips);
