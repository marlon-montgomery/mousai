import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {downloadFileFromUrl} from '@common/uploads/utils/download-file-from-url';
import {CsvExportInfoDialogComponent} from '@common/csv/csv-info-dialog/csv-export-info-dialog.component';

export interface CsvExportResponse {
    downloadPath?: string;
    result?: 'jobQueued';
}

@Injectable({
    providedIn: 'root',
})
export class CsvExporterService {
    constructor(private http: AppHttpClient, private modal: Modal) {}

    export(url: string, params: object = {}) {
        return this.http.post(url, params).subscribe((response: CsvExportResponse) => {
          if (response.downloadPath) {
            downloadFileFromUrl(response.downloadPath);
          } else {
            this.modal.open(CsvExportInfoDialogComponent);
          }
      });
    }
}
