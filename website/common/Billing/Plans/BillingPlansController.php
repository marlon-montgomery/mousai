<?php namespace Common\Billing\Plans;

use Common\Billing\BillingPlan;
use Common\Billing\GatewayException;
use Common\Billing\Gateways\Contracts\GatewayInterface;
use Common\Billing\Gateways\GatewayFactory;
use Common\Billing\Plans\Actions\CrupdateBillingPlan;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\Request;

class BillingPlansController extends BaseController
{
    /**
     * @var BillingPlan
     */
    private $plan;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var GatewayFactory
     */
    private $factory;

    public function __construct(
        BillingPlan $plan,
        Request $request,
        GatewayFactory $factory
    ) {
        $this->plan = $plan;
        $this->request = $request;
        $this->factory = $factory;
    }

    public function index()
    {
        $this->authorize('index', BillingPlan::class);

        $dataSource = new MysqlDataSource(
            $this->plan->with(['parent', 'permissions']),
            $this->request->all(),
        );

        return $this->success(['pagination' => $dataSource->paginate()]);
    }

    public function store()
    {
        $this->authorize('store', BillingPlan::class);

        $this->validate($this->request, [
            'name' => 'required|string|max:250',
            'currency' => 'required_unless:free,1|string|max:255',
            'interval' => 'required_unless:free,1|string|max:255',
            'amount' => 'required_unless:free,true|min:0',
            'permissions' => 'array',
            'show_permissions' => 'required|boolean',
            'recommended' => 'required|boolean',
            'position' => 'required|integer',
            'available_space' => 'nullable|integer|min:1',
        ]);

        $plan = app(CrupdateBillingPlan::class)->execute($this->request->all());

        return $this->success(['plan' => $plan]);
    }

    public function update(BillingPlan $billingPlan)
    {
        $this->authorize('update', $billingPlan);

        $this->validate($this->request, [
            'name' => 'required|string|max:250',
            'currency' => 'string|max:255',
            'interval' => 'string|max:255',
            'amount' => 'min:0',
            'permissions' => 'array',
            'show_permissions' => 'boolean',
            'recommended' => 'boolean',
            'parent_id' => "nullable|integer|notIn:$billingPlan->id",
        ]);

        $plan = app(CrupdateBillingPlan::class)->execute(
            $this->request->except('parent'),
            $billingPlan,
        );

        return $this->success(['plan' => $plan]);
    }

    public function destroy(string $ids)
    {
        $planIds = explode(',', $ids);
        $this->authorize('destroy', [BillingPlan::class, $planIds]);

        $plans = $this->plan
            ->withCount('subscriptions')
            ->whereIn('id', $planIds)
            ->get();
        $couldNotDelete = [];

        foreach ($plans as $plan) {
            if ($plan->subscriptions_count) {
                $couldNotDelete[] = $plan->name;
                continue;
            }

            $plan->delete();

            $this->factory
                ->getEnabledGateways()
                ->each(function (GatewayInterface $gateway) use ($plan) {
                    $gateway->plans()->delete($plan);
                });
        }

        if (!empty($couldNotDelete)) {
            $couldNotDelete = implode(', ', $couldNotDelete);
            return $this->error(
                __(
                    "Could not delete plans: ':planNames', because they have active subscriptions.",
                    ['planNames' => $couldNotDelete],
                ),
            );
        } else {
            return $this->success();
        }
    }

    /**
     * Sync billing plans across all enabled payment gateways.
     */
    public function sync()
    {
        @ini_set('max_execution_time', 300);

        $plans = $this->plan
            ->where('free', false)
            ->orderBy('parent_id', 'asc')
            ->get();

        foreach ($this->factory->getEnabledGateways() as $gateway) {
            foreach ($plans as $plan) {
                if ($gateway->plans()->find($plan)) {
                    continue;
                }
                try {
                    $gateway->plans()->create($plan);
                } catch (GatewayException $e) {
                    return $this->error(
                        "Could not sync \"$plan->name\" plan: {$e->getMessage()}",
                    );
                }
            }
        }

        return $this->success();
    }
}
