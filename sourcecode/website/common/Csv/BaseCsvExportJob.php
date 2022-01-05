<?php

namespace Common\Csv;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Str;

abstract class BaseCsvExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var resource
     */
    private $csvStream;

    /**
     * @var array
     */
    protected $headerKeys;

    abstract protected function generateLines();
    abstract public function cacheName(): string;

    public function handle()
    {
        $this->csvStream = fopen('php://temp', 'w');
        $cacheName = $this->cacheName();

        CsvExport::where('cache_name', $cacheName)->delete();

        $this->generateLines();

        $csvExport = CsvExport::create([
            'cache_name' => $cacheName,
            'user_id' => $this->requesterId ?? null,
            'download_name' => "$cacheName.csv",
            'uuid' => Str::uuid(),
        ]);
        $csvExport->storeFile($this->csvStream);
        fclose($this->csvStream);

        $this->sendNotification($csvExport);
    }

    protected function writeLineToCsv(array $data)
    {
        if (!$this->headerKeys) {
            $this->buildCsvHeader($data);
        }

        $values = array_map(function ($value) {
            if ($value instanceof Carbon) {
                return $value->created_at->format('Y-m-d');
            }
            return $value;
        }, array_values($data));

        fputcsv($this->csvStream, $values);
    }

    protected function buildCsvHeader(array $lineData)
    {
        $this->headerKeys = array_map(function ($column) {
            return Str::title(str_replace('_', ' ', $column));
        }, array_keys($lineData));

        fputcsv($this->csvStream, $this->headerKeys);
    }

    protected function notificationName(): string
    {
        return $this->cacheName();
    }

    protected function sendNotification(CsvExport $export)
    {
        if (!$this->requesterId) {
            return;
        }

        User::find($this->requesterId)->notify(
            new CsvExportReadyNotif($export, $this->notificationName()),
        );
    }
}
