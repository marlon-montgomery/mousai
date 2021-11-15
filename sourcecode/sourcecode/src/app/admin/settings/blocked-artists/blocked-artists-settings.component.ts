import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';

@Component({
    selector: 'blocked-artists-settings',
    templateUrl: './blocked-artists-settings.component.html',
    styleUrls: ['./blocked-artists-settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'settings-panel'},
})
export class BlockedArtistsSettingsComponent extends SettingsPanelComponent implements OnInit {

    /**
     * Blocked artist input model.
     */
    public blockedArtist: string;

    /**
     * List of blocked artist names.
     */
    public blockedArtists: string[] = [];

    ngOnInit() {
        const blockedArtists = this.state.client['artists.blocked'] as string;
        this.blockedArtists = blockedArtists ? JSON.parse(blockedArtists) : [];
    }

    /**
     * Add a new artist to blocked artists list.
     */
    public addBlockedArtist() {
        if ( ! this.blockedArtist) return;

        if (this.blockedArtists.findIndex(curr => curr === this.blockedArtist) > -1) {
            return this.blockedArtist = null;
        }

        this.blockedArtists.push(this.blockedArtist);
        this.blockedArtist = null;
    }

    /**
     * Remove specified artist from blocked artists list.
     */
    public removeBlockedArtist(blockedArtist: string) {
        const i = this.blockedArtists.findIndex(curr => curr === blockedArtist);
        this.blockedArtists.splice(i, 1);
    }

    public saveSettings() {
        const settings = this.state.getModified();
        settings.client['artists.blocked'] = JSON.stringify(this.blockedArtists);
        super.saveSettings(settings);
    }
}
