import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Track} from '../../../models/Track';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Player} from '../../player/player.service';
import {FormattedDuration} from '../../player/formatted-duration.service';
import {WpUtils} from '../../web-player-utils';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {queueId} from '../../player/queue-id';
import {TrackCommentsService} from '../track-comments.service';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';
import {DatatableService} from '@common/datatable/datatable.service';
import {trackIsLocallyUploaded} from '../../utils/track-is-locally-uploaded';

@Component({
    selector: 'track-page',
    templateUrl: './track-page.component.html',
    styleUrls: ['./track-page.component.scss'],
    providers: [TrackCommentsService, DatatableService],
})
export class TrackPageComponent implements OnInit {
    public track: Track;
    public duration: string;
    public tracks: Track[] = [];
    public showWave: boolean;
    public queueId: string;

    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private player: Player,
        private contextMenu: ContextMenu,
        private durationService: FormattedDuration,
        public trackComments: TrackCommentsService,
        public settings: Settings,
        public currentUser: CurrentUser,
        public datatable: DatatableService<Track>,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.track = data.api.track;
            this.showWave = this.settings.get('player.seekbar_type') === 'waveform' && trackIsLocallyUploaded(this.track);
            this.duration = this.durationService.fromMilliseconds(this.track.duration);
            this.queueId = queueId(this.track.album ? this.track.album : this.track, 'allTracks');
            this.tracks = this.track.album ?
                WpUtils.assignAlbumToTracks(this.track.album.tracks, this.track.album) :
                [this.track];
            this.datatable.init({initialData: this.tracks});

            if (this.settings.get('player.track_comments')) {
                this.trackComments.setMediaItem(this.track);
                this.trackComments.pagination$.next(data.api.comments);
            }
        });
    }
}
