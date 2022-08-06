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

function getSelectedStudents() {
  return selectStudents(getSelectedStudentNames());
}

function getSelectedStudentNames() {
  const selectedStudentNames = $('#ms').multipleSelect('getSelects', 'text');
  if (selectedStudentNames.length === 0) {
    return extractNames(allStudents); // All names are selected by default if none selected.
  }
  return selectedStudentNames;
}

function selectStudents(studentNames) {
  return allStudents.filter(({ name }) => studentNames.includes(name));
}

function refreshStudentDropdown() {
  populateStudentDropdown();
  $('#ms').multipleSelect('refreshOptions', {});
}

function populateStudentDropdown() {
  const studentDropdown = $('#ms');
  $.each(allStudents, function () {
    const { name } = this;
    studentDropdown.append($('<option />').val(Math.random()).text(name));
  });
}
