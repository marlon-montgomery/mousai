import {ChangeDetectionStrategy, Component} from '@angular/core';
import {SettingsPanelComponent} from '../settings-panel.component';

@Component({
    selector: 'mail-settings',
    templateUrl: './mail-settings.component.html',
    styleUrls: ['./mail-settings.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {class: 'settings-panel'},
})
export class MailSettingsComponent extends SettingsPanelComponent {
    connectGmailAccount() {
        const url = `secure/settings/mail/gmail/connect`;
        this.social.openNewSocialAuthWindow(url).then(user => {
            this.state.server['connectedGmailAccount'] = user.email;
            this.state.errors$.next({});
            this.toast.open(`Connected gmail account: ${user.email}`);
        });
    }
}
