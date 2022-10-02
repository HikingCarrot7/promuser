<?php

class Student {
    public $id;
    public $name;
    public $logs;
    public $average;
    public $averagePerActivity;
    public $averagePerDay;
    public $averagePerActivityPerDay;


    public function __construct($id) {
        $this->id = $id;
    }
}
