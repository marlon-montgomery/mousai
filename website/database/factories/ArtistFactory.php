<?php

namespace Database\Factories;

use App\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Artist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $createdAt = $this->faker->dateTimeBetween('-4 months', 'now');
        return [
            'name' => $this->faker->words(rand(2, 5), true),
            'image_small' => $this->faker->imageUrl(240, 240),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'plays' => $this->faker->numberBetween(865, 5965456),
            'views' => $this->faker->numberBetween(0, 549454),
            'verified' => true,
        ];
    }
}
