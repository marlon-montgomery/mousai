
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Invoice')}}</title>
    <base href="{{ $htmlBaseUri }}">
    <link rel="stylesheet" href="client/assets/css/invoice.css">
    <style>
        h1 {
            display: inline-block;
        }

        h2 { font-size: 2em; }

        .invoice-details {
            float: right;
        }

        .invoice-details span { font-weight: normal; font-size: .9em;}

        .total { float: right; }
        .total .amount { font-weight: normal; }

        table { margin-top: 50px; }
        thead { background: #4662fa; color: white; }
        thead th { border: none !important; }

        .notice {
            background: #d7d7d7;
            padding: 2em;
            clear: both;
            margin-top: 6em;
        }

        .col {
            float: left;
            width: 300px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <h1>{{parse_url(config('app.url'))['host']}}</h1>
        <div class="invoice-details">
            <h2 contenteditable>{{__('Invoice ID')}}: {{$invoice['uuid']}}</h2>
            <h4 contenteditable>{{__('Invoice Date')}}: <span id="date-of-invoice">{{$invoice['created_at']}}</span></h4>
        </div>
    </div>
    <div class="row billing__references">
        <div class="col">
            <h4>{{__('Billed To')}}:</h4>
            <h5 contenteditable>{{$user['display_name']}}</h5>
            <p contenteditable>{{$user['email']}}</p>
        </div>
        <div class="col">
            <h4>{{__('From')}}:</h4>
            <h5>{{config('app.name')}}</h5>
            @if($address = $settings->get('billing.invoice.address'))
                <p class="address" contenteditable>
                    {!! $address !!}
                </p>
            @endif
        </div>
    </div>
    <div class="row billing__lines">
        <table class="lines table table-striped">
            <thead>
            <tr>
                <th>{{__('Description')}}</th>
                <th>{{__('Qty')}}</th>
                <th>{{__('Price')}}</th>
            </tr>
            </thead>
            <tbody>
            <tr contenteditable>
                <td>{{config('app.name')}} {{__('Subscription Dues')}} ({{$invoice['subscription']['plan']['name']}} {{__('plan')}})</td>
                <td>1</td>
                <td>{{$invoice['currency_symbol']}}{{$invoice['subscription']['plan']['amount']}}</td>
            </tr>
            </tbody>
        </table>
        <div class="total">
            <h4>{{__('Total')}}: <span class="amount">{{$invoice['subscription']['plan']['currency_symbol']}}{{$invoice['subscription']['plan']['amount']}} {{$invoice['subscription']['plan']['currency']}}</span></h4>
        </div>
        @if($notes = $settings->get('billing.invoice.notes'))
            <div class="notes notice" contenteditable>
                <h5>{{__('Notes')}}</h5>
                <p>{!! $notes !!}</p>
            </div>
        @endif
    </div>
</div>

<script>
    window.print();
</script>
</body>
</html>
