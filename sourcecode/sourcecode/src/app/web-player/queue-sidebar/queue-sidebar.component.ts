import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {PlayerQueue} from '../player/player-queue.service';
import {Track} from '../../models/Track';
import {Player} from '../player/player.service';
import {QueueSidebar} from './queue-sidebar.service';
import {TrackContextMenuComponent} from '../tracks/track-context-menu/track-context-menu.component';
import {Settings} from '@common/core/config/settings.service';
import {WebPlayerImagesService} from '../web-player-images.service';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';

@Component({
    selector: 'queue-sidebar',
    templateUrl: './queue-sidebar.component.html',
    styleUrls: ['./queue-sidebar.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'[class.hide]': '!sidebar.isVisible()'}
})
export class QueueSidebarComponent implements OnInit {
    public videoIsHidden: boolean;

    constructor(
        public queue: PlayerQueue,
        public player: Player,
        public sidebar: QueueSidebar,
        private contextMenu: ContextMenu,
        private settings: Settings,
        public wpImages: WebPlayerImagesService,
    ) {}

    ngOnInit() {
        this.videoIsHidden = this.settings.get('player.hide_video');

        if (this.settings.get('player.hide_queue')) {
            this.sidebar.hide();
        }
    }

    public playTrack(track: Track, index: number) {
        if (this.player.cued(track)) {
            this.player.play();
        } else {
            this.player.stop();
            this.queue.set(index);
            this.player.play();
        }
    }

    public trackIsPlaying(track: Track) {
        return this.player.isPlaying() && this.player.cued(track);
    }

    public showContextMenu(track: Track, e: MouseEvent) {
        e.stopPropagation();
        this.contextMenu.open(TrackContextMenuComponent, e.target, {data: {item: track, type: 'track'}});
    }
}
