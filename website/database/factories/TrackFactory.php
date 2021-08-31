<?php

namespace Database\Factories;

use App\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Track::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sampleNumber = rand(1,10);
        return [
            'name' => $this->faker->words(rand(2, 5), true),
            'number' => rand(1, 10),
            'duration' => 323000 + rand(1, 1000),
            'image' => $this->faker->imageUrl(240, 240),
            'url' => "storage/samples/{$sampleNumber}.mp3",
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'plays' => $this->faker->numberBetween(865, 596545),
            'description' => $this->faker->text(750) . "\n\n Visit: demo-url.com
Visit my bandcamp: demo.bandcamp.com
See me on instagram: www.instagram.com/demo
Read me on twitter: www.twitter.com/demo"
        ];
    }
}
