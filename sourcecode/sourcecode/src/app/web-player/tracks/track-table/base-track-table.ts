import {Player} from '../../player/player.service';
import {FormattedDuration} from '../../player/formatted-duration.service';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {ContextMenu} from '../../../../common/core/ui/context-menu/context-menu.service';
import {ChangeDetectorRef, ElementRef, NgZone} from '@angular/core';
import {SelectedTracks} from './selected-tracks.service';
import {BrowserEvents} from '../../../../common/core/services/browser-events.service';
import {WebPlayerState} from '../../web-player-state.service';

export class BaseTrackTable {
    constructor(
        public player: Player,
        private duration: FormattedDuration,
        public urls: WebPlayerUrls,
        private contextMenu: ContextMenu,
        private zone: NgZone,
        private el: ElementRef,
        public selectedTracks: SelectedTracks,
        private browserEvents: BrowserEvents,
        public state: WebPlayerState,
        private cd: ChangeDetectorRef,
    ) {}
}
