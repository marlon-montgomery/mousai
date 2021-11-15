import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    OnDestroy,
    OnInit,
} from '@angular/core';
import {BreakpointsService} from '@common/core/ui/breakpoints.service';
import {BehaviorSubject, Subject} from 'rxjs';
import {LocalStorage} from '@common/core/services/local-storage.service';
import {Settings} from '@common/core/config/settings.service';
import { filter, skip, take, takeUntil } from 'rxjs/operators';
import {NavigationEnd, Router} from '@angular/router';

@Component({
    selector: 'sidenav',
    templateUrl: './sidenav.component.html',
    styleUrls: ['./sidenav.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SidenavComponent implements OnInit, OnDestroy {
    navIsOpen$ = new BehaviorSubject<boolean>(false);
    isCompact$ = new BehaviorSubject<boolean>(false);
    onDestroy$ = new Subject();

    constructor(
        public breakpoints: BreakpointsService,
        private localStorage: LocalStorage,
        private settings: Settings,
        private el: ElementRef<HTMLElement>,
        private router: Router
    ) {}

    ngOnInit() {
        this.navIsOpen$.next(!this.breakpoints.isMobile$.value);

        // set navbar width using css variables
        this.isCompact$
            .pipe(takeUntil(this.onDestroy$))
            .subscribe(isCompact => {
                if (isCompact) {
                    this.el.nativeElement.style.setProperty(
                        '--nav-width',
                        '80px'
                    );
                } else {
                    this.el.nativeElement.style.removeProperty('--nav-width');
                }
            });

        // only enable transitions for subsequent opens so there's no initial flash
        this.navIsOpen$.pipe(skip(1), take(1)).subscribe(() => {
            this.el.nativeElement.classList.add('enable-transitions');
        });

        // toggle "nav-closed" class on host
        this.navIsOpen$.pipe(takeUntil(this.onDestroy$)).subscribe(isOpen => {
            if (isOpen) {
                this.el.nativeElement.classList.remove('nav-closed');
            } else {
                this.el.nativeElement.classList.add('nav-closed');
            }
        });

        // close left column when navigating between routes pages on mobile
        this.router.events
            .pipe(
                filter(e => e instanceof NavigationEnd),
                takeUntil(this.onDestroy$)
            )
            .subscribe(() => {
                this.navIsOpen$.next(!this.breakpoints.isMobile$.value);
            });
    }

    ngOnDestroy() {
        this.onDestroy$.next();
    }

    toggleSidebarMode() {
        if (this.breakpoints.isMobile$.value) {
            this.navIsOpen$.next(!this.navIsOpen$.value);
        } else {
            this.isCompact$.next(!this.isCompact$.value);
            this.localStorage.set(
                this.storageSelector(),
                this.isCompact$.value
            );
        }
    }

    private storageSelector() {
        return `${this.settings.get('branding.site_name')}.sidebarCompact`;
    }
}
