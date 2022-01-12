import {ChangeDetectionStrategy, Component, OnInit, ViewChild} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';
import {LoggedInUserMenuComponent} from '@common/core/ui/logged-in-user-widget/logged-in-user-menu/logged-in-user-menu.component';

@Component({
    selector: 'nav-sidebar-user-menu',
    templateUrl: './nav-sidebar-user-menu.component.html',
    styleUrls: ['./nav-sidebar-user-menu.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class NavSidebarUserMenuComponent implements OnInit {
    @ViewChild(LoggedInUserMenuComponent) loggedInUserMenu: LoggedInUserMenuComponent;
    tryProVisible: boolean;
    constructor(public settings: Settings, public currentUser: CurrentUser) {}

    ngOnInit() {
        this.tryProVisible = this.settings.get('billing.enable') &&
            this.currentUser.hasPermission('plans.view') &&
            !this.currentUser.isSubscribed();
    }
}
