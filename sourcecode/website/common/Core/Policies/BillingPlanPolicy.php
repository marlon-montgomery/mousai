<?php

namespace Common\Core\Policies;

use App\User;
use Common\Settings\Settings;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillingPlanPolicy
{
    use HandlesAuthorization;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function index(User $user)
    {
        return $this->settings->get('billing.enable') || $user->hasPermission('plans.view');
    }

    public function show(User $user)
    {
        return $this->settings->get('billing.enable') || $user->hasPermission('plans.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('plans.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('plans.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('plans.delete');
    }
}
