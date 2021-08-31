import {ChangeDetectionStrategy, Component, HostBinding} from '@angular/core';
import {ArtistPageService} from '../artist-page.service';
import {WebPlayerUrls} from '../../../web-player-urls.service';
import {Player} from '../../../player/player.service';
import {WebPlayerState} from '../../../web-player-state.service';
import {Settings} from '@common/core/config/settings.service';
import {UserLibrary} from '../../../users/user-library/user-library.service';
import {ArtistContextMenuComponent} from '../../artist-context-menu/artist-context-menu.component';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {ARTIST_PAGE_TABS} from '../artist-page-tabs';
import {getFaviconFromUrl} from '@common/core/utils/get-favicon-from-url';

@Component({
    selector: 'artist-page-header',
    templateUrl: './artist-page-header.component.html',
    styleUrls: ['./artist-page-header.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ArtistPageHeaderComponent {
    @HostBinding('class.media-page-header') mediaPageHeader = true;
    public allTabs = ARTIST_PAGE_TABS;
    favicon = getFaviconFromUrl;
    constructor(
        public urls: WebPlayerUrls,
        public player: Player,
        public state: WebPlayerState,
        public settings: Settings,
        public library: UserLibrary,
        public artistPage: ArtistPageService,
        private contextMenu: ContextMenu,
    ) {}

    public toggleLike() {
        this.artistPage.addingToLibrary$.next(true);
        const artist = this.artistPage.artist$.value;
        const promise = this.library.has(artist) ?
            this.library.remove([artist]) :
            this.library.add([artist]);
        promise.then(() => this.artistPage.addingToLibrary$.next(false));
    }

    public showArtistContextMenu(e: MouseEvent) {
        e.stopPropagation();

        this.contextMenu.open(
            ArtistContextMenuComponent,
            e.target,
            {data: {item: this.artistPage.artist$.value, type: 'artist'}, originX: 'center', overlayX: 'center'}
        );
    }
}
