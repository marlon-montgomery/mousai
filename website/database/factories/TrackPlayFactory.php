<?php

namespace Database\Factories;

use Agent;
use App\TrackPlay;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackPlayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrackPlay::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_at' => $this->faker->dateTimeBetween('-4 months', 'now'),
            'platform' => Arr::random(array_keys(Agent::getPlatforms())),
            'device' => Arr::random(['mobile', 'tablet', 'desktop']),
            'browser' => Arr::random(array_keys(Agent::getBrowsers())),
            'location' => $this->faker->countryCode,
        ];
    }
}
