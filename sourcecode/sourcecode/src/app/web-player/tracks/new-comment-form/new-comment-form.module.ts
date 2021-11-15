import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {NewCommentFormComponent} from './new-comment-form.component';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MediaImageModule} from '../../shared/media-image/media-image.module';
import {TranslationsModule} from '@common/core/translations/translations.module';


@NgModule({
    declarations: [
        NewCommentFormComponent,
    ],
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        MediaImageModule,
        TranslationsModule,
    ],
    exports: [
        NewCommentFormComponent,
    ]
})
export class NewCommentFormModule {
}
