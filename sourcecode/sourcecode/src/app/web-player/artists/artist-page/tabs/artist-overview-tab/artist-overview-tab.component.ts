import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {ArtistPageService} from '../../artist-page.service';
import {BehaviorSubject} from 'rxjs';
import {Settings} from '@common/core/config/settings.service';
import {Album} from '../../../../../models/Album';
import {WebPlayerState} from '../../../../web-player-state.service';
import {WebPlayerUrls} from '../../../../web-player-urls.service';
import {finalize} from 'rxjs/operators';
import {Artists} from '../../../artists.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {LocalStorage} from '@common/core/services/local-storage.service';

@Component({
    selector: 'artist-overview-tab',
    templateUrl: './artist-overview-tab.component.html',
    styleUrls: ['./artist-overview-tab.component.scss'],
})
export class ArtistOverviewTabComponent extends InfiniteScroll implements OnInit, OnDestroy {
    public albumsLayout$ = new BehaviorSubject<'list' | 'grid'>(
        this.localStorage.get('artistPage.albumLayout') || this.settings.get('player.default_artist_view')
    );
    public loading$ = new BehaviorSubject<boolean>(false);

    constructor(
        public artistPage: ArtistPageService,
        public settings: Settings,
        public state: WebPlayerState,
        public urls: WebPlayerUrls,
        private artists: Artists,
        private localStorage: LocalStorage,
        protected zone: NgZone,
    ) {
        super();
    }

    ngOnInit() {
        this.el = this.state.scrollContainer;
        super.ngOnInit();
    }

    ngOnDestroy() {
        super.ngOnDestroy();
    }

    public setAlbumLayout(value: 'list'|'grid') {
        this.albumsLayout$.next(value);
        this.localStorage.set('artistPage.albumLayout', value);
    }

    public canLoadMore() {
        const albums = this.artistPage.albums$.value;
        return this.artistPage.activeTab$.value === 'discography' && albums.current_page < albums.last_page;
    }

    protected isLoading() {
        return this.loading$.value;
    }

    protected loadMoreItems() {
        const albums = this.artistPage.albums$.value;
        this.loading$.next(true);
        this.artists.paginateAlbums(this.artistPage.artist$.value.id, albums.current_page + 1)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.artistPage.albums$.next({
                    ...response.pagination,
                    data: [...albums.data, ...response.pagination.data],
                });
            });
    }

    public albumTrackByFn = (i: number, album: Album) => album.id;
}
