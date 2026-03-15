<?php
$conexion = new mysqli("localhost", "root", "", "skilltrack_ai");
if ($conexion->connect_error) { die("Error: " . $conexion->connect_error); }
?>