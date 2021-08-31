<?php namespace Common\Billing\Gateways\Stripe;

use App\User;
use Common\Billing\GatewayException;
use Common\Billing\Gateways\Contracts\GatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Omnipay;
use Omnipay\Stripe\Gateway;

class StripeGateway implements GatewayInterface
{
    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var StripePlans
     */
    private $plans;

    /**
     * @var StripeSubscriptions
     */
    private $subscriptions;

    /**
     * StripeGateway constructor.
     */
    public function __construct()
    {
        $this->gateway = Omnipay::create('Stripe');

        $this->gateway->initialize(array(
            'apiKey' => config('services.stripe.secret'),
        ));

        $this->plans = new StripePlans($this->gateway);
        $this->subscriptions = new StripeSubscriptions($this->gateway);
    }

    public function plans()
    {
        return $this->plans;
    }

    public function subscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * Check if specified webhook is valid.
     *
     * @param Request $request
     * @return bool
     */
    public function webhookIsValid(Request $request)
    {
        return ! is_null($this->gateway->fetchEvent(
            ['eventReference' => $request->get('id')]
        )->send()->getEventReference());
    }

    /**
     * Add a new card to customer on stripe.
     *
     * @param User $user
     * @param string $token
     * @return User
     * @throws GatewayException
     * @throws InvalidCreditCardException
     */
    public function addCard(User $user, $token)
    {
        $params['token'] = $token;

        //create new stripe customer or attach to existing one
        if ($user->stripe_id) {
            $params['customerReference'] = $user->stripe_id;
        } else {
            $params['email'] = $user->email;
        }

        $request = $this->gateway->createCard($params);
        $response = $request->sendData(array_merge(
            $request->getData(),
            $user->stripe_id ? [] : ['email' => $user->email, 'name' => $user->display_name]
        ));

        if ( ! $response->isSuccessful()) {
            $data = $response->getData();

            // customer is missing on stripe when we have stripe id for user set locally
            // possibly because stripe mode was switched from test to live or vice versa
            if (Arr::get($data, 'error.code') === 'resource_missing' && Arr::get($data, 'error.param') === 'customer') {
                $user->fill(['stripe_id' => null])->save();
                return $this->addCard($user, $token);
            }

            //if card validation fails on stripe, throw exception so we can show message to user
            if (isset($data['error']['type']) && $data['error']['type'] === 'card_error') {
                throw new InvalidCreditCardException($data['error']['message']);
            }

            throw new GatewayException($response->getMessage());
        }

        //store stripe id on user model, if needed
        if ($user->stripe_id !== $stripeId = $response->getCustomerReference()) {
            $user->fill(['stripe_id' => $stripeId])->save();
        }

        //TODO: check if user has more then one card
        $this->setDefaultCustomerSource($user, $response->getCardReference());

        return $user;
    }

    /**
     * Change default customer payment source to specified card.
     *
     * @param User $user
     * @param string $cardReference
     * @return null|string
     * @throws GatewayException
     */
    public function setDefaultCustomerSource(User $user, $cardReference)
    {
        $response = $this->gateway->updateCustomer([
            'customerReference' => $user->stripe_id,
        ])->sendData(['default_source' => $cardReference, 'expand' => ['sources']]);

        // default source
        $cardData = Arr::first($response->getData()['sources']['data'], function($card) use($cardReference) {
            return $card['id'] === $cardReference;
        });

        if ( ! $response->isSuccessful()) {
            throw new GatewayException($response->getMessage());
        }

        $user->fill([
            'card_last_four' => $cardData['last4'],
            'card_brand'     => $cardData['brand'],
        ])->save();

        return $response->getCustomerReference();
    }
}
