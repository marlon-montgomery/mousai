import {Component, Input} from '@angular/core';
import {Channel} from '../../../../admin/channels/channel';
import {WebPlayerUrls} from '../../../web-player-urls.service';
import {CurrentUser} from '@common/auth/current-user';
import {CHANNEL_MODEL_TYPES} from '../../../../models/model_types';

@Component({
    selector: 'channel-content',
    templateUrl: './channel-content.component.html',
    styleUrls: ['./channel-content.component.scss'],
})
export class ChannelContentComponent {
    @Input() channel: Channel;
    @Input() nested = false;

    public modelTypes = CHANNEL_MODEL_TYPES;

    constructor(
        public urls: WebPlayerUrls,
        public user: CurrentUser,
    ) {}
}
