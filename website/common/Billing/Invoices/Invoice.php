<?php

namespace Common\Billing\Invoices;

use Common\Billing\Subscription;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Invoice
 *
 * @property-read Subscription $subscription
 * @mixin Eloquent
 * @property int $id
 * @property int $subscription_id
 * @property int $paid
 * @property string $uuid
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice query()
 */
class Invoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'subscription_id' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}