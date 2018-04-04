<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$twitteroauth = new TwitterOAuth($config['consumer_key'], $config['consumer_secret']);
try {
    $request_token = $twitteroauth->oauth(
        'oauth/request_token', [
            'oauth_callback' => $config['url_callback']
        ]
    );
    if ($twitteroauth->getLastHttpCode() != 200) {
        die('There was a problem performing this request');
    }

    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

    $url = $twitteroauth->url(
        'oauth/authenticate', [
            'oauth_token' => $request_token['oauth_token']
        ]
    );

    header('Location: ' . $url);
} catch (Exception $e) {
    die('Login Error: ' . $e->getMessage());
}