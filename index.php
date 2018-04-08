<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

use Abraham\TwitterOAuth\TwitterOAuth;

if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $screen_name = $_SESSION['screen_name'];
    $img = $_SESSION['img'];
    $followers_count = $_SESSION['followers_count'];
    $oauth_token = $_SESSION['oauth_token'];
    $oauth_token_secret = $_SESSION['oauth_token_secret'];

    if (count($_POST) > 0 && isset($_POST['action']) && $_POST['action'] === "Report'n'Block!") {
        if (isset($_POST['follower_id']) && is_array($_POST['follower_id']) && count($_POST['follower_id']) > 0) {
            $followers_id = $_POST['follower_id'];
            try {
                $twitter = new TwitterOAuth(
                    $config['consumer_key'],
                    $config['consumer_secret'],
                    $oauth_token,
                    $oauth_token_secret
                );

                $errors = array();
                $success_count = 0;

                foreach ($followers_id as $follower_id) {
                    $report = $twitter->post('users/report_spam', array(
                        'user_id' => $follower_id,
                        'perform_block' => true
                    ));

                    if (isset($report->error)) {
                        $error[] = $report->error;
                    } else $success_count++;
                }

                $_SESSION['error'] = implode('<br/>', $errors);
                $_SESSION['success'] = "You successfully reported " . $success_count . " followers!";

                $_SESSION['last_refresh'] = NULL; // Let script reload amount of followers on next page load
            } catch (Exception $e) {
                $error = 'System Error: ' . $e->getMessage();
            }

        } else {
            $_SESSION['error'] = "You must select at least one follower to report!";
        }

        header('Location: ' . $config['base_url']);
        exit;
    }


    try {
        $twitter = new TwitterOAuth(
            $config['consumer_key'],
            $config['consumer_secret'],
            $oauth_token,
            $oauth_token_secret
        );

        $followers_raw = $twitter->get('followers/list', array(
            'user_id' => $user_id,
            'screen_name' => $screen_name,
            'count' => $config['max_at_once'],
            'skip_status' => 'true'
        ));

        if (isset($followers_raw->users) && is_array($followers_raw->users) && count($followers_raw->users))
            $followers = $followers_raw->users;
        elseif (isset($followers_raw->errors)) {
            $error = $followers_raw->errors[0]->message;
            if ($followers_raw->errors[0]->message === 'Rate limit exceeded') {
                $error .= '. <strong>Cool down, wait few minutes, app will work again soon :)</strong>';
                $xheaders = $twitter->getLastXHeaders();
                $error .= '. Current available API requests: ' .
                    $xheaders['x_rate_limit_remaining'] .
                    '/' .
                    $xheaders['x_rate_limit_limit'] .
                    '. Estimate limit reset: ' .
                    date("H:i:s d/m/Y", $xheaders['x_rate_limit_reset']) .
                    ', in ' .
                    ($xheaders['x_rate_limit_reset'] - time()) . ' seconds!';
            }
        }

    } catch (Exception $e) {
        $error = 'System Error: ' . $e->getMessage();
    }

    if (!isset($_SESSION['last_refresh']) || !$_SESSION['last_refresh'] || (int)$_SESSION['last_refresh'] < (time() - 400)) {
        try {
            $me = $twitter->get('users/show', array(
                'user_id' => $user_id,
                'screen_name' => $screen_name
            ));

            $_SESSION['followers_count'] = $followers_count = $me->followers_count;
            $_SESSION['last_refresh'] = time();
        } catch (Exception $e) {
            $error = 'System Error: ' . $e->getMessage();
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Twitter Followers Report'n'Block</title>
</head>
<body>

<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="<?php echo $config['base_url']; ?>">Twitter Followers
        Report'n'Block</a>

    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
            <?php if (!isset($user_id) || !$user_id) { ?>
                <a href="<?php echo $config['base_url'] . 'login.php'; ?>" class="nav-link">
                    Login
                </a>
            <?php } else { ?>
                <a href="<?php echo $config['base_url'] . 'logout.php'; ?>" class="nav-link">
                    Logout
                </a>
            <?php } ?>
        </li>
    </ul>
</nav>

<div class="container-fluid">
    <?php if (!isset($user_id) || !$user_id) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger mt-2 text-right">
                    Click on Login button. <strong>C'mon! <a href="<?php echo $config['base_url'] . 'login.php'; ?>">Click
                            up here!</a></strong>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-3">
                    <?php if (isset($error) && $error) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php } ?>
                    <?php if (isset($success) && $success) { ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php } ?>

                    <div>
                        <img src="<?php echo $img; ?>" alt="<?php echo $screen_name; ?>" class="img-thumbnail">
                        <span>
                            Hello, <strong><?php echo $screen_name; ?>!</strong>
                        </span>

                        You have <?php echo $followers_count; ?> followers,
                        first <?php echo $followers_count < $config['max_at_once'] ? $followers_count : $config['max_at_once']; ?>
                        of them shown, you can
                        select and report them as bots.
                    </div>

                    <form action="<?php echo $config['base_url']; ?>" method="post">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">&nbsp;</th>
                                <th scope="col">#</th>
                                <th scope="col">UP</th>
                                <th scope="col">Name</th>
                                <th scope="col">Created At</th>
                                <th scope="col">You Follow</th>
                                <th scope="col">Following</th>
                                <th scope="col">Followers</th>
                                <th scope="col">Tweets Count</th>
                                <th scope="col">Possible bot?</th>
                            </tr>
                            <tr>
                                <td colspan="10" class="text-center">
                                    <input type="submit" class="btn btn-danger" name="action" value="Report'n'Block!">
                                    <a href="<?php echo $config['base_url'] . '?' . http_build_query(array("_t" => time())); ?>"
                                       class="btn btn-success">Refresh List</a>
                                </td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($followers) && is_array($followers) && count($followers)) { ?>
                                <?php foreach ($followers as $follower) { ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" title="<?php echo $follower->screen_name; ?>"
                                                   name="follower_id[]" value="<?php echo $follower->id; ?>">
                                        </td>
                                        <td>
                                            <?php echo $follower->id; ?>
                                        </td>
                                        <td>
                                            <img src="<?php echo $follower->profile_image_url_https; ?>"
                                                 alt="<?php echo $follower->screen_name; ?>" class="img-thumbnail">
                                        </td>
                                        <td>
                                            <?php echo $follower->name; ?>&nbsp;<a
                                                    href="https://twitter.com/<?php echo $follower->screen_name; ?>"
                                                    target="_blank">(@<?php echo $follower->screen_name; ?>)</a>
                                        </td>
                                        <td>
                                            <?php echo $follower->created_at; ?>
                                        </td>
                                        <td>
                                            <?php echo $follower->following ? '<strong class="text-success">Yes</strong>' : "No"; ?>
                                        </td>
                                        <td>
                                            <?php echo $follower->friends_count; ?>
                                        </td>
                                        <td>
                                            <?php echo $follower->followers_count; ?>
                                        </td>
                                        <td>
                                            <?php echo $follower->statuses_count; ?>
                                        </td>
                                        <td>
                                            <?php echo $follower->friends_count > $follower->followers_count && $follower->followers_count < 5 && (int)$follower->statuses_count === 0 && "https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png" === $follower->profile_image_url_https ? '<strong class="text-danger">Yes</strong>' : "No"; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="10">Nothing found.</td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<?php

// Create footer.php and put Google Analytics code there if you want any
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'footer.php')) include_once __DIR__ . DIRECTORY_SEPARATOR . 'footer.php';

?>
</body>
</html>