import {NgModule} from '@angular/core';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {MatDialogModule} from '@angular/material/dialog';
import {CsvExportInfoDialogComponent} from './csv-export-info-dialog.component';
import {TranslationsModule} from '../../core/translations/translations.module';

@NgModule({
    declarations: [CsvExportInfoDialogComponent],
    imports: [
        TranslationsModule,

        MatIconModule,
        MatButtonModule,
        MatDialogModule,
    ],
    exports: [CsvExportInfoDialogComponent],
})
export class CsvExportInfoDialogModule {}
