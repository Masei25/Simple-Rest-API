<?php

require "vendor/autoload.php";

use Dotenv\Dotenv;
use Src\System\DatabaseConnector;
use Src\TableGateways\PersonGateway;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

$dbConnect = (new DatabaseConnector())->getConnection();

$personGateway = new PersonGateway($dbConnect);
