$(function () {
  $('#ms').multipleSelect({
    onClose: function () {
      const selectedStudentNodes = getSelectedStudentNodes();
      updateGraph(selectedStudentNodes);
      if ($('#checkAccessFilter').is(':checked')) {
        applyAccessFilter();
      }
    },
    width: '100%',
    filter: true,
  });

  refreshStudentDropdown();
});

function getSelectedStudentNodes() {
  return selectStudentNodes(getSelectedStudentNames());
}

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
