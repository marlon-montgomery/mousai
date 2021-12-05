import {filter} from 'rxjs/operators';
import {Component, ElementRef, OnInit, ViewEncapsulation} from '@angular/core';
import {NavigationEnd, Router} from '@angular/router';
import {BrowserEvents} from '@common/core/services/browser-events.service';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Settings} from '@common/core/config/settings.service';
import cssVars from 'css-vars-ponyfill';
import {MetaTagsService} from '@common/core/meta/meta-tags.service';
import {ChannelShowComponent} from './web-player/channels/channel-show/channel-show.component';
import {ChannelResolverService} from './admin/channels/crupdate-channel-page/channel-resolver.service';
import {CookieNoticeService} from '@common/gdpr/cookie-notice/cookie-notice.service';
import {CustomHomepage} from '@common/pages/shared/custom-homepage.service';
import {MatIconRegistry} from '@angular/material/icon';
import {DomSanitizer} from '@angular/platform-browser';


export const LANDING_PAGE_NAME = 'Landing Page';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AppComponent implements OnInit {
    constructor(
        private browserEvents: BrowserEvents,
        private el: ElementRef,
        private http: AppHttpClient,
        private settings: Settings,
        private router: Router,
        private customHomepage: CustomHomepage,
        private meta: MetaTagsService,
        private cookieNotice: CookieNoticeService,
        private matIconRegistry: MatIconRegistry,
        private domSanitzer: DomSanitizer,
    ) {
        this.matIconRegistry.addSvgIcon(
            'bitclout',
            this.domSanitzer.bypassSecurityTrustResourceUrl('client/assets/icons/individual/bitclout.svg')
        );
    }

    ngOnInit() {
        this.browserEvents.subscribeToEvents(this.el.nativeElement);
        this.settings.setHttpClient(this.http);
        this.meta.init();

        // google analytics
        if (this.settings.get('analytics.tracking_code')) {
            this.triggerAnalyticsPageView();
        }

        // custom homepage
        this.customHomepage.select({
            menuCategories: [{
                name: 'Channel',
                route: {
                    component: ChannelShowComponent,
                    resolve: {api: ChannelResolverService},
                    data: {name: 'channel'}
                }
            }],
            routes: [{
                name: LANDING_PAGE_NAME,
                ignore: true,
            }]
        });

        this.loadCssVariablesPolyfill();
        this.cookieNotice.maybeShow();
    }

    private triggerAnalyticsPageView() {
        this.router.events
            .pipe(filter(e => e instanceof NavigationEnd))
            .subscribe((event: NavigationEnd) => {
                if ( ! window['ga']) return;
                window['ga']('set', 'page', event.urlAfterRedirects);
                window['ga']('send', 'pageview');
            });
    }

    private loadCssVariablesPolyfill() {
        const isNativeSupport = typeof window !== 'undefined' &&
            window['CSS'] &&
            window['CSS'].supports &&
            window['CSS'].supports('(--a: 0)');
        if ( ! isNativeSupport) {
            cssVars();
        }
    }
}
