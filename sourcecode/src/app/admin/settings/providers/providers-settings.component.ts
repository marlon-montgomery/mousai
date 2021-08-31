import {Component} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';

@Component({
    selector: 'providers-settings',
    templateUrl: './providers-settings.component.html',
    host: {'class': 'settings-panel'},
})
export class ProvidersSettingsComponent extends SettingsPanelComponent {
    private providers = ['artist', 'album', 'search', 'genres', 'new_releases', 'top_50', 'biography'];

    /**
     * Check if specified provider API keys should be requested in settings page.
     */
    public needProviderKeys(name: string): boolean {
        return this.providers.findIndex(provider => {
            const currentName = this.state.client[provider + '_provider'] || '';
            return (currentName as string).toLowerCase() === name.toLowerCase();
        }) > -1;
    }
}
