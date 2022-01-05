<?php

namespace App\Http\Controllers;

use App\BitClout;
use App\Http\Requests\OnTip;
use Common\Core\BaseController;
use GuzzleHttp\Exception\GuzzleException;
use Str;

class BitCloutController extends BaseController
{
    protected BitClout $bitClout;

    public function __construct(BitClout $bitClout)
    {
        $this->bitClout = $bitClout;
    }

    /**
     * @todo this function should be replaced with a proper verification method as it relies on the user input
     * This, however, is not possible, due to the fact that transactions on BitClout can take a long time to sync.
     * As an example, if a user queries a TransactionIDBase58Check that they created recently, the API is likely to respond with the following:
     * HTTP STATUS: 400 RESPONSE: {Error: APITransactionInfo: Could not find transaction with TransactionIDBase58Check = USER_TRANSACTION_ID}
     * @throws GuzzleException
     */
    public function onTip(OnTip $request)
    {
        $message = config('bitclout.message.on_tip');

        $donor = $request->donor ? "@$request->donor" : config('bitclout.message.anonymous');
        $donee = "@$request->donee";

        $message = Str::replace(':donor:', $donor, $message);
        $message = Str::replace(':donee:', $donee, $message);
        $message = Str::replace(':amount:', $request->amount, $message);
        $message = Str::replace(':amount_usd:', $request->amount_usd, $message);

        $this->bitClout->submit($message);

        return $this->success();
    }
}
