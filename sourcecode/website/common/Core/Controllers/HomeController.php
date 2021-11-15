<?php namespace Common\Core\Controllers;

use Common\Core\AppUrl;
use Common\Core\BaseController;
use Common\Core\Bootstrap\BootstrapData;
use Common\Settings\Settings;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends BaseController {

    /**
     * @var BootstrapData
     */
    private $bootstrapData;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(BootstrapData $bootstrapData, Settings $settings)
    {
        $this->bootstrapData = $bootstrapData;
        $this->settings = $settings;
    }

	public function show()
	{
	    // only get meta tags if we're actually
        // rendering homepage and not a fallback route
        $data = [];
	    if (request()->path() === '/' && $response = $this->handleSeo($data)) {
            return $response;
        }

	    $view = view('app')
            ->with('bootstrapData', $this->bootstrapData->init())
            ->with('htmlBaseUri', app(AppUrl::class)->htmlBaseUri)
            ->with('settings', $this->settings)
            ->with('customHtmlPath', public_path('storage/custom-code/custom-html.html'))
            ->with('customCssPath', public_path('storage/custom-code/custom-styles.css'));

	    if (isset($data['seo'])) {
	        $view->with('meta', $data['seo']);
        }

        return response($view);
	}
}
