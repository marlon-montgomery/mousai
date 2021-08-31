import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ArtistPageService} from '../../artist-page.service';
import {Settings} from '@common/core/config/settings.service';
import {ImageGalleryOverlayComponent} from '@common/shared/image-gallery-overlay/image-gallery-overlay.component';
import {OverlayPanel} from '@common/core/ui/overlay-panel/overlay-panel.service';

@Component({
    selector: 'artist-bio-tab',
    templateUrl: './artist-bio-tab.component.html',
    styleUrls: ['./artist-bio-tab.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ArtistBioTabComponent {
    constructor(
        public artistPage: ArtistPageService,
        public settings: Settings,
        private overlay: OverlayPanel,
    ) {}

    public openImageGallery(activeIndex: number) {
        this.overlay.open(ImageGalleryOverlayComponent, {
            origin: 'global',
            position: 'center',
            panelClass: 'image-gallery-overlay-container',
            backdropClass: 'image-gallery-overlay-backdrop',
            hasBackdrop: true,
            data: {
                images: this.artistPage.fullSizeProfileImages$.value,
                activeIndex,
            }
        });
    }
}
