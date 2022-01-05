import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {FormattedDuration} from '../../player/formatted-duration.service';
import {PlaylistContextMenuComponent} from '../playlist-context-menu/playlist-context-menu.component';
import {GetPlaylistResponse, Playlists} from '../playlists.service';
import {Track} from '../../../models/Track';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {CdkDragDrop, moveItemInArray} from '@angular/cdk/drag-drop';
import {WebPlayerState} from '../../web-player-state.service';
import {UserPlaylists} from '../user-playlists.service';
import {queueId} from '../../player/queue-id';
import {Playlist} from '../../../models/Playlist';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {Subscription} from 'rxjs';
import {DatatableService} from '@common/datatable/datatable.service';
import {CurrentUser} from '@common/auth/current-user';

@Component({
    selector: 'playlist',
    templateUrl: './playlist.component.html',
    styleUrls: ['./playlist.component.scss'],
    providers: [DatatableService],
})
export class PlaylistComponent extends InfiniteScroll implements OnInit, OnDestroy {
    public playlist: Playlist;
    public totalDuration: string;
    public following = false;
    private subscriptions: Subscription[] = [];
    public currentUserIsOwner = false;

    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private duration: FormattedDuration,
        private contextMenu: ContextMenu,
        private playlists: Playlists,
        public state: WebPlayerState,
        public userPlaylists: UserPlaylists,
        protected zone: NgZone,
        public datatable: DatatableService<Track>,
        private currentUser: CurrentUser,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit() {
        this.el = this.state.scrollContainer;
        this.route.data.subscribe((data: {api: GetPlaylistResponse}) => {
            this.datatable.init({
                uri: `playlists/${data.api.playlist.id}/tracks`,
                initialData: data.api.tracks,
                infiniteScroll: true,
            });
            this.playlist = data.api.playlist;
            this.currentUserIsOwner = data.api.playlist.owner_id === this.currentUser.get('id');
            this.bindToPlaylistEvents();
            this.totalDuration = this.duration.toVerboseString(data.api.totalDuration);
        });
        super.ngOnInit();
    }

    ngOnDestroy() {
        this.subscriptions.forEach(s => s.unsubscribe());
        this.datatable.destroy();
        super.ngOnDestroy();
    }

    public queueId() {
        return queueId(this.playlist, 'allTracks', this.datatable.getCurrentParams());
    }

    public removeTracksFromPlaylist(tracks: Track[]) {
        this.playlists.removeTracks(this.playlist.id, tracks).subscribe();
    }

    public openContextMenu(e: MouseEvent) {
        e.stopPropagation();
        this.contextMenu.open(
            PlaylistContextMenuComponent,
            e.target,
            {originX: 'center', overlayX: 'center', data: {item: this.playlist, type: 'playlist'}}
        );
    }

    public reorderPlaylist(e: CdkDragDrop<Track>) {
        const newData = [...this.datatable.data];
        moveItemInArray(newData, e.previousIndex, e.currentIndex);
        const ids = newData.map(track => track.id);
        this.playlists.changeTrackOrder(this.playlist.id, {ids, previousIndex: e.previousIndex, currentIndex: e.currentIndex}).subscribe();
        this.datatable.data = newData;
    }

    public toggleFollow() {
        this.userPlaylists.following(this.playlist.id) ?
            this.userPlaylists.unfollow(this.playlist) :
            this.userPlaylists.follow(this.playlist);
    }

    public bindToPlaylistEvents() {
        const sub1 = this.playlists.addedTracks$.subscribe(e => {
            if (e.id !== this.playlist.id) return;
            this.datatable.data = e.tracks.concat(this.datatable.data);
        });
        const sub2 = this.playlists.removedTracks$.subscribe(e => {
            if (e.id !== this.playlist.id) return;
            e.tracks.forEach(track => {
                const i = this.datatable.data.findIndex(curr => curr.id === track.id);
                this.datatable.data.splice(i, 1);
                this.datatable.data = this.datatable.data;
            });
        });
        const sub3 = this.playlists.updated$.subscribe(playlist => {
            this.playlist = playlist;
        });
        this.subscriptions = [sub1, sub2, sub3];
    }

    public canLoadMore() {
        return this.datatable.paginator.canLoadNextPage();
    }

    protected isLoading() {
        return this.datatable.paginator.loading$.value;
    }

    protected loadMoreItems() {
        this.datatable.paginator.nextPage();
    }
}
