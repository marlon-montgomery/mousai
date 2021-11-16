import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Tracks} from '../../../web-player/tracks/tracks.service';
import {FormattedDuration} from '../../../web-player/player/formatted-duration.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {CurrentUser} from '@common/auth/current-user';
import {Track, TRACK_MODEL} from '../../../models/Track';
import {CrupdateLyricModalComponent} from '../../lyrics-page/crupdate-lyric-modal/crupdate-lyric-modal.component';
import {WebPlayerUrls} from '../../../web-player/web-player-urls.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';
import {ImportMediaModalComponent} from '../../import-media-modal/import-media-modal.component';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'track-index',
    templateUrl: './track-index.component.html',
    styleUrls: ['./track-index.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class TrackIndexComponent implements OnInit {
    public tracks$ = this.datatable.data$ as Observable<Track[]>;
    constructor(
        private modal: Modal,
        private tracks: Tracks,
        private duration: FormattedDuration,
        public currentUser: CurrentUser,
        public datatable: DatatableService<Track>,
        public urls: WebPlayerUrls,
        public settings: Settings,
    ) {}

    ngOnInit() {
        this.datatable.sort$.next({orderBy: 'plays'});
        this.datatable.init({
            uri: 'tracks',
            staticParams: {with: ['artists', 'lyric']}
        });
    }

    public openCrupdateLyricModal(track: Track) {
        this.modal.open(CrupdateLyricModalComponent, {track, lyric: track.lyric})
            .afterClosed().subscribe(lyric => {
            if ( ! lyric) return;
            track.lyric = lyric;
        });
    }

    public maybeDeleteSelectedTracks() {
        this.datatable.confirmResourceDeletion('tracks').subscribe(() => {
            const ids = this.datatable.selectedRows$.value;
            this.tracks.delete(ids).subscribe(() => {
                this.datatable.reset();
            });
        });
    }

    public formatDuration(duration: number) {
        return this.duration.fromMilliseconds(duration);
    }

    public openImportMedialModal() {
        this.modal.open(ImportMediaModalComponent, {modelType: TRACK_MODEL})
            .afterClosed()
            .subscribe(response => {
                if (response) {
                    this.datatable.reset();
                }
            });
    }
}
