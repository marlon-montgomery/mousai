<html lang="en">
<head>
    <title>Update Complete</title>
    <base href="{{ $htmlBaseUri }}">
    <meta name="robots" content="noindex,nofollow">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            background: #f6f5f2;
            overflow: auto;
            font-family: Roboto,'Helvetica Neue',sans-serif;
            font-size: 16px;
            text-align: center;
        }
        .button {
            display: block;
            color: #fff;
            background-color: #1565C0;
            border: 1px solid transparent;
            padding: 0 8px;
            border-radius: 3px;
            font-size: 14px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            min-width: 88px;
            line-height: 36px;
            height: 36px;
            text-transform: uppercase;
            text-align: center;
            box-shadow: 0 2px 5px 0 rgba(0,0,0,.26);
            text-decoration: none;
            margin-top: 25px;
            max-width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding-top: 80px;
        }
        .panel {
            background: #fff;
            box-shadow: 1px 1px 2px 0 #d0d0d0;
            padding: 20px 30px 40px;
            margin-top: 50px;
            border-radius: 4px;
        }
        p {
            margin: 15px 0 35px 0;
        }
        h3 {
            font-weight: 400;
            font-size: 20px;
            margin-bottom: 0;
        }
        h4 {
            margin-bottom: 10px;
        }
        .support-links-container {
            border-top: 1px solid #E0E0E0;
            margin-top: 10px;
            padding-top: 10px;
        }
    </style>
</head>

<body>
<div class="container">
    <img class="img-responsive" src="client/assets/images/logo-dark.png" alt="logo">

    <div class="panel">
        <h3 class="install-completed-header">Update has been successfully completed!</h3>

        <p>You can close this page now and continue using the site.</p>

        <div class="support-links-container">
            <h4>Support and Questions</h4>
            <div>If you encounter any issues after the update, please submit a ticket <a target="_blank" href="https://support.vebto.com">here</a>. Thanks!</div>
        </div>

        <a href="/" class="button">Close Update Page</a>
    </div>
</div>
</body>
</html>
