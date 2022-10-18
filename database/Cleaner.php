<?php

$contextFilePath = dirname(__FILE__) . '/../files/ContextData.txt';
$groupFilePath = dirname(__FILE__) . '/../files/Group.txt';
$StudentsFilePath = dirname(__FILE__) . '/../files/Students.txt';

unlink($contextFilePath);
unlink($groupFilePath);
unlink($StudentsFilePath);

?>