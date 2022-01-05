import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProfileDescriptionComponent} from './profile-description.component';


@NgModule({
    declarations: [
        ProfileDescriptionComponent,
    ],
    exports: [
        ProfileDescriptionComponent,
    ],
    imports: [
        CommonModule,
    ]
})
export class ProfileDescriptionModule {
}
