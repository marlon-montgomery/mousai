import {Component, OnDestroy, ViewEncapsulation} from '@angular/core';
import {Player} from '../player.service';
import {FullscreenOverlay} from '../../fullscreen-overlay/fullscreen-overlay.service';
import {MatBottomSheet} from '@angular/material/bottom-sheet';
import {Subscription} from 'rxjs';
import {NavSidebarUserMenuComponent} from '../../nav-sidebar/nav-sidebar-user-menu/nav-sidebar-user-menu.component';

@Component({
    selector: 'mobile-player-controls',
    templateUrl: './mobile-player-controls.component.html',
    styleUrls: ['./mobile-player-controls.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class MobilePlayerControlsComponent implements OnDestroy {
    private subscription: Subscription;
    constructor(
        public player: Player,
        public overlay: FullscreenOverlay,
        private matSheet: MatBottomSheet,
    ) {}

    openUserMenuSheet() {
        this.subscription?.unsubscribe();
        this.subscription = this.matSheet.open(NavSidebarUserMenuComponent).instance.loggedInUserMenu.itemClicked.subscribe(() => {
            this.matSheet.dismiss();
        });
    }

    ngOnDestroy() {
        this.subscription?.unsubscribe();
    }
}
