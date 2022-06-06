<?php

require_once("database.php");

$database = new Database();
$file_storage = [];

if (!$database->prepare_registration()) {
    $file_storage["success"] = false;
    $file_storage["message"] = "Storage not ready.";
    echo json_encode($file_storage);
    return false;
}

if (!isset($_GET['nid_name_param']) || !isset($_GET['sample_id_param']) || !isset($_GET['date_of_recording_param']) || !isset($_GET['nid_file_param'])) {
    $file_storage["success"] = false;
    $file_storage["message"] = "Parameter missing.";
    echo json_encode($file_storage);
    return false;
}

$nid_name = $_GET['nid_name_param'];
$sample_id = $_GET['sample_id_param'];
$date_of_recording = $_GET['date_of_recording_param'];
$nr_of_lines = $_GET['nr_of_lines_param'];
$nid_file = $_GET['nid_file_param'];


$storage = $database->store_file($nid_name, $nr_of_lines, $sample_id, $date_of_recording, $nid_file);

if ($storage) {
    $file_storage["success"] = true;
    $file_storage["message"] = "Storage successful.";
    echo json_encode($file_storage);
    return true;
}

$file_storage["success"] = false;
$file_storage["message"] = "Storage not successful.";
echo json_encode($file_storage);
return false;