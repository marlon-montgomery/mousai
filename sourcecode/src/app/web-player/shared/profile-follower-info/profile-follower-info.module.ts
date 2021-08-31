import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProfileFollowerInfoComponent} from './profile-follower-info.component';
import {TranslationsModule} from '@common/core/translations/translations.module';

@NgModule({
    declarations: [
        ProfileFollowerInfoComponent,
    ],
    exports: [
        ProfileFollowerInfoComponent,
    ],
    imports: [
        CommonModule,
        TranslationsModule,
    ]
})
export class ProfileFollowerInfoModule {
}
