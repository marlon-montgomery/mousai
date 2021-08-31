<?php namespace Common\Files\Requests;

use Common\Files\Actions\GetUserSpaceUsage;
use Illuminate\Validation\Validator;
use Common\Core\BaseFormRequest;
use Common\Settings\Settings;

class UploadFileRequest extends BaseFormRequest
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var GetUserSpaceUsage
     */
    private $spaceUsage;

    /**
     * @param Settings $settings
     * @param GetUserSpaceUsage $spaceUsage
     */
    public function __construct(Settings $settings, GetUserSpaceUsage $spaceUsage)
    {
        $this->settings = $settings;
        $this->spaceUsage = $spaceUsage;
        parent::__construct();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'file' => 'required|file',
            'parentId' => 'nullable|integer|exists:file_entries,id',
            'relativePath' => 'nullable|string|max:255',
        ];

        // validate by allowed extension setting
        if ($allowed = $this->settings->getJson('uploads.allowed_extensions')) {
            $rules['file'] .= '|mimes:' . implode(',', $allowed);
        }

        // validate by max file size setting
        if ($maxSize = (int) $this->settings->get('uploads.max_size')) {
            // size is stored in megabytes, laravel expects kilobytes
            $rules['file'] .= '|max:' . $maxSize * 1024;
        }

        return $rules;
    }

    /**
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $file = $this->file('file');

            $blocked = $this->settings->getJson('uploads.blocked_extensions', []);
            if (in_array($file->guessExtension(), $blocked)) {
                $validator->errors()->add('file', "Files of this type can't be uploaded.");
            }

            // check if user has enough space left to upload all files.
            if ($this->spaceUsage->userIsOutOfSpace($this->file('file'))) {
                $validator->errors()->add('file', __(
                    "You have exhausted your allowed space of :space. Delete some files or upgrade your plan.",
                    ['space' => $this->formatBytes($this->spaceUsage->getAvailableSpace())]));
            }
        });
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
