<?php

namespace Common\Files\Chunks;

use Common\Core\BaseController;
use Common\Files\FileEntry;
use Common\Settings\Settings;
use File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChunkedUploadsController extends BaseController
{
    use HandlesUploadChunks;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    /**
     * @return JsonResponse
     */
    public function load()
    {
        list($fingerprint, $totalChunks, $chunkIndex, $originalName) = $this->validateUploadParams();

        $response = app(GetAlreadyUploadedChunks::class)->execute(
            $fingerprint,
            $totalChunks,
            $originalName,
            $this->getMetadata()
        );

        $chunkDir = $this->chunkDir($fingerprint);
        if ( ! isset($response['fileEntry']) && ! File::exists($chunkDir)) {
            File::makeDirectory($chunkDir);
        }

        return $this->success($response);
    }

    /**
     * @return JsonResponse
     */
    public function storeChunk()
    {
        $this->authorize('store', FileEntry::class);

        list($fingerprint, $totalChunks, $chunkIndex, $originalName) = $this->validateUploadParams();

        app(StoreChunkOnDisk::class)->execute(
            $fingerprint,
            $chunkIndex,
            $this->request->file('file')
        );

        // TODO: check here if sum of all uploaded chunks equals total file size
        // or even checksum, instead of checking if it's last chunk
        if ($totalChunks === ($chunkIndex + 1)) {
            $response = app(GetAlreadyUploadedChunks::class)->execute(
                $fingerprint,
                $totalChunks,
                $originalName,
                $this->getMetadata()
            );
        }

        return $this->success($response ?? []);
    }

    protected function validateUploadParams(): array
    {
        $params = [
            'fingerPrint' => $this->request->header('Be-Fingerprint') ?? $this->request->get('_fingerprint'),
            'totalChunks' => (int) ($this->request->header('Be-Chunk-Count') ?? $this->request->get('_chunkCount')),
            'chunkIndex' => (int) ($this->request->header('Be-Chunk-Index') ?? $this->request->get('_chunkNumber')),
            'originalName' => $this->request->header('Be-Original-Filename') ?? $this->request->get('_originalFileName')
        ];

        return array_values($this->getValidationFactory()->make($params, [
            //'file' => 'required',
            'fingerPrint' => 'required|string',
            'totalChunks' => 'required|int',
            'chunkIndex' => 'required|int',
            'originalName' => 'required|string',
        ])->validate());
    }

    protected function getMetadata(): array
    {
        $metadata = $this->request->except('file');
        if ($header = $this->request->header('Be-Metadata')) {
            foreach (explode(',', $header) as $part) {
                $parts = explode(' ', $part);
                $metadata[$parts[0]] = isset($parts[1]) ? base64_decode($parts[1]) : null;
            }
        }

        return $metadata;
    }
}
