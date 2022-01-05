import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ArtistPageService} from '../../artist-page.service';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'similar-artists-tab',
    templateUrl: './similar-artists-tab.component.html',
    styleUrls: ['./similar-artists-tab.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class SimilarArtistsTabComponent {
    constructor(
        public artistPage: ArtistPageService,
        public settings: Settings,
    ) {}
}
