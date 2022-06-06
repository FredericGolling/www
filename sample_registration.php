<?php

require_once("database.php");

$database = new Database();
$register = [];

if (!$database->prepare_registration()) {
    $register["success"] = false;
    $register["message"] = "Storage not ready.";
    echo json_encode($register);
    return false;
}

if (!isset($_GET['sample_name']) || !isset($_GET['material']) || !isset($_GET['container_number'])) {
    $register["success"] = false;
    $register["message"] = "Parameter missing.";
    echo json_encode($register);
    return false;
}

$sample_name = $_GET['sample_name'];
$material = $_GET['material'];
$container_number = $_GET['container_number'];


$storage = $database->register_sample($sample_name, $material, $container_number);

if ($storage) {
    $register["success"] = true;
    $register["message"] = "Registration successful.";
    echo json_encode($register);
    return true;
}

$register["success"] = false;
$register["message"] = "Registration not successful.";
echo json_encode($register);
return false;