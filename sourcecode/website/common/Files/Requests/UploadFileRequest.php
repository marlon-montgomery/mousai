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

    public function __construct(
        Settings $settings,
        GetUserSpaceUsage $spaceUsage
    ) {
        $this->settings = $settings;
        $this->spaceUsage = $spaceUsage;
        parent::__construct();
    }

    public function rules(): array
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
            // size is stored in bytes, laravel expects kilobytes
            $rules['file'] .= '|max:' . $maxSize / 1000;
        }

        return $rules;
    }

    public function messages(): array
    {
        $size = $this->settings->get('uploads.max_size');
        $formatted = $size ?  $this->formatBytes($size): 0;
        return [
            'file.max' => __('The file size may not be greater than :size', ['size' => $formatted])
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $file = $this->file('file');

            $blocked = $this->settings->getJson(
                'uploads.blocked_extensions',
                [],
            );
            if (in_array($file->guessExtension(), $blocked)) {
                $validator
                    ->errors()
                    ->add('file', "Files of this type can't be uploaded.");
            }

            // check if user has enough space left to upload all files.
            if ( ! $this->spaceUsage->hasEnoughSpaceToUpload($this->file('file')->getSize())) {
                $validator->errors()->add(
                    'file',
                    __(
                        'You have exhausted your allowed space of :space. Delete some files or upgrade your plan.',
                        [
                            'space' => self::formatBytes(
                                $this->spaceUsage->getAvailableSpace(),
                            ),
                        ],
                    ),
                );
            }
        });
    }

    public static function formatBytes($bytes, $unit = 'MB'): string
    {
        if ((!$unit && $bytes >= 1 << 30) || $unit == 'GB') {
            return number_format($bytes / (1 << 30), 1) . 'GB';
        }
        if ((!$unit && $bytes >= 1 << 20) || $unit == 'MB') {
            return number_format($bytes / (1 << 20), 1) . 'MB';
        }
        if ((!$unit && $bytes >= 1 << 10) || $unit == 'KB') {
            return number_format($bytes / (1 << 10), 1) . 'KB';
        }
        return number_format($bytes) . ' bytes';
    }
}
