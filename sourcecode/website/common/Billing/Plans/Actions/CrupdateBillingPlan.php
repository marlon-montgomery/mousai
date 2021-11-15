<?php

namespace Common\Billing\Plans\Actions;

use Common\Auth\Permissions\Traits\SyncsPermissions;
use Common\Billing\BillingPlan;
use Common\Billing\Gateways\Contracts\GatewayInterface;
use Common\Billing\Gateways\GatewayFactory;
use Arr;
use Str;

class CrupdateBillingPlan
{
    use SyncsPermissions;

    /**
     * @var GatewayFactory
     */
    private $factory;

    /**
     * @param GatewayFactory $factory
     */
    public function __construct(GatewayFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param array $data
     * @param BillingPlan|null $originalPlan
     * @return BillingPlan
     */
    public function execute($data, BillingPlan $originalPlan = null)
    {
        $plan = $originalPlan ? $originalPlan : app(BillingPlan::class)->newModelInstance(['uuid' => Str::random(36)]);

        if ($parentId = Arr::get($data, 'parent_id')) {
            $parent = app(BillingPlan::class)->find($parentId);
        }

        $newData = [
            'name' => $data['name'],
            'parent_id' => $data['parent_id'],
            'hidden' => $data['hidden'] ?: false,
            'amount' => $data['amount'],
            'interval_count' => $data['interval_count'],
            'interval' => $data['interval'],
            'currency' => isset($parent) ? $parent->currency : $data['currency'],
            'currency_symbol' => isset($parent) ? $parent->currency_symbol : $data['currency_symbol'],
        ];

        if ( ! isset($parent)) {
            $newData = array_merge($newData, [
                'available_space' => Arr::get($data, 'available_space') ?: null,
                'features' => $data['features'],
                'free' => $data['free'],
                'position' => $data['position'],
                'recommended' => $data['recommended'],
                'show_permissions' => $data['show_permissions'],
            ]);
        }

        $plan = $plan->fill($newData);
        $plan->save();

        if ($permissions = Arr::get($data, 'permissions')) {
            $this->syncPermissions($plan, $permissions);
        }

        if ( ! $plan->free && ! $originalPlan) {
            $this->factory->getEnabledGateways()->each(function(GatewayInterface $gateway) use($plan) {
                $gateway->plans()->create($plan);
            });
        }

        return $plan;
    }
}
