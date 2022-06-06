<?php

include("database.php");

$database = new Database();

if (!$database->prepare_registration()) {
    return false;
}

return true;