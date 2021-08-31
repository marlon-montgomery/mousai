<?php

namespace Common\Billing\Listeners;

use Common\Billing\BillingPlan;
use Common\Billing\Gateways\GatewayFactory;
use Common\Settings\Events\SettingsSaved;
use Illuminate\Support\Collection;

class SyncPlansWhenBillingSettingsChange
{
    /**
     * @var GatewayFactory
     */
    private $gatewayFactory;

    /**
     * @param GatewayFactory $gatewayFactory
     */
    public function __construct(GatewayFactory $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * @param SettingsSaved $event
     */
    public function handle(SettingsSaved $event)
    {
        $s = $event->envSettings;
        @ini_set('max_execution_time', 300);
        $plans = BillingPlan::where('free', false)->orderBy('parent_id', 'asc')->get();

        if (array_key_exists('stripe_key', $s) || array_key_exists('stripe_secret', $s)) {
            $this->syncPlans('stripe', $plans);
        }

        if (array_key_exists('paypal_client_id', $s) || array_key_exists('paypal_secret', $s)) {
            $this->syncPlans('paypal', $plans);
        }
    }

    private function syncPlans($gatewayName, Collection $plans)
    {
        $gateway = $this->gatewayFactory->get($gatewayName);
        $plans->each(function(BillingPlan $plan) use($gateway) {
            if ( ! $gateway->plans()->find($plan)) {
                $gateway->plans()->create($plan);
            }
        });
    }
}
