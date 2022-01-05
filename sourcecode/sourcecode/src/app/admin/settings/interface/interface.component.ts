import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';
import {ARTIST_PAGE_TABS} from '../../../web-player/artists/artist-page/artist-page-tabs';
import {MatTabChangeEvent} from '@angular/material/tabs';
import {CdkDragDrop, moveItemInArray} from '@angular/cdk/drag-drop';

@Component({
    selector: 'interface',
    templateUrl: './interface.component.html',
    styleUrls: ['./interface.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {class: 'settings-panel'},
})
export class InterfaceComponent extends SettingsPanelComponent implements OnInit {
    private tabs = ['general', 'tracks', 'artistPage'];
    public artistTabs: {id: number, active: boolean}[] = [];
    public allArtistTabs = ARTIST_PAGE_TABS;
    public selectedIndex: number;

    ngOnInit() {
        this.artistTabs = this.settings.getJson('artistPage.tabs', []);
        this.selectedIndex = this.tabs.findIndex(t => {
            return t === (this.route.snapshot.queryParams.tab || 'general');
        });
    }

    public saveSettings() {
        const settings = this.state.getModified();
        settings.client['artistPage.tabs'] = JSON.stringify(this.artistTabs);
        super.saveSettings(settings);
    }

    public toggleArtistTab(tabId: number) {
        const tab = this.artistTabs.find(t => t.id === tabId);
        tab.active = !tab.active;
    }

    public onTabChange(e: MatTabChangeEvent) {
        this.selectedIndex = e.index;
        this.router.navigate([], {
            queryParams: {tab: this.tabs[this.selectedIndex]},
            replaceUrl: true
        });
    }

    public artistPageListDrop(event: CdkDragDrop<string[]>) {
        moveItemInArray(this.artistTabs, event.previousIndex, event.currentIndex);
    }

    public tabIsActive(tab: {id: number}): boolean {
        return this.artistTabs.find(t => t.id === tab.id).active;
    }
}
