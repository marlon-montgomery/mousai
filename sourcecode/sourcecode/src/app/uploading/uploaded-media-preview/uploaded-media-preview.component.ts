import {Component, OnInit, ChangeDetectionStrategy, Input, ViewChild, ElementRef} from '@angular/core';
import {Track, TRACK_MODEL} from '../../models/Track';
import {ShareableNetworks, shareLinkSocially} from '@common/core/utils/share-link-socially';
import {Settings} from '@common/core/config/settings.service';
import {WebPlayerUrls} from '../../web-player/web-player-urls.service';
import {Album} from '../../models/Album';

@Component({
    selector: 'uploaded-media-preview',
    templateUrl: './uploaded-media-preview.component.html',
    styleUrls: ['./uploaded-media-preview.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {'class': 'material-panel'},
})
export class UploadedMediaPreviewComponent implements OnInit {
    @ViewChild('trackLinkInput', {static: true}) trackLinkInput: ElementRef<HTMLInputElement>;
    @Input() media: Track|Album;

    constructor(
        public settings: Settings,
        public urls: WebPlayerUrls
    ) {}

    ngOnInit() {
        setTimeout(() => {
            this.trackLinkInput.nativeElement.focus();
            this.trackLinkInput.nativeElement.select();
        });
    }

    public mediaLink() {
        if (this.isTrack(this.media)) {
            return this.urls.track(this.media);
        } else {
            return this.urls.album(this.media);
        }
    }

    public mediaUrl() {
        return this.urls.routerLinkToUrl(this.mediaLink());
    }

    public shareUsing(network: ShareableNetworks) {
        shareLinkSocially(network, this.mediaUrl());
    }

    public isTrack(media: Track|Album): media is Track {
        return media.model_type === TRACK_MODEL;
    }
}
