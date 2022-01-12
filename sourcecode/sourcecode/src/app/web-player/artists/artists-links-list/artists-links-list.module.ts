import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
import {DiamondComponent} from './diamond/diamond.component';
import {MarqueeComponent} from '../../marquee/marquee.component';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';
import {DiamondModalComponent} from './diamond-modal/diamond-modal.component';
import {MatButtonModule} from '@angular/material/button';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {MatDialogModule} from '@angular/material/dialog';
import {ArtistsLinksListComponent} from './artists-links-list.component';
import {RouterModule} from '@angular/router';


@NgModule({
    declarations: [
        DiamondComponent,
        MarqueeComponent,
        DiamondModalComponent,
        ArtistsLinksListComponent,
    ],
    imports: [
        CommonModule,
        RouterModule,
        MatIconModule,
        MatProgressSpinnerModule,
        MatButtonModule,
        MatProgressBarModule,
        MatDialogModule,
    ],
    exports: [
        ArtistsLinksListComponent,
    ]
})
export class ArtistsLinksListModule {
}
