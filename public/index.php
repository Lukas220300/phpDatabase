<?php

use SCHOENBECK\Database\DatabaseConfigurator;

include_once "../vendor/autoload.php";

use SCHOENBECK\Database\DatabaseAdapter;
use SCHOENBECK\Database\DatabaseConfig;
use SCHOENBECK\Database\DatabaseConnection;


$config = new DatabaseConfig("database", "lamp", "lamp", 3306, "lamp", "mysql");

$connection = new DatabaseConnection($config);

//echo $connection->__toString() . "<br>";

$adaptor = new DatabaseAdapter($connection);

//echo $adaptor->selectFromTable("test");



$dbConfig = new DatabaseConfigurator($adaptor);

$result = $dbConfig->checkDatabaseConfigYamlFile("./database.yml", true);

print_r($result);