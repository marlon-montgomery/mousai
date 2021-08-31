<?php

namespace Database\Seeders;

use Common\Auth\Permissions\Permission;
use Common\Billing\BillingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BillingPlanSeeder extends Seeder
{
    /**
     * @var BillingPlan
     */
    private $plan;

    /**
     * @param BillingPlan $plan
     */
    public function __construct(BillingPlan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->plan->count() === 0 && config('common.site.demo')) {
            $this->createPlan([
                'name' => 'Basic',
                'minutes' => 180,
                'free' => true,
                'position' => 1,
                'features' => [
                    '3h upload time',
                    'Ad supported experience',
                    'Listen on browser, phone, tablet or TV',
                    'Stream unlimited music',
                    'Cancel anytime',
                ]
            ]);

            $this->createPlan([
                'name' => 'Pro Unlimited',
                'minutes' => null,
                'amount' => 8,
                'position' => 2,
                'recommended' => true,
                'features' => [
                    'Unlimited upload time',
                    'No advertisements',
                    'Download songs',
                    'Pro badge',
                    'Listen on browser, phone and tablet or TV',
                    'Stream unlimited amount of music',
                    'Cancel anytime',
                ]
            ]);
        }
    }

    private function createPlan($params)
    {
        $permissions = app(Permission::class)->pluck('id', 'name');
        $free = Arr::get($params, 'free', false);

        $basic = $this->plan->create([
            'name' => $params['name'],
            'uuid' => Str::random(36),
            'amount' => Arr::get($params, 'amount'),
            'free' => $free,
            'currency' => 'USD',
            'currency_symbol' => '$',
            'interval' => 'month',
            'interval_count' => 1,
            'position' => $params['position'],
            'recommended' => Arr::get($params, 'recommended', false),
            'features' => $params['features'],
        ]);

        $newPermissions = [$permissions['files.create']];

        $minutes = Arr::get($params, 'minutes');
        $newPermissions[$permissions['tracks.create']] = [
            'restrictions' => json_encode([['name' => 'minutes', 'value' => $minutes]])
        ];

        $basic->permissions()->sync($newPermissions);

        if ( ! $free) {
            $this->plan->create([
                'name' => "6 Month Subscription",
                'uuid' => Str::random(36),
                'parent_id' => $basic->id,
                'interval' => 'month',
                'interval_count' => 6,
                'amount' => ($params['amount'] * 6) * ((100 - 10) / 100), // 6 months - 10%
                'currency' => 'USD',
                'currency_symbol' => '$',
            ]);

            $this->plan->create([
                'name' => "1 Year Subscription",
                'uuid' => Str::random(36),
                'parent_id' => $basic->id,
                'interval' => 'month',
                'interval_count' => 12,
                'amount' => ($params['amount'] * 12) * ((100 - 20) / 100), // 12 months - 20%,
                'currency' => 'USD',
                'currency_symbol' => '$',
            ]);
        }
    }
}
