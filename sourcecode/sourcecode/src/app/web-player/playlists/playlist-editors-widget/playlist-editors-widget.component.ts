import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {Playlist} from '../../../models/Playlist';
import {WebPlayerUrls} from '../../web-player-urls.service';

@Component({
    selector: 'playlist-editors-widget',
    templateUrl: './playlist-editors-widget.component.html',
    styleUrls: ['./playlist-editors-widget.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class PlaylistEditorsWidgetComponent {
    @Input() playlist: Playlist;

    constructor(public urls: WebPlayerUrls) {}
}
