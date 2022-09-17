<?php

    class ContextData {

        public $courseId;
        public $courseContextId;
        public $studentRoleId;
        public $professorId;
        
        public function __construct ($courseId) {
            $this->courseId = $courseId;
        }
    }

?>