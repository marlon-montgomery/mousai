import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';
import {CountryListItem} from '@common/core/services/value-lists.service';

@Component({
    selector: 'player-settings',
    templateUrl: './player-settings.component.html',
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'settings-panel'},
})
export class PlayerSettingsComponent extends SettingsPanelComponent implements OnInit {
    public countries: CountryListItem[] = [];

    ngOnInit() {
        this.valueLists.get(['countries']).subscribe(response => {
            this.countries = response.countries;
            this.cd.markForCheck();
        });
    }
}
