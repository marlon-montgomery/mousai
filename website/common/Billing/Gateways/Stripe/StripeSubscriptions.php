<?php namespace Common\Billing\Gateways\Stripe;

use App\User;
use Carbon\Carbon;
use Common\Billing\BillingPlan;
use Common\Billing\GatewayException;
use Common\Billing\Gateways\Contracts\GatewaySubscriptionsInterface;
use Common\Billing\Subscription;
use LogicException;
use Omnipay\Stripe\Gateway;

class StripeSubscriptions implements GatewaySubscriptionsInterface
{
    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @param Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Fetch specified subscription's details from gateway.
     *
     * @param Subscription $subscription
     * @return array
     * @throws GatewayException
     */
    public function find(Subscription $subscription)
    {
        $response = $this->gateway->fetchSubscription([
            'subscriptionReference' => $subscription->gateway_id,
            'customerReference' => $subscription->user->stripe_id,
        ])->send();

        if ( ! $response->isSuccessful()) {
            throw new GatewayException("Could not find stripe subscription: {$response->getMessage()}");
        }

        return [
            'subscription' => $response->getData(),
            'renews_at' => Carbon::createFromTimestamp($response->getData()['current_period_end']),
        ];
    }

    /**
     * Create a new subscription on stripe using specified plan.
     *
     * @param BillingPlan $plan
     * @param User $user
     * @param null $startDate
     * @return array
     * @throws GatewayException
     */
    public function create(BillingPlan $plan, User $user, $startDate = null)
    {
        if ($user->subscribedTo($plan, 'stripe')) {
            throw new LogicException("User already subscribed to '{$plan->name}' plan.");
        }

        $request = $this->gateway->createSubscription([
            'customerReference' => $user->stripe_id,
            'plan' => $plan->uuid,
        ]);
        $response = $request->sendData(array_merge(
            $request->getData(),
            [
                'trial_end' => $startDate ? Carbon::parse($startDate)->getTimestamp() : 'now',
                'expand' => ['latest_invoice.payment_intent'],
            ]
        ));

        if ( ! $response->isSuccessful()) {
            throw new GatewayException("Stripe subscription creation failed: {$response->getMessage()}");
        }

        $data = $response->getData();

        if ($data['latest_invoice']['payment_intent']['status'] === 'requires_action') {
            $status = 'requires_action';
        } else if ($data['status'] === 'active') {
            $status = 'complete';
        } else {
            $status = 'incomplete';
        }

        return [
            'status' => $status,
            'payment_intent_secret' => $data['latest_invoice']['payment_intent']['client_secret'],
            'reference' => $response->getSubscriptionReference(),
            'end_date' => $data['current_period_end'],
            'last_payment_error' => $data['latest_invoice']['payment_intent']['last_payment_error'] ?? null,
        ];
    }

    /**
     * Cancel specified subscription on stripe.
     *
     * @param Subscription $subscription
     * @param bool $atPeriodEnd
     * @return bool
     * @throws GatewayException
     */
    public function cancel(Subscription $subscription, $atPeriodEnd = true)
    {
        if ( ! $subscription->user->stripe_id) {
            return true;
        }

        // cancel subscription at current period end and don't delete
        if ($atPeriodEnd) {
            $request = $this->gateway->updateSubscription([
                'subscriptionReference' => $subscription->gateway_id,
                'customerReference' => $subscription->user->stripe_id,
                'plan' => $subscription->plan->uuid,
            ]);
            $response = $request->sendData(array_merge(
                $request->getData(),
                ['cancel_at_period_end' => 'true']
            ));
        // cancel and delete subscription instantly
        } else {
            $response = $this->gateway->cancelSubscription([
                'subscriptionReference' => $subscription->gateway_id,
                'customerReference' => $subscription->user->stripe_id,
            ])->send();
        }

        if ( ! $response->isSuccessful()) {
            throw new GatewayException("Stripe subscription cancel failed: {$response->getMessage()}");
        }

        return true;
    }

    /**
     * Resume specified subscription on stripe.
     *
     * @param Subscription $subscription
     * @param array $params
     * @return bool
     * @throws GatewayException
     */
    public function resume(Subscription $subscription, $params)
    {
        $response = $this->gateway->updateSubscription(array_merge([
            'plan' => $subscription->plan->uuid,
            'customerReference' => $subscription->user->stripe_id,
            'subscriptionReference' => $subscription->gateway_id,
        ], $params))->send();

        if ( ! $response->isSuccessful()) {
            throw new GatewayException("Stripe subscription resume failed: {$response->getMessage()}");
        }

        return true;
    }

    /**
     * Change billing plan of specified subscription.
     *
     * @param Subscription $subscription
     * @param BillingPlan $newPlan
     * @return boolean
     * @throws GatewayException
     */
    public function changePlan(Subscription $subscription, BillingPlan $newPlan)
    {
        $request = $this->gateway->updateSubscription([
            'plan' => $newPlan->uuid,
            'customerReference' => $subscription->user->stripe_id,
            'subscriptionReference' => $subscription->gateway_id,
        ]);

        $response = $request->sendData(array_merge(
            $request->getData(),
            ['proration_behavior' => 'always_invoice']
        ));

        if ( ! $response->isSuccessful()) {
            throw new GatewayException("Stripe subscription plan change failed: {$response->getMessage()}");
        }

        return true;
    }
}
