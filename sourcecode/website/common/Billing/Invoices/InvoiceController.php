<?php

namespace Common\Billing\Invoices;

use Common\Billing\Gateways\Stripe\StripeGateway;
use Common\Billing\Subscription;
use Common\Core\AppUrl;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @param Request $request
     * @param Invoice $invoice
     */
    public function __construct(Request $request, Invoice $invoice)
    {
        $this->request = $request;
        $this->invoice = $invoice;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', [Invoice::class, $this->request->get('userId')]);

        $invoices = $this->invoice->with('subscription.plan')
            ->whereHas('subscription', function(Builder $builder) {
                $builder->where('user_id', $this->request->get('userId'));
            })->get();

        return $this->success(['invoices' => $invoices]);
    }

    public function show($uuid)
    {
        $invoice = $this->invoice->where('uuid', $uuid)
            ->with('subscription.plan', 'subscription.user')
            ->firstOrFail();

        $this->authorize('show', $invoice);

        return view('common::billing/invoice')
            ->with('invoice', $invoice)
            ->with('htmlBaseUri', app(AppUrl::class)->htmlBaseUri)
            ->with('user', $invoice->subscription->user)
            ->with('settings', app(Settings::class));
    }
}
