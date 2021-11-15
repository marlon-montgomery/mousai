import {ChangeDetectionStrategy, Component} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';
import {AppCurrentUser} from '../../../app-current-user';

@Component({
    selector: 'backstage-type-selector',
    templateUrl: './backstage-type-selector.component.html',
    styleUrls: ['./backstage-type-selector.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class BackstageTypeSelectorComponent {
    public currentUserIsArtist = false;

    constructor(
        public settings: Settings,
        private currentUser: AppCurrentUser,
    ) {
        this.currentUserIsArtist = !!this.currentUser.primaryArtist();
    }
}
