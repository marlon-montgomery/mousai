<html lang="en">
<head>
    <title>Update</title>
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
            text-transform: uppercase;
            text-align: center;
            box-shadow: 0 2px 5px 0 rgba(0,0,0,.26);
        }
        .button[disabled] {
            background: rgba(0,0,0,0.12);
            color: rgba(0,0,0,0.26);
            cursor: default;
            box-shadow: none;
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
            margin: 15px 0 25px 0;
        }
        h3 {
            font-weight: 400;
            font-size: 18px;
        }
        ul {
            list-style: none;
        }
        li {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(0,0,0,0.12);
            color: #f2564d;
        }
        li:last-of-type {
            border: none;
        }
    </style>
</head>

<body>
<div class="container">
    <img class="img-responsive" src="client/assets/images/logo-dark.png" alt="logo">

    @if($requirementsFailed)
        <div class="panel">
            <h3>Detected the following problems. Please correct them before proceeding with the update.</h3>
            <ul class="errors">
                @foreach($requirements as $req)
                    @if(!$req['result'])
                        <li>{{$req['errorMessage']}}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    <form class="panel" action="secure/update/run" method="post">
        {{ csrf_field() }}
        <p>This might take several minutes, please don't close this browser tab while update is in progress.</p>
        @if($requirementsFailed)
            <button class="button" type="submit" disabled>Update Now</button>
        @else
            <button class="button" type="submit">Update Now</button>
        @endif
    </form>
</div>
</body>
</html>
