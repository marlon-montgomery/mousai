import {
    ChangeDetectionStrategy,
    Component,
    OnInit,
    ViewChild,
} from '@angular/core';
import {Settings} from '../core/config/settings.service';
import {CurrentUser} from '../auth/current-user';
import {MenuItem} from '@common/core/ui/custom-menu/menu-item';
import {SidenavComponent} from '@common/shared/sidenav/sidenav.component';

@Component({
    selector: 'admin',
    templateUrl: './admin.component.html',
    styleUrls: ['./admin.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminComponent implements OnInit {
    @ViewChild(SidenavComponent, {static: true}) sidenav: SidenavComponent;
    menu = this.generateMenu();

    constructor(public settings: Settings) {}

    ngOnInit() {
        this.menu = this.generateMenu();
    }

    public getCustomSidebarItems() {
        return this.settings.get('vebto.admin.pages');
    }

    // TODO: refactor this later, so can be changed from menu manager
    private generateMenu() {
        const items = [
            {
                name: 'analytics',
                icon: 'pie-chart',
                permission: 'reports.view',
                route: 'analytics',
            },
            {
                name: 'appearance',
                icon: 'style',
                permission: 'resources.defaults.permissions.update',
                route: 'appearance',
            },
            {
                name: 'settings',
                icon: 'settings',
                permission: 'settings.view',
                route: 'settings',
            },

            {
                name: 'plans',
                icon: 'assignment',
                permission: 'plans.view',
                route: 'plans',
            },
            {
                name: 'subscriptions',
                icon: 'subscriptions',
                permission: 'subscriptions.view',
                route: 'subscriptions',
            },

            ...this.getCustomSidebarItems(),

            {
                name: 'users',
                icon: 'person',
                permission: 'users.view',
                route: 'users',
            },
            {
                name: 'roles',
                icon: 'people',
                permission: 'roles.view',
                route: 'roles',
            },
            {
                name: 'pages',
                icon: 'page',
                permission: 'pages.view',
                route: 'custom-pages',
            },
            {
                name: 'tags',
                icon: 'local-offer',
                permission: 'tags.view',
                route: 'tags',
            },
            {
                name: 'files',
                icon: 'file',
                permission: 'files.view',
                route: 'files',
            },
            {
                name: 'translations',
                icon: 'translate',
                permission: 'localizations.view',
                route: 'translations',
            },
        ];

        if (this.settings.get('vebto.admin.ads')) {
            items.push({
                name: 'ads',
                icon: 'ads',
                permission: 'settings.view',
                route: 'ads',
            });
        }

        return items.map(item => {
            item.type = 'route';
            item.label = item.name;
            item.action = 'admin/' + item.route;
            item.activeExact = false;
            item.condition = function (user: CurrentUser, settings: Settings) {
                let condition = true;
                if (item.name === 'plans' || item.name === 'subscriptions') {
                    condition = settings.get('billing.enable');
                }
                return condition && user.hasPermission(item.permission);
            };
            return item;
        }) as MenuItem[];
    }
}
