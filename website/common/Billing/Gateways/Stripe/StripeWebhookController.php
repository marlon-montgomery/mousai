<?php namespace Common\Billing\Gateways\Stripe;

use Common\Billing\Gateways\GatewayFactory;
use Common\Billing\Invoices\CrupdateInvoice;
use Common\Billing\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    /**
     * @var StripeGateway
     */
    private $gateway;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @param GatewayFactory $gatewayFactory
     * @param Subscription $subscription
     */
    public function __construct(GatewayFactory $gatewayFactory, Subscription $subscription)
    {
        $this->gateway = $gatewayFactory->get('stripe');
        $this->subscription = $subscription;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        if ( ! $this->gateway->webhookIsValid($request)) {
            return response('Webhook validation failed', 422);
        };

        switch ($payload['type']) {
            case 'customer.subscription.deleted':
                return $this->handleSubscriptionDeleted($payload);
            case 'invoice.payment_succeeded':
                return $this->handleSubscriptionRenewed($payload);
            case 'invoice.payment_failed':
                return $this->handleSubscriptionFailed($payload);
            default:
                return response('Webhook handled', 200);
        }
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return Response
     */
    protected function handleSubscriptionDeleted($payload)
    {
        $gatewayId = $payload['data']['object']['id'];

        $subscription = $this->subscription->where('gateway_id', $gatewayId)->first();

        if ($subscription && ! $subscription->cancelled()) {
            $subscription->markAsCancelled();
        }

        return response('Webhook Handled', 200);
    }

    /**
     * Handle a renewed stripe subscription.
     *
     * @param  array  $payload
     * @return Response
     */
    protected function handleSubscriptionRenewed($payload)
    {
        $gatewayId = $payload['data']['object']['subscription'];

        $subscription = $this->subscription->where('gateway_id', $gatewayId)->first();

        if ($subscription) {
            $stripeSubscription = $this->gateway->subscriptions()->find($subscription);
            $subscription->fill(['renews_at' => $stripeSubscription['renews_at']])->save();
            app(CrupdateInvoice::class)->execute([
                'subscription_id' => $subscription->id,
                'paid' => true,
            ]);
        }

        return response('Webhook Handled', 200);
    }

    protected function handleSubscriptionFailed($payload)
    {
        $gatewayId = $payload['data']['object']['subscription'];

        $subscription = $this->subscription->where('gateway_id', $gatewayId)->first();

        if ($subscription && ! $subscription->cancelled()) {
            $subscription->markAsCancelled();
        }

        return response('Webhook handled', 200);
    }
}
