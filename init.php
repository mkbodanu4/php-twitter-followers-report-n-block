<?php

error_reporting(E_ALL);
session_start();

if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'config.php')) die('No configuration file found!');
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
