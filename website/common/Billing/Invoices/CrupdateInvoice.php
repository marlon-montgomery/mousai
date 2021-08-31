<?php


namespace Common\Billing\Invoices;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CrupdateInvoice
{
    public function execute($data)
    {
        $invoice = new Invoice([
            'subscription_id' => $data['subscription_id'],
            'paid' => $data['paid'],
            'uuid' => Str::random(10),
            'notes' => Arr::get($data, 'notes'),
        ]);

        $invoice->save();

        return $invoice;
    }
}
