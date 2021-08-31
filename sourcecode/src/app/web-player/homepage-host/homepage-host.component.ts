import {Component, OnInit} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {combineLatest, Observable} from 'rxjs';
import {NavigationEnd, Router} from '@angular/router';
import {distinctUntilChanged, filter, map, startWith} from 'rxjs/operators';
import {AppearanceListenerService} from '@common/shared/appearance/appearance-listener.service';
import {Settings} from '@common/core/config/settings.service';
import {LANDING_PAGE_NAME} from '../../app.component';

@Component({
    selector: 'homepage-host',
    templateUrl: './homepage-host.component.html',
    styleUrls: ['./homepage-host.component.scss'],
})
export class HomepageHostComponent implements OnInit {
    public shouldShowLanding$: Observable<boolean>;

    constructor(
        public currentUser: CurrentUser,
        private router: Router,
        private settings: Settings,
        private appearance: AppearanceListenerService,
    ) {}

    ngOnInit() {
        this.shouldShowLanding$ = combineLatest([
           this.router.events
                .pipe(
                    filter(e => e instanceof NavigationEnd),
                    map((e: NavigationEnd) => e.urlAfterRedirects === '/'),
                    startWith(this.router.url.split('?')[0] === '/'),
                ),
            this.currentUser.isLoggedIn$,
        ]).pipe(
            filter(() => this.settings.get('homepage.value') === LANDING_PAGE_NAME),
            map(([isRoot, isLoggedIn]) => {
                return isRoot && (!isLoggedIn || this.appearance.active);
            }),
            distinctUntilChanged(),
        );
    }
}
