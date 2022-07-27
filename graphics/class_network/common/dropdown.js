$(function () {
  $('#ms').multipleSelect({
    onClose: function () {
      onStudentSelectionForGraph();
      onStudentSelectionForChart();
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
  const selectedStudentNames = $('#ms').multipleSelect('getSelects', 'text');
  if (selectedStudentNames.length === 0) {
    return allStudentNames; // All names are selected by default if none selected.
  }
  return selectedStudentNames;
}

function selectStudentNodes(studentNames) {
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
