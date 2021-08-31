<?php

namespace Common\Billing\Subscriptions;

use Carbon\Carbon;
use Common\Billing\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'plan_id' => 1,
            'gateway' => 'stripe',
            'renews_at' => Carbon::now()->addDays($this->faker->numberBetween(1, 10)),
        ];
    }
}
