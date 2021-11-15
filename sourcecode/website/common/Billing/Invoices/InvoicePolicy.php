<?php

namespace Common\Billing\Invoices;

use App\User;
use Common\Core\Policies\BasePolicy;

class InvoicePolicy extends BasePolicy
{
    public function index(User $user, $userId = null)
    {
        return $user->hasPermission('invoices.view') || $user->id === (int) $userId;
    }

    public function show(User $user, Invoice $invoice)
    {
        return $user->hasPermission('invoices.view') || $invoice->subscription->user_id == $user->id;
    }
}