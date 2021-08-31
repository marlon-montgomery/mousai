<?php namespace Common\Database\Seeds;

use Common\Pages\CustomPage;
use Illuminate\Database\Seeder;

class DefaultPagesSeeder extends Seeder
{
    /**
     * @var CustomPage
     */
    private $page;

    /**
     * @param CustomPage $page
     */
    public function __construct(CustomPage $page)
    {
        $this->page = $page;
    }

    /**
     * @return void
     */
    public function run()
    {
        $path = app('path.common') . '/resources/lorem.html';
        $lorem = file_get_contents($path);

        $this->page->firstOrCreate([
            'slug' => 'privacy-policy',
        ], [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'body' => '<h1>Example Privacy Policy</h1>' . $lorem,
            'type' => 'default',
        ]);

        $this->page->firstOrCreate([
            'slug' => 'terms-of-service',
        ], [
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'body' => '<h1>Example Terms of Service</h1>' . $lorem,
            'type' => 'default',
        ]);

        $this->page->firstOrCreate([
            'slug' => 'about-us',
        ], [
            'title' => 'About Us',
            'slug' => 'about-us',
            'body' => '<h1>Example About Us</h1>' . $lorem,
            'type' => 'default',
        ]);
    }
}
