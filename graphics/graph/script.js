// const info = JSON.parse(sessionStorage.getItem("classInformationByDay"));

function createMoodleNodes() {
  return [
    {
      data: {
        id: 'mod_resource',
        label: 'mod_resource',
        level: 100,
        color: 'red',
      },
    },
    {
      data: {
        id: 'mod_forum',
        label: 'mod_forum',
        level: 100,
        color: 'red',
      },
    },
    {
      data: {
        id: 'mod_page',
        label: 'mod_page',
        level: 100,
        color: 'red',
      },
    },
    {
      data: {
        id: 'mod_folder',
        label: 'mod_folder',
        level: 100,
        color: 'red',
      },
    },
    {
      data: {
        id: 'mod_url',
        label: 'mod_url',
        level: 100,
        color: 'red',
      },
    },
    {
      data: {
        id: 'mod_assign',
        label: 'mod_assign',
        level: 100,
        color: 'red',
      },
    },
    {
      data: {
        id: 'mod_wiki',
        label: 'mod_wiki',
        level: 100,
        color: 'red',
      },
    },
  ];
}

const MIN_DIAMETER = 50;
const MAX_DIAMETER = 200;

const allStudentNodes = createStudentNodes();
const sortedStudents = sortStudentsByAvgTimeSpent();
enrichStudentNodesWithDiameter(allStudentNodes);

function createStudentNodes() {
  return Object.keys(info).map((student) => {
    return {
      data: {
        id: student,
        color: 'green',
        label: student,
        avgTimeSpent: info[student][2],
      },
    };
  });
}

function enrichStudentNodesWithDiameter(studentNodes) {
  studentNodes.forEach((node) => {
    node.data = {
      ...node.data,
      diameter: calculateDiameter(node.data.avgTimeSpent, studentNodes),
    };
  });
}

function calculateDiameter(x, studentNodes) {
  const { min, max } = extractMinAndMaxAvgTimeSpentFrom(studentNodes);
  if (min === max) {
    return MIN_DIAMETER;
  }
  return (
    ((x - min) / (max - min)) * (MAX_DIAMETER - MIN_DIAMETER) + MIN_DIAMETER
  );
}

function extractMinAndMaxAvgTimeSpentFrom(studentNodes) {
  return {
    min: studentNodes[studentNodes.length - 1].data.avgTimeSpent,
    max: studentNodes[0].data.avgTimeSpent,
  };
}

function sortStudentsByAvgTimeSpent() {
  return allStudentNodes.sort((a, b) => {
    return b.data.avgTimeSpent - a.data.avgTimeSpent;
  });
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
          label: info[data.id][0][module], // Average as label
        },
      };
    });

    edges.push(...studentEdges);
  });

  return edges;
}

const nodes = [...createMoodleNodes(), ...allStudentNodes];
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
        'curve-style': 'bezier',
        'target-arrow-shape': 'triangle',
      },
    },
  ],
  layout: graphLayout(),
});

$(function () {
  $('#ms').multipleSelect({
    onClose: function () {
      const selectedStudentNames = getSelectedStudentNames();
      const selectedStudentNodes = selectStudentNodes(selectedStudentNames);
      enrichStudentNodesWithDiameter(selectedStudentNodes);
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

function updateGraph(studentNodes) {
  const edges = createEdges(studentNodes);
  cy.elements().remove();
  cy.add([...createMoodleNodes(), ...studentNodes, ...edges]);
  cy.makeLayout(graphLayout()).run();
  addGraphTooltips();
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
    studentDropdown.append($('<option />').val(Math.random).text(id));
  });
}

function addGraphTooltips() {
  cy.elements().forEach(function (ele) {
    makePopper(ele);
  });

  cy.elements().unbind('mouseover');
  cy.elements().bind('mouseover', (event) => event.target.tippy.show());

  cy.elements().unbind('mouseout');
  cy.elements().bind('mouseout', (event) => event.target.tippy.hide());
}

function makePopper(ele) {
  let ref = ele.popperRef(); // used only for positioning

  ele.tippy = tippy(ref, {
    // tippy options:
    content: () => {
      const content = document.createElement('div');
      content.innerHTML = ele.data().label;
      return content;
    },
    trigger: 'manual', // probably want manual mode
  });
}

cy.ready(addGraphTooltips);
