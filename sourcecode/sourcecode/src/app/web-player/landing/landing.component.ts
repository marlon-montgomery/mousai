import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    OnInit
} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {ReplaySubject} from 'rxjs';
import {Settings} from '@common/core/config/settings.service';
import {Translations} from '@common/core/translations/translations.service';
import {HomepageContent} from './homepage-content';
import {MetaTagsService} from '@common/core/meta/meta-tags.service';
import {FormControl} from '@angular/forms';
import {WebPlayerUrls} from '../web-player-urls.service';
import {Router} from '@angular/router';
import {Channel} from '../../admin/channels/channel';
import {GenericBackendResponse} from '@common/core/types/backend-response';
import {DomSanitizer} from '@angular/platform-browser';

@Component({
    selector: 'landing',
    templateUrl: './landing.component.html',
    styleUrls: ['./landing.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LandingComponent implements OnInit {
    public channels$ = new ReplaySubject<Channel[]>(1);
    public content: HomepageContent;
    public searchControl = new FormControl();
    public overlayBackground;

    constructor(
        private http: AppHttpClient,
        public settings: Settings,
        private i18n: Translations,
        private metaTags: MetaTagsService,
        private urls: WebPlayerUrls,
        private router: Router,
        private cd: ChangeDetectorRef,
        private sanitizer: DomSanitizer,
    ) {}

    ngOnInit() {
        this.settings.all$().subscribe(() => {
            this.content = this.settings.getJson('homepage.appearance');
            this.overlayBackground = this.sanitizer.bypassSecurityTrustStyle(
                `linear-gradient(45deg, ${this.content.headerOverlayColor1} 0%, ${this.content.headerOverlayColor2} 100%)`
            );
            this.cd.markForCheck();
        });
        this.http.get<GenericBackendResponse<{channels: Channel[]}>>('landing/channels').subscribe(response => {
            const channels = response.channels.map(channel => {
                channel.config.disablePagination = true;
                channel.config.disablePlayback = true;
                return channel;
            });
            this.channels$.next(channels);
            this.metaTags.latestMetaTags$.next(response.seo);
        });
    }

    public copyrightText() {
        const year = (new Date()).getFullYear();
        return this.i18n.t('Copyright © :year, All Rights Reserved', {year});
    }

    public search() {
        const value = this.searchControl.value;
        this.searchControl.reset();
        this.router.navigate(this.urls.search(value));
    }
}
