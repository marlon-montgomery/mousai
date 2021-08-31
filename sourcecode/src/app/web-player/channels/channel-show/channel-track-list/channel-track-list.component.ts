import {ChangeDetectionStrategy, Component, Input, NgZone} from '@angular/core';
import {Channel} from '../../../../admin/channels/channel';
import {WebPlayerState} from '../../../web-player-state.service';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {ChannelService} from '../../../../admin/channels/channel.service';
import {ActivatedRoute} from '@angular/router';
import {Track} from '../../../../models/Track';

@Component({
    selector: 'channel-track-list',
    templateUrl: './channel-track-list.component.html',
    styleUrls: ['./channel-track-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ChannelTrackListComponent {
    @Input() channel: Channel;
    @Input() nested = false;

    constructor(
        private state: WebPlayerState,
        protected zone: NgZone,
        private http: AppHttpClient,
        private route: ActivatedRoute,
    ) {}

    loadMoreFn = (page: number) => {
        const filter = this.route.snapshot.params.filter || '';
        return this.http.get<{pagination: PaginationResponse<Track>}>(
            `${ChannelService.BASE_URI}/${this.channel.id}?returnContentOnly=true&filter=${filter}`,
            {page}
        );
    }
}
