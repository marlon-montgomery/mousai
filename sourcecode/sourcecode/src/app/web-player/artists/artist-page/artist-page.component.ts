import {
    ChangeDetectionStrategy,
    Component,
    NgZone,
    OnDestroy,
    OnInit,
    ViewEncapsulation
} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Subscription} from 'rxjs';
import {Player} from '../../player/player.service';
import {WebPlayerState} from '../../web-player-state.service';
import {Settings} from '@common/core/config/settings.service';
import {ArtistPageService} from './artist-page.service';
import {Artists} from '../artists.service';
import {ARTIST_PAGE_TABS} from './artist-page-tabs';

@Component({
    selector: 'artist-page',
    templateUrl: './artist-page.component.html',
    styleUrls: ['./artist-page.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ArtistPageComponent implements OnInit, OnDestroy {
    private subscriptions: Subscription[] = [];

    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        public player: Player,
        public state: WebPlayerState,
        public settings: Settings,
        public artistPage: ArtistPageService,
        protected zone: NgZone,
        private artists: Artists,
    ) {}

    ngOnInit() {
        const defaultTabId = this.settings.getJson('artistPage.tabs', [])[0]?.id || 1;
        this.route.data.subscribe(data => {
            this.artistPage.setArtist(data.api.artist, data.api.albums);
        });
        this.route.queryParams.subscribe(params => {
            this.artistPage.activeTab$.next(params.tab || ARTIST_PAGE_TABS[defaultTabId].queryParam);
        });
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    loadMoreArtists = (page: number) => {
        return this.artists.paginateTracks(this.artistPage.artist$.value.id, page);
    }

    loadMoreAlbums = (page: number) => {
        return this.artists.paginateAlbums(this.artistPage.artist$.value.id, page);
    }

    loadMoreFollowers = (page: number) => {
        return this.artists.paginateFollowers(this.artistPage.artist$.value.id, page);
    }
}
