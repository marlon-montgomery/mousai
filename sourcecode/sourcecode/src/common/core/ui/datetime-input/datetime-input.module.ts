import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DatetimeInputComponent} from './datetime-input.component';
import {ReactiveFormsModule} from '@angular/forms';
import {TranslationsModule} from '@common/core/translations/translations.module';
import { MatButtonModule } from '@angular/material/button';

@NgModule({
    declarations: [DatetimeInputComponent],
    imports: [
        CommonModule,
        ReactiveFormsModule,
        TranslationsModule,

        MatButtonModule,
    ],
    exports: [
        DatetimeInputComponent,
    ]
})
export class DatetimeInputModule {
}
