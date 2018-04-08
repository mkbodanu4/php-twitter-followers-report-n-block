<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');

if (empty($oauth_verifier) ||
    empty($_SESSION['oauth_token']) ||
    empty($_SESSION['oauth_token_secret'])
) {
    header('Location: ' . $config['url_login']);
}

try {

    $connection = new TwitterOAuth(
        $config['consumer_key'],
        $config['consumer_secret'],
        $_SESSION['oauth_token'],
        $_SESSION['oauth_token_secret']
    );

    $token = $connection->oauth(
        'oauth/access_token', [
            'oauth_verifier' => $oauth_verifier
        ]
    );

    $twitter = new TwitterOAuth(
        $config['consumer_key'],
        $config['consumer_secret'],
        $token['oauth_token'],
        $token['oauth_token_secret']
    );

    $user = $twitter->get('account/verify_credentials');

    $_SESSION['user_id'] = $user->id;
    $_SESSION['name'] = $user->name;
    $_SESSION['screen_name'] = $user->screen_name;
    $_SESSION['img'] = $user->profile_image_url_https;
    $_SESSION['oauth_token'] = $token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $token['oauth_token_secret'];
    $_SESSION['followers_count'] = $user->followers_count;
    $_SESSION['last_refresh'] = time();

    header('Location: ' . $config['base_url']);
} catch (Exception $e) {
    die('Login Error: ' . $e->getMessage());
}