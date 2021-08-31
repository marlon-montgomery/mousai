import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ArtistsLinksListComponent} from './artists-links-list.component';
import {RouterModule} from '@angular/router';


@NgModule({
    declarations: [
       ArtistsLinksListComponent,
    ],
    imports: [
        CommonModule,
        RouterModule,
    ],
    exports: [
        ArtistsLinksListComponent,
    ]
})
export class ArtistsLinksListModule {
}
