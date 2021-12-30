import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ArtistsLinksListComponent} from './artists-links-list.component';
import {RouterModule} from '@angular/router';
import {MatIconModule} from '@angular/material/icon';
import {MarqueeComponent} from '../../marquee/marquee.component';


@NgModule({
    declarations: [
        ArtistsLinksListComponent,
        MarqueeComponent,
    ],
    imports: [
        CommonModule,
        RouterModule,
        MatIconModule,
    ],
    exports: [
        ArtistsLinksListComponent,
    ]
})
export class ArtistsLinksListModule {
}
