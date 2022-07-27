$('#viewAsGraphBtn').prop('disabled', true); // Graph shows as default.

$('#viewAsGraphBtn').click(function (e) {
  e.preventDefault();
  $('#viewAsChartBtn').prop('disabled', false);
  $('#viewAsGraphBtn').prop('disabled', true);
  toggleGraphic();
});

$('#viewAsChartBtn').click(function (e) {
  e.preventDefault();
  $('#viewAsGraphBtn').prop('disabled', false);
  $('#viewAsChartBtn').prop('disabled', true);
  toggleGraphic();
});

function toggleGraphic() {
  $('#myNetworkGraph').toggle();
  $('#myNetworkChart').toggle();
}
