<?php namespace Common\Files\Controllers;

use Common\Core\BaseController;
use Common\Settings\Setting;
use Common\Settings\Settings;
use File;
use Illuminate\Http\Request;
use Image;

class UploadFaviconController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Need to store favicon in "client" sub-dir because
     * angular cli prepends paths in manifest.json with "client"
     */
    const FAVICON_DIR = 'client/favicon';

    private $sizes = [
        [72, 72],
        [96, 96],
        [128, 128],
        [144, 144],
        [152, 152],
        [192, 192],
        [384, 384],
        [512, 512],
    ];

    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    public function store()
    {
        $this->authorize('update', Setting::class);

        $this->validate($this->request, [
            'file' => 'required|file'
        ]);

        if ( ! File::exists($this->absoluteFaviconDir())) {
            File::makeDirectory($this->absoluteFaviconDir());
        }

        foreach ($this->sizes as $size) {
           $this->saveFaviconForSize($size);
        }
        $this->saveFaviconForSize([16, 16], public_path(), 'favicon.ico');

        $uri = self::FAVICON_DIR . '/icon-144x144.png';
        $this->settings->save(['branding.favicon' => $uri]);

        // need to set url as file entry for appearance
        // image input to preview image properly
        return $this->success(['fileEntry' => ['url' => $uri]]);
    }

    private function saveFaviconForSize(array $size, string $dir = null, string $name = null)
    {
        $img = Image::make($this->request->file('file'));

        $img->fit($size[0], $size[1]);

        $img->encode('png');

        if ( ! $dir) {
            $dir = $this->absoluteFaviconDir();
        }

        if ( ! $name) {
            $name = "icon-$size[0]x$size[1].png";
        }

        File::put("$dir/$name", $img);
    }

    private function absoluteFaviconDir(): string
    {
        return public_path(self::FAVICON_DIR);
    }
}
