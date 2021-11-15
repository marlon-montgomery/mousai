<?php namespace Common\Billing;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait Billable
 * @property-read Collection|Subscription[] $subscriptions
 */
trait Billable
{
    public function subscribe($gateway, $gatewayId, BillingPlan $plan)
    {
        if ($plan->interval === 'year') {
            $renewsAt = Carbon::now()->addYears($plan->interval_count);
        } else if ($plan->interval === 'week') {
            $renewsAt = Carbon::now()->addWeeks($plan->interval_count);
        } else {
            $renewsAt = Carbon::now()->addMonths($plan->interval_count);
        }

        $this->subscriptions()->create([
            'plan_id' => $plan->id,
            'ends_at' => null,
            'renews_at' => $renewsAt,
            'gateway' => $gateway,
            'gateway_id' => $gatewayId,
        ]);

        $this->load('subscriptions');
    }

    /**
     * Determine if user is subscribed.
     *
     * @return bool
     */
    public function subscribed()
    {
        $subscription = $this->subscriptions->first(function(Subscription $sub) {
            return $sub->valid();
        });

        return ! is_null($subscription);
    }

    /**
     * Check if user is subscribed to specified plan and gateway.
     */
    public function subscribedTo(BillingPlan $plan, string $gateway): bool {
        return ! is_null($this->subscriptions->first(function(Subscription $sub) use($plan, $gateway) {
            return $sub->valid && $sub->plan_id === $plan->id && $sub->gateway_name === $gateway;
        }));
    }

    /**
     * @return HasMany
     */
    public function subscriptions()
    {
        // always return subscriptions that are not attached to any gateway last
        return $this->hasMany(Subscription::class, 'user_id')->orderBy(DB::raw('FIELD(gateway_name, "none")'), 'asc');
    }
}
