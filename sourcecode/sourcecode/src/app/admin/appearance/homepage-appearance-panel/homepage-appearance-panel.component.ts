import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {BehaviorSubject, of} from 'rxjs';
import {FormArray, FormBuilder, FormControl} from '@angular/forms';
import {AppearanceEditor} from '@common/admin/appearance/appearance-editor/appearance-editor.service';
import {Settings} from '@common/core/config/settings.service';
import {catchError, debounceTime, distinctUntilChanged, filter, finalize, map, switchMap} from 'rxjs/operators';
import * as Dot from 'dot-object';
import {HomepageContent} from '../../../web-player/landing/homepage-content';
import {Channel} from '../../channels/channel';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import {ChannelService} from '../../channels/channel.service';
import {moveItemInArray} from '@angular/cdk/drag-drop';
import {mapOrder} from '@common/core/utils/map-order';

const CONFIG_KEY = 'homepage.appearance';

@Component({
    selector: 'homepage-appearance-panel',
    templateUrl: './homepage-appearance-panel.component.html',
    styleUrls: ['./homepage-appearance-panel.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class HomepageAppearancePanelComponent implements OnInit {
    public selectedSubpanel$ = new BehaviorSubject<string>(null);
    public defaultValues: HomepageContent;

    public path$ = this.selectedSubpanel$.pipe(map(panel => {
        const path = ['Homepage'];
        if (panel) path.push(panel);
        return path;
    }));

    public form = this.fb.group({
        headerTitle: [''],
        headerSubtitle: [''],
        headerImage: [''],
        headerOverlayColor1: [''],
        headerOverlayColor2: [''],
        footerTitle: [''],
        footerSubtitle: [''],
        footerImage: [''],
        actions: this.fb.group({
            inputText: [''],
            inputButton: [''],
            cta1: [''],
            cta2: [''],
        }),
        primaryFeatures: this.fb.array([]),
        secondaryFeatures: this.fb.array([]),
        channelIds: this.fb.control([]),
    });

    public searchControl = new FormControl();
    public results$ = new BehaviorSubject<Channel[]>([]);
    public loading$ = new BehaviorSubject(false);
    public selectedChannels$ = new BehaviorSubject<Channel[]>([]);

    constructor(
        private fb: FormBuilder,
        private editor: AppearanceEditor,
        private settings: Settings,
        private channels: ChannelService,
    ) {}

    ngOnInit() {
        this.bindToSearchQueryControl();
        const data = this.settings.getJson(CONFIG_KEY, {}) as HomepageContent;
        this.loadChannels(data.channelIds);
        this.defaultValues = this.editor.defaultSettings[CONFIG_KEY] ? JSON.parse(this.editor.defaultSettings[CONFIG_KEY]) : {};

        (data.primaryFeatures || []).forEach(() => {
            this.addFeature('primary');
        });
        (data.secondaryFeatures || []).forEach(() => {
            this.addFeature('secondary');
        });

        this.form.patchValue(data);

        this.form.valueChanges.subscribe(value => {
            this.editor.setConfig(CONFIG_KEY, value);
            this.editor.addChanges({[CONFIG_KEY]: value});
        });
    }

    public openPreviousPanel() {
        if (this.selectedSubpanel$.value) {
            this.openSubpanel(null);
        } else {
            this.editor.closeActivePanel();
        }
    }

    public openSubpanel(name: string) {
        this.selectedSubpanel$.next(name);
    }

    public addFeature(type: 'primary'|'secondary') {
        const features = this.form.get(`${type}Features`) as FormArray;
        const data: {[key: string]: string[]} = {title: [''], subtitle: [''], image: ['']};
        if (type === 'secondary') {
            data.description = [''];
        }
        features.push(this.fb.group(data));
    }

    public removeFeature(type: 'primary'|'secondary', index: number) {
        const features = this.form.get(`${type}Features`) as FormArray;
        features.removeAt(index);
    }

    public defaultValue(key: string): string {
        return Dot.pick(key, this.defaultValues) || '';
    }

    public primaryArray() {
        return this.form.get('primaryFeatures') as FormArray;
    }

    public secondaryArray() {
        return this.form.get('secondaryFeatures') as FormArray;
    }

    /**
     * CHANNELS
     */

    private loadChannels(channelIds: number[]) {
        if ( ! channelIds || !channelIds.length) return;
        this.loading$.next(true);
        this.channels.all({channelIds})
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                const channels = response.pagination.data;
                this.selectedChannels$.next(mapOrder(channels, channelIds, 'id'));
            });
    }

    public removeChannel(channel: Channel) {
        const channels = this.selectedChannels$.value;
        const newChannels = channels.filter(curr => curr.id !== channel.id);
        this.updateChannels(newChannels);
    }

    public reorderChannels($event) {
        const channels = this.selectedChannels$.value.slice();
        moveItemInArray(channels, $event.previousIndex, $event.currentIndex);
        this.updateChannels(channels);
    }

    public selectResult(e: MatAutocompleteSelectedEvent) {
        const newChannel = e.option.value;
        if (this.selectedChannels$.value.find(c => c.id === newChannel.id)) {
            return;
        }
        this.updateChannels([...this.selectedChannels$.value, newChannel]);
        this.searchControl.reset();
    }

    private updateChannels(newChannels: Channel[]) {
        this.selectedChannels$.next(newChannels);
        this.form.patchValue({channelIds: newChannels.map(c => c.id)});
    }

    private bindToSearchQueryControl() {
        this.searchControl.valueChanges
            .pipe(
                debounceTime(200),
                distinctUntilChanged(),
                filter(query => typeof query === 'string'),
                switchMap(query => this.searchForChannel(query)),
                catchError(() => of([])),
            ).subscribe(results => {
            this.results$.next(results);
        });
    }

    private searchForChannel(query: string) {
        return this.channels.all({query})
            .pipe(map(response => {
                return response.pagination.data
                    // track list is not supported on landing page currently.
                    .filter(c => c.config.layout !== 'trackTable' && c.config.layout !== 'trackList');
            }));
    }

    public displayFn = (channel: Channel) => channel ? channel.name : '';
}
