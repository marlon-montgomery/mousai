<?php

namespace Common\Csv;

use Auth;
use Common\Auth\Jobs\ExportUsersCsv;

class CommonCsvExportController extends BaseCsvExportController
{
    public function exportUsers()
    {
        return $this->exportUsing(new ExportUsersCsv(Auth::id()));
    }
}
