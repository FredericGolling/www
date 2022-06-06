<?php

require_once("database.php");

$database = new Database();

$drop = $database->drop_all();