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
            'skip_status' => 'false'
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

    $today_only = isset($_GET['today_only']) && 1 === (int)$_GET['today_only'];
    $true_bot_only = isset($_GET['true_bot_only']) && 1 === (int)$_GET['true_bot_only'];
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

    <style>
        .table-header-row {
            border-bottom: 2px solid #d3d3d3;
            padding: 7px;
            margin-bottom: 12px;
            word-wrap: break-word;
        }

        .table-row {
            border-bottom: 1px solid #d3d3d3;
            padding: 7px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .selected-row {
            background-color: #a9cef880;
        }

        .table-row {
            user-select: none; /* CSS3 (little to no support) */
            -ms-user-select: none; /* IE 10+ */
            -moz-user-select: none; /* Gecko (Firefox) */
            -webkit-user-select: none; /* Webkit (Safari, Chrome) */
        }
    </style>
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

                    <div class="text-right pb-2">
                        <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#filters"
                                aria-expanded="false" aria-controls="filters">
                            Filters
                        </button>
                    </div>

                    <div class="collapse" id="filters">
                        <div class="card card-body">
                            <form action="<?php echo $config['base_url']; ?>" method="get">
                                <div class="form-group">
                                    <input type="checkbox" title="Today Only" name="today_only" id="today_only"
                                        <?php echo $today_only ? 'checked' : ''; ?>
                                           value="1"/>
                                    <label for="today_only">Created Time - Today Only</label>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" title="true_bot_only" name="true_bot_only" id="true_bot_only"
                                        <?php echo $true_bot_only ? 'checked' : ''; ?>
                                           value="1"/>
                                    <label for="true_bot_only">Possible Bot - 100% Only</label>
                                </div>
                                <input type="submit" class="btn btn-primary" value="Refresh List"/>
                            </form>
                        </div>
                    </div>

                    <form action="<?php echo $config['base_url']; ?>" method="post">
                        <div class="">
                            <div class="container-fluid">
                                <div class="row table-header-row d-none d-sm-flex">
                                    <div class="col-md-1 col-sm-2 col-5">
                                        <strong>UP</strong>
                                    </div>
                                    <div class="col-md-5 col-sm-7 col-5">
                                        <strong>Name</strong>
                                    </div>
                                    <div class="col-md-1 col-sm-2 col-12">
                                        <strong>Created Time</strong>
                                    </div>
                                    <div class="col-md-1 col-sm-3 col-12">
                                        <strong>You Follow</strong>
                                    </div>
                                    <div class="col-md-1 col-sm-3 col-12">
                                        <strong>Following/Followers</strong>
                                    </div>
                                    <div class="col-md-1 col-sm-3 col-12">
                                        <strong>Tweets Count</strong>
                                    </div>
                                    <div class="col-md-1 col-sm-3 col-12">
                                        <strong>Possible bot?</strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <input type="submit" class="btn btn-danger" name="action"
                                               value="Report'n'Block!">
                                        <a href="<?php echo $config['base_url'] . '?' . http_build_query(array("_t" => time())); ?>"
                                           class="btn btn-success">Refresh List</a>
                                    </div>
                                </div>
                                <?php if (isset($followers) && is_array($followers) && count($followers)) { ?>
                                    <?php foreach ($followers as $follower) { ?>
                                        <?php

                                        /* Created Time Calculation */
                                        $created_time = ceil((time() - strtotime($follower->created_at)) / 86400);

                                        /* Possible Bot Index Calculation */
                                        $bot_value = 0;

                                        /* 1. Most of bots were created less than 3 days ago */
                                        if (ceil((time() - strtotime($follower->created_at)) / 86400) <= 3)
                                            $bot_value++;

                                        /* 2. Most of bots follow many account with low follow-back rate */
                                        if ($follower->friends_count > $follower->followers_count)
                                            $bot_value++;

                                        /* 3. Most of bots have only few followers */
                                        if ($follower->followers_count < 5)
                                            $bot_value++;

                                        /* 4. Most of bots have no tweets at all */
                                        if ((int)$follower->statuses_count === 0)
                                            $bot_value++;
                                        else {
                                            /* 5. Most of bots have only retweets, so last status 100% retweet */
                                            if (isset($follower->status) && isset($follower->status->retweeted_status)) {
                                                $bot_value++;
                                            }
                                        }

                                        /* 6. Most of bots have default profile picture */
                                        if ("https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png" === $follower->profile_image_url_https)
                                            $bot_value++;

                                        /* 7. Most of bots have no profile background */
                                        if (NULL === $follower->profile_background_image_url_https)
                                            $bot_value++;

                                        /* Filters */

                                        if ($today_only && $created_time > 1)
                                            continue;

                                        if ($true_bot_only && $bot_value < 6)
                                            continue;

                                        ?>
                                        <div class="row table-row">
                                            <input type="checkbox" class="d-none"
                                                   title="<?php echo $follower->screen_name; ?>"
                                                   name="follower_id[]" value="<?php echo $follower->id; ?>">
                                            <div class="col-md-1 col-sm-2 col-5">
                                                <img src="<?php echo $follower->profile_image_url_https; ?>"
                                                     alt="<?php echo $follower->screen_name; ?>" class="img-thumbnail">
                                            </div>
                                            <div class="col-md-5 col-sm-7 col-5">
                                                <?php echo $follower->name; ?>&nbsp;<a
                                                        href="https://twitter.com/<?php echo $follower->screen_name; ?>"
                                                        target="_blank">(@<?php echo $follower->screen_name; ?>)</a>
                                            </div>
                                            <div class="col-md-1 col-sm-2 col-12">
                                                <span class="d-xs-block d-sm-none">
                                                    <strong>Created Time:</strong>
                                                </span>
                                                <?php echo $created_time . " day(s) ago"; ?>
                                            </div>
                                            <div class="col-md-1 col-sm-3 col-12">
                                                <span class="d-xs-block d-sm-none">
                                                    <strong>You Follow:</strong>
                                                </span>
                                                <?php echo $follower->following ? '<strong class="btn btn-success">Yes</strong>' : "No"; ?>
                                            </div>
                                            <div class="col-md-1 col-sm-3 col-12">
                                                <span class="d-xs-block d-sm-none">
                                                    <strong>Following/Followers:</strong>
                                                </span>
                                                <?php echo $follower->friends_count; ?>
                                                &nbsp;/&nbsp;
                                                <?php echo $follower->followers_count; ?>
                                            </div>
                                            <div class="col-md-1 col-sm-3 col-12">
                                                <span class="d-xs-block d-sm-none">
                                                    <strong>Tweets Count</strong>
                                                </span>
                                                <?php echo $follower->statuses_count; ?>
                                            </div>
                                            <div class="col-md-1 col-sm-3 col-12">
                                                <span class="d-xs-block d-sm-none">
                                                    <strong>Possible bot? -</strong>
                                                </span>
                                                <?php

                                                switch ($bot_value) {
                                                    case 7:
                                                    case 6:
                                                        echo '<strong class="btn btn-danger">100%</strong>';
                                                        break;
                                                    case 5:
                                                        echo '<strong class="btn btn-primary">70%</strong>';
                                                        break;
                                                    case 4:
                                                    case 3:
                                                        echo '<strong class="btn btn-info">50%</strong>';
                                                        break;
                                                    case 2:
                                                        echo '<strong class="btn btn-success">20%</strong>';
                                                        break;
                                                    case 1:
                                                    case 0:
                                                    default:
                                                        echo "0%";
                                                }

                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-md-1 col-sm-12">Nothing found.</div>
                                    </div>
                                <?php } ?>
                                </tbody>
                                </table>
                            </div>
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
<script>
    var row_start = null, row_end, checkbox, rows;

    $(function () {
        $('.table-row').click(function (e) {
            if (e.target.tagName === "A") return;

            row_start = $(this).toArray()[0];

            checkbox = $(this).find('input[type=checkbox]');
            if (checkbox.length) {
                checkbox.prop('checked', !checkbox.prop('checked'));

                if (checkbox.prop('checked')) {
                    $(this).addClass('selected-row');
                } else {
                    $(this).removeClass('selected-row');
                }
            }
        });

        $('.table-row').mousedown(function (e) {
            if (e.target.tagName === "A") return;

            if (row_start !== null && e.shiftKey) {
                row_end = $(this).toArray()[0];

                rows = $('.table-row').toArray();

                if (row_start !== row_end && rows.indexOf(row_start) !== -1 && rows.indexOf(row_end) !== -1) {
                    if (rows.indexOf(row_start) > rows.indexOf(row_end)) {
                        for (var i = (rows.indexOf(row_end) + 1); i < rows.indexOf(row_start); i++) {
                            checkbox = $(rows[i]).find('input[type=checkbox]');
                            if (checkbox.length) {
                                checkbox.prop('checked', !checkbox.prop('checked'));

                                if (checkbox.prop('checked')) {
                                    $(rows[i]).addClass('selected-row');
                                } else {
                                    $(rows[i]).removeClass('selected-row');
                                }
                            }
                        }
                    } else {
                        for (var i = (rows.indexOf(row_start) + 1); i < rows.indexOf(row_end); i++) {
                            checkbox = $(rows[i]).find('input[type=checkbox]');
                            if (checkbox.length) {
                                checkbox.prop('checked', !checkbox.prop('checked'));

                                if (checkbox.prop('checked')) {
                                    $(rows[i]).addClass('selected-row');
                                } else {
                                    $(rows[i]).removeClass('selected-row');
                                }
                            }
                        }
                    }
                }

                row_start = null;
                row_end = null;
            }
        });
    });
</script>
<?php

// Create footer.php and put Google Analytics code there if you want any
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'footer.php')) include_once __DIR__ . DIRECTORY_SEPARATOR . 'footer.php';

?>
</body>
</html>