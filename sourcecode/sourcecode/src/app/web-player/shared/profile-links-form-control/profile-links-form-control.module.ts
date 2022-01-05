import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProfileLinksFormControlComponent} from './profile-links-form-control.component';
import {ReactiveFormsModule} from '@angular/forms';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {TranslationsModule} from '@common/core/translations/translations.module';


@NgModule({
    declarations: [
        ProfileLinksFormControlComponent,
    ],
    imports: [
        CommonModule,
        ReactiveFormsModule,
        TranslationsModule,

        MatIconModule,
        MatButtonModule,
    ],
    exports: [
        ProfileLinksFormControlComponent,
    ]
})
export class ProfileLinksFormControlModule {
}
