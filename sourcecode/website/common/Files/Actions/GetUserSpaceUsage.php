<?php namespace Common\Files\Actions;

use App\User;
use Auth;
use Common\Billing\BillingPlan;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class GetUserSpaceUsage
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var User
     */
    protected $user;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->user = Auth::user();
    }

    public function execute(User $user = null): array
    {
        $this->user = $user ?? Auth::user();
        return [
            'used' => $this->getSpaceUsed(),
            'available' => $this->getAvailableSpace(),
        ];
    }

    private function getSpaceUsed(): int
    {
        return (int) $this->user
            ->entries(['owner' => true])
            ->where('type', '!=', 'folder')
            ->withTrashed()
            ->sum('file_size');
    }

    public function getAvailableSpace(): ?int
    {
        $space = null;

        if (!is_null($this->user->available_space)) {
            $space = $this->user->available_space;
        } elseif (app(Settings::class)->get('billing.enable')) {
            if ($this->user->subscribed()) {
                $space = $this->user->subscriptions->first()->mainPlan()
                    ->available_space;
            } elseif ($freePlan = BillingPlan::where('free', true)->first()) {
                $space = $freePlan->available_space;
            }
        }

        // space is not set at all on user or billing plans
        if (is_null($space)) {
            $defaultSpace = $this->settings->get('uploads.available_space');
            return is_numeric($defaultSpace) ? abs($defaultSpace) : null;
        } else {
            return abs($space);
        }
    }

    /**
     * Return if user has used up his disk space.
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function userIsOutOfSpace(UploadedFile $file)
    {
        $availableSpace = $this->getAvailableSpace();

        // unlimited space
        if (is_null($availableSpace)) {
            return false;
        }
        return $this->getSpaceUsed() + $file->getSize() > $availableSpace;
    }
}
