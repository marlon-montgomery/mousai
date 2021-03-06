import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormattedDatePipe} from '@common/core/ui/format-pipes/formatted-date.pipe';
import {FormattedFileSizePipe} from '@common/uploads/formatted-file-size.pipe';
import { FaviconPipe } from './favicon.pipe';
import { TitleCasePipe } from './title-case.pipe';


@NgModule({
    declarations: [
        FormattedDatePipe,
        FormattedFileSizePipe,
        FaviconPipe,
        TitleCasePipe,
    ],
    imports: [
        CommonModule
    ],
    exports: [
        FormattedDatePipe,
        FormattedFileSizePipe,
        FaviconPipe,
        TitleCasePipe,
    ]
})
export class FormatPipesModule {
}
