import {ChangeDetectionStrategy, Component} from '@angular/core';
import {MatDialogRef} from '@angular/material/dialog';

@Component({
    selector: 'csv-export-info-dialog',
    templateUrl: './csv-export-info-dialog.component.html',
    styleUrls: ['./csv-export-info-dialog.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CsvExportInfoDialogComponent {
    constructor(
        private dialogRef: MatDialogRef<CsvExportInfoDialogComponent>,
    ) {}

    public close() {
        this.dialogRef.close();
    }
}
