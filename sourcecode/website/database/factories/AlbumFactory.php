<?php

namespace Database\Factories;

use App\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Album::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(rand(2, 5), true),
            'release_date' => $this->faker->dateTimeBetween('-4 months', 'now')->format('Y-m-d'),
            'image' => $this->faker->imageUrl(240, 240),
            'created_at' => $this->faker->dateTimeBetween('-4 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-4 months', 'now'),
            'plays' => $this->faker->numberBetween(865, 596545),
        ];
    }
}
