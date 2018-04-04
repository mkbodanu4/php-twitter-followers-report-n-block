<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';
session_destroy();
header('Location: ' . $config['base_url']);
