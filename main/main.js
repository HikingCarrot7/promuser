function setCSV() {
  var windowLocationS = window.location;
  var pathnameS = windowLocationS.pathname.split('/');
  var directoryS =
    windowLocationS.origin +
    '/' +
    pathnameS[1] +
    '/blocks/promuser/csv/downloadCSV.php?idCourse=' +
    courseId;
  var directorySInterval =
    windowLocationS.origin +
    '/' +
    pathnameS[1] +
    '/blocks/promuser/csv/downloadCSVInterval.php?idCourse=' +
    courseId;
  document.getElementById('buttonCSV').innerHTML =
    '<a href=' +
    directoryS +
    ' class="btn btn-sm btn-success" style="margin-top:3px;">Exportar .csv de Recursos</a>';
  document.getElementById('buttonCSVInterval').innerHTML =
    '<a href=' +
    directorySInterval +
    ' class="btn btn-sm btn-success" style="margin-top:3px;">Exportar .csv de Accesos</a>';
}

setCSV();

function showInformation() {
  /*let results = getTotalResults();
  if (results[0] == null) {*/
    document.getElementById('loading').style.display = 'block';
  /*} else {
    document.getElementById('generalInformation1').innerHTML = results[0];
    document.getElementById('generalInformation2').innerHTML = results[1];
  }*/
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById('loading').style.display = 'none';
      jsonResponse = JSON.parse(this.responseText);
      time1 = jsonResponse[0];
      let hours = Math.floor(time1 / 3600);
      let minutes = Math.floor((time1 % 3600) / 60);
      let seconds = time1 % 60;

      //Anteponiendo un 0 a los minutos si son menos de 10
      minutes = minutes < 10 ? '0' + minutes : minutes;

      //Anteponiendo un 0 a los segundos si son menos de 10
      seconds = seconds < 10 ? '0' + seconds : seconds;

      let result1 = hours + ':' + minutes + ':' + seconds;
      //localStorage.setItem('totalPromResult1', result1);
      document.getElementById('generalInformation1').innerHTML = result1;

      time2 = jsonResponse[1];
      let hours1 = Math.floor(time2 / 3600);
      let minutes1 = Math.floor((time2 % 3600) / 60);
      let seconds1 = time2 % 60;

      //Anteponiendo un 0 a los minutos si son menos de 10
      minutes1 = minutes1 < 10 ? '0' + minutes1 : minutes1;

      //Anteponiendo un 0 a los segundos si son menos de 10
      seconds1 = seconds1 < 10 ? '0' + seconds1 : seconds1;

      let result2 = hours1 + ':' + minutes1 + ':' + seconds1;
      //localStorage.setItem('totalPromResult2', result2);
      document.getElementById('generalInformation2').innerHTML = result2;
    }
  };
  var e = document.getElementById('selectUserId');
  var strUser = e.options[e.selectedIndex].value;
  var params = 'idUser=' + strUser + '&idCourse=' + courseId;
  var windowLocation = window.location;
  var pathname = windowLocation.pathname.split('/');
  var directory =
    windowLocation.origin +
    '/' +
    pathname[1] +
    '/blocks/promuser/helpers/generalInformation.php';
  //console.log(directory);
  xhttp.open('POST', directory, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function getTotalResults() {
  //let result1 = localStorage.getItem('totalPromResult1');
  //let result2 = localStorage.getItem('totalPromResult2');

  //return [result1, result2];
}

function setProm() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      generateTableUser(JSON.parse(this.responseText));
    }
  };
  var e = document.getElementById('selectUserId');
  var strUser = e.options[e.selectedIndex].value;
  var params = 'idUser=' + strUser + '&idCourse=' + courseId;
  var windowLocation = window.location;
  var pathname = windowLocation.pathname.split('/');
  var directory =
    windowLocation.origin +
    '/' +
    pathname[1] +
    '/blocks/promuser/helpers/getProm.php';

  xhttp.open('POST', directory, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function setPromUser() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      setPromByDay(JSON.parse(this.responseText));
    }
  };
  var e = document.getElementById('selectUserId');
  var strUser = e.options[e.selectedIndex].value;
  var params = 'idUser=' + strUser + '&idCourse=' + courseId;
  var windowLocationPromDay = window.location;
  var pathnamePromDay = windowLocationPromDay.pathname.split('/');
  var directoryPromByDay =
    windowLocationPromDay.origin +
    '/' +
    pathnamePromDay[1] +
    '/blocks/promuser/helpers/getPromByDayUser.php';

  xhttp.open('POST', directoryPromByDay, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function showGraphic() {
  var windowLocation = window.location;
  var pathname = windowLocation.pathname.split('/');
  var directory =
    windowLocation.origin +
    '/' +
    pathname[1] +
    '/blocks/promuser/helpers/getProm.php';
  window.open(
    windowLocation.origin +
      '/' +
      pathname[1] +
      '/blocks/promuser/graphics/graphic.php?var=' +
      document.getElementById('selectUserId').options[
        document.getElementById('selectUserId').selectedIndex
      ].value +
      '&courseVar=' +
      courseId
  );
}

function showGraphicNetworks() {
  var windowLocation = window.location;
  var pathname = windowLocation.pathname.split('/');
  window.open(
    windowLocation.origin +
      '/' +
      pathname[1] +
      '/blocks/promuser/graphics/graphicNetwork.php?var=' +
      document.getElementById('selectUserId').options[
        document.getElementById('selectUserId').selectedIndex
      ].value +
      '&courseVar=' +
      courseId
  );
}

function showGraphicClassNetworks() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      setPromActivitiesPerStudentTable(JSON.parse(this.responseText));
      console.log(JSON.parse(this.responseText));
      document.getElementById('graphic-class').innerHTML = 'Mostrar gráfico';
    }
  };
  var windowLocationPromDay = window.location;
  var pathnamePromDay = windowLocationPromDay.pathname.split('/');
  var directoryPromByDay =
    windowLocationPromDay.origin +
    '/' +
    pathnamePromDay[1] +
    '/blocks/promuser/tables/table_getPromActivitiesPerStudent.php';
  var params = 'idCourse=' + courseId;
  document.getElementById('graphic-class').innerHTML = 'Generando...';
  xhttp.open('POST', directoryPromByDay, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
  //-----------------------------------------------------------------------------
}

function setLoadingGraph() {
  document.getElementById('graphic-class').innerHTML = 'Generando...';
}

function setPromActivitiesPerStudentTable(json_data) {
  sessionStorage.setItem('classInformationByDay', JSON.stringify(json_data));

  var windowLocation = window.location;
  var pathname = windowLocation.pathname.split('/');
  window.open(
    windowLocation.origin +
      '/' +
      pathname[1] +
      '/blocks/promuser/graphics/graphicClassNetwork.php?classInformationByDay'
  );
}

function showTableActivitiesPerInterval() {
  setEmptyTable('interval');
}

function updateTableActivitiesPerInterval() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      setPromActivitiesPerOptionInterval(JSON.parse(this.responseText));
      $('#updateTable').text('Actualizar datos');
    }
  };
  $('#updateTable').text('Cargando...');
  var windowLocationPromDay = window.location;
  var pathnamePromDay = windowLocationPromDay.pathname.split('/');
  var directoryPromByDay =
    windowLocationPromDay.origin +
    '/' +
    pathnamePromDay[1] +
    '/blocks/promuser/tables/table_getPromActivitiesPerOption.php';
  var params = 'option=interval&idCourse=' + courseId;
  xhttp.open('POST', directoryPromByDay, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function showTableActivitiesPerDay() {
  setEmptyTable('day');
}

function updateTableActivitiesPerDay() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      setPromActivitiesPerOptionDay(JSON.parse(this.responseText));
      $('#updateTable').text('Actualizar datos');
    }
  };
  $('#updateTable').text('Cargando...');
  var windowLocationPromDay = window.location;
  var pathnamePromDay = windowLocationPromDay.pathname.split('/');
  var directoryPromByDay =
    windowLocationPromDay.origin +
    '/' +
    pathnamePromDay[1] +
    '/blocks/promuser/tables/table_getPromActivitiesPerOption.php';
  var params = 'option=day&idCourse=' + courseId;
  xhttp.open('POST', directoryPromByDay, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function setPromActivitiesPerOptionInterval(json_data) {
  /*localStorage.setItem(
    'PromActivitiesPerOptionInterval',
    JSON.stringify(json_data)
  );*/
  changeTitleModal('Tiempo promedio del grupo por actividad');
  let first_date = new Date(json_data['first_date']['date']);
  first_date =
    first_date.getDay() +
    '/' +
    (first_date.getMonth() + 1) +
    '/' +
    first_date.getFullYear() +
    ' ' +
    first_date.getHours() +
    ':' +
    first_date.getMinutes();
  delete json_data['first_date'];
  let last_date = new Date(json_data['last_date']['date']);
  last_date =
    last_date.getDay() +
    '/' +
    (last_date.getMonth() + 1) +
    '/' +
    last_date.getFullYear() +
    ' ' +
    last_date.getHours() +
    ':' +
    last_date.getMinutes();
  delete json_data['last_date'];
  generateTable(json_data, first_date, last_date);
}

function setPromActivitiesPerOptionDay(json_data) {
  //localStorage.setItem('PromActivitiesPerOptionDay', JSON.stringify(json_data));
  changeTitleModal('Tiempo promedio del grupo por día');
  let first_date = new Date(json_data['first_date']['date']);
  first_date =
    first_date.getDay() +
    '/' +
    (first_date.getMonth() + 1) +
    '/' +
    first_date.getFullYear() +
    ' ' +
    first_date.getHours() +
    ':' +
    first_date.getMinutes();
  delete json_data['first_date'];
  let last_date = new Date(json_data['last_date']['date']);
  last_date =
    last_date.getDay() +
    '/' +
    (last_date.getMonth() + 1) +
    '/' +
    last_date.getFullYear() +
    ' ' +
    last_date.getHours() +
    ':' +
    last_date.getMinutes();
  delete json_data['last_date'];
  generateTable(json_data, first_date, last_date);
}

function showUserSelectedAverages () {
  var userName =
    document.getElementById('selectUserId').options[
      document.getElementById('selectUserId').selectedIndex
    ].text;
  var userId =
    document.getElementById('selectUserId').options[
      document.getElementById('selectUserId').selectedIndex
    ].value;
  setProm();
  setPromUser();
  changeTitleModalUser('Tiempo promedio por día');
  changeStudentNameModalUser(userName);
}
