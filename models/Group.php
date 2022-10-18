<?php

class Group {
    public $users;
    public $SATS;
    public $SATPD;

    //Funcion para obtener SATS
    public function getSemesterAvgTimeSpent($professorId) {
        $sumaPromediosGrupo = 0;
        $arrayTiemposAlumnos = array();

        $resultado = loadUsers();

        foreach ($resultado as $rs) {
            if ($professorId != $rs->userid) {
                $promedioTiempoAlumno = loadSATS($rs->userid);
                $sumaPromediosGrupo += $promedioTiempoAlumno;
                array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
            }
        }

        $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);
        $valorTotalGrupo = round($valorTotalGrupo);
        
        $SATS = $valorTotalGrupo;
        $this->SATS = $SATS;
        
        return $SATS;
    }

    //Funcion para obtener SATPD
    public function getSemesterAvgTimePerDay($professorId) {
        $sumaPromediosGrupo = 0;
        $arrayTiemposAlumnos = array();

        $users = loadUsers();

        foreach ($users as $aUser) {
            if ($professorId != $aUser->userid) {
                $promedioTiempoAlumno = loadSATSPD($aUser->userid);
                $sumaPromediosGrupo += $promedioTiempoAlumno;
                array_push($arrayTiemposAlumnos, $promedioTiempoAlumno);
            }
        }

        $valorTotalGrupo = $sumaPromediosGrupo / sizeof($arrayTiemposAlumnos);
        $valorTotalGrupo = round($valorTotalGrupo);
        
        $SATPD = $valorTotalGrupo;
        $this->SATPD = $SATPD;
        
        return $SATPD;
    }
}
