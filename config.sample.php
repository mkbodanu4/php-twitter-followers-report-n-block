<?php

$config['base_url'] = "http://localhost/php-twitter-followers-report-n-block/";
$config['url_login'] = "http://localhost/php-twitter-followers-report-n-block/login.php";

// While creating app at https://apps.twitter.com/
$config['url_callback'] = "http://localhost/php-twitter-followers-report-n-block/callback.php"; // https://apps.twitter.com/app/new => Callback URL

// When app created -> https://apps.twitter.com/app/{ID}/keys
$config['consumer_key'] = ""; // Consumer Key (API Key)
$config['consumer_secret'] = ""; // Consumer Secret (API Secret)

$config['max_at_once'] = 200; // Followers to load