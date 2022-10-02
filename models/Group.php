<?php

class Group {
    public $averagePerSession;
    public $averagePerDay;
    public $users;
    public $averagePerActivity;
    public $averagePerActivityPerDay;

    public static function getSemesterAvgTimeSpent($idCourse, $idUser) {
        $sumaPromediosGrupo = 0;
        $arrayTiemposAlumnos = array();
        //Se obtiene el rol de estudiante con una función del archivo Queries.php
        $id_role_student = getStudentRoleId();
        //Se obtiene el contextId con una función del archivo Queries.php 
        $contextId = loadCourseContextId();
        //Se obtienen los usuarios de este curso con una función del archivo Queries.php
        $resultado = getUsersInThisCourse($idCourse);

        foreach ($resultado as $rs) {
            if ($idUser != $rs->userid) {
                $promedioTiempoAlumno = Student::getSemesterAvgTimeSpent($rs->userid, $idCourse);
                $sumaPromediosGrupo += $promedioTiempoAlumno;
                array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
            }
        }

        $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);
        $valorTotalGrupo = round($valorTotalGrupo);
        return $valorTotalGrupo;
    }

    public static function getSemesterAvgTimePerDay() {
        $course_id = loadCourseId();
        $professorId = loadProfessorId();

        $sumaPromediosGrupo = 0;
        $arrayTiemposAlumnos = array();

        //Se obtiene el rol de estudiante con una función del archivo Queries.php
        $id_role_student = getStudentRoleId();
        //Se obtiene el contextId con una función del archivo Queries.php 
        $contextId = loadCourseContextId();
        //Se obtienen los usuarios de este curso con una función del archivo Queries.php
        $users = loadUsers();

        foreach ($users as $aUser) {
            if ($professorId != $aUser->userid) {
                $promedioTiempoAlumno = Student::getSemesterAvgTimeSpentPerDay($aUser->userid, $course_id);
                $sumaPromediosGrupo += $promedioTiempoAlumno;
                array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
            }
        }

        $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);
        $valorTotalGrupo = round($valorTotalGrupo);
        return $valorTotalGrupo;
    }
}
