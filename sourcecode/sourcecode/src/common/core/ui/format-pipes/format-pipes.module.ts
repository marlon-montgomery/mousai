import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormattedDatePipe} from '@common/core/ui/format-pipes/formatted-date.pipe';
import {FormattedFileSizePipe} from '@common/uploads/formatted-file-size.pipe';
import {FaviconPipe} from './favicon.pipe';
import {TitleCasePipe} from './title-case.pipe';
import {SnakeCasePipe} from '@common/core/ui/format-pipes/snake-case.pipe';
import {BackgroundUrlPipe} from '@common/core/ui/format-pipes/background-url.pipe';

@NgModule({
    declarations: [
        FormattedDatePipe,
        FormattedFileSizePipe,
        FaviconPipe,
        TitleCasePipe,
        SnakeCasePipe,
        BackgroundUrlPipe,
    ],
    imports: [CommonModule],
    exports: [
        FormattedDatePipe,
        FormattedFileSizePipe,
        FaviconPipe,
        TitleCasePipe,
        SnakeCasePipe,
        BackgroundUrlPipe,
    ],
    providers: [
        BackgroundUrlPipe,
    ]
})
export class FormatPipesModule {}
