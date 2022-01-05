<?php

if (version_compare(PHP_VERSION, '7.3') === -1) {
    exit('You need at least PHP '.'7.3'.' to install this application.');
}

// if not installed yet, redirect to public dir
if ( ! file_exists(__DIR__.'/.env') || ! (preg_match('/INSTALLED=(true|1)/', file_get_contents(__DIR__.'/.env')))) {
    header("Location: public");
    die();
}

?>

<html lang="en">
<head>
    <title>.htaccess error</title>
    <style>
        body {
            background: rgb(250, 250, 250);
            color: rgba(0, 0, 0, 0.87);
            padding: 30px;
        }
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        img {
            max-width: 200px;
        }
        .panel {
            background: rgb(255, 255, 255);
            margin: auto;
            border: 1px solid rgba(0, 0, 0, 0.12);
            padding: 25px;
            border-radius: 4px;
            max-width: 800px;
            text-align: center;
        }
        h1 {
            margin: 0 0 10px;
        }
        p {
            font-size: 17px;
        }
    </style>
</head>
<body>
<div class="logo">
    <img class="img-responsive" src="client/assets/images/logo-dark.png" alt="logo">
</div>
<div class="panel">
    <h1>Could not find .htaccess file</h1>
    <p>See the article <a href="https://support.vebto.com/help-center/articles/21/27/172/site-not-loading">here</a> for possible solutions.</p>
</div>
</body>
</html>
