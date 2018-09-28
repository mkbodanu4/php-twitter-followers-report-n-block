<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
          integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <title>Terms Of Service / Report'n'Block</title>
</head>
<body>

<nav class="navbar navbar-expand-sm navbar-dark sticky-top bg-dark flex-md-nowrap">
    <a class="navbar-brand" href="<?php echo $config['base_url']; ?>">
        Report'n'Block
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#top-nav" aria-controls="top-nav"
            aria-expanded="false" aria-label="Open Menu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="top-nav">
        <ul class="navbar-nav">
            <li class="nav-item text-nowrap">
                <a href="https://github.com/mkbodanu4/php-twitter-followers-report-n-block" class="nav-link">
                    <i class="fab fa-github"></i> Source
                </a>
            </li>
            <li class="nav-item text-nowrap">
                <a href="<?php echo $config['base_url'] . 'policy.php'; ?>" class="nav-link">
                    <i class="fas fa-info-circle"></i> Privacy Policy
                </a>
            </li>
            <li class="nav-item text-nowrap">
                <a href="<?php echo $config['base_url'] . 'terms.php'; ?>" class="nav-link">
                    <i class="fas fa-info-circle"></i> Terms Of Service
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3 text-center">
                <div class="card-header">
                    <i class="fa fa-info"></i> Terms Of Service
                </div>
                <div class="card-body">
                    <b>
                        [NEED TO BE FILLED BY APP OWNER]
                    </b>
                </div>
                <div class="card-footer text-right">
                    &copy; <?php echo date("Y"); ?> <?php echo $config['app_owner'] ? $config['app_owner'] : ""; ?>
                </div>
            </div>
        </div>
    </div>
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