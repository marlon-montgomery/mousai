import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    OnInit,
    ViewChild,
} from '@angular/core';
import {FormBuilder, FormControl} from '@angular/forms';
import {BehaviorSubject, of} from 'rxjs';
import {ChannelService} from '../channel.service';
import {Channel, CHANNEL_MODEL} from '../channel';
import {
    catchError,
    debounceTime,
    distinctUntilChanged,
    filter,
    finalize,
    switchMap,
} from 'rxjs/operators';
import {ActivatedRoute, Router} from '@angular/router';
import {ChannelContentItem} from '../channel-content-item';
import {Toast} from '@common/core/ui/toast.service';
import {slugifyString} from '@common/core/utils/slugify-string';
import {Settings} from '@common/core/config/settings.service';
import {Search} from '../../../web-player/search/search.service';
import {
    CdkDrag,
    CdkDragMove,
    CdkDropList,
    CdkDropListGroup,
    moveItemInArray,
} from '@angular/cdk/drag-drop';
import {ViewportRuler} from '@angular/cdk/overlay';
import {CHANNEL_MODEL_TYPES} from '../../../models/model_types';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {GenericBackendResponse} from '@common/core/types/backend-response';

@Component({
    selector: 'crupdate-channel-page',
    templateUrl: './crupdate-channel-page.component.html',
    styleUrls: ['./crupdate-channel-page.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CrupdateChannelPageComponent implements OnInit {
    public modelTypes = CHANNEL_MODEL_TYPES;
    public channel: Channel;
    public channelContent$ = new BehaviorSubject<ChannelContentItem[]>([]);

    public readonly autoUpdateMethods = {
        spotifyTopTracks: {
            name: 'Spotify: Popular Tracks',
            model: CHANNEL_MODEL_TYPES.track,
            active: this.settings.get('spotify_is_setup'),
        },
        spotifyNewAlbums: {
            name: 'Spotify: New Releases',
            model: CHANNEL_MODEL_TYPES.album,
            active: this.settings.get('spotify_is_setup'),
        },
        spotifyPlaylistTracks: {
            name: 'Spotify: Playlist Tracks',
            model: CHANNEL_MODEL_TYPES.track,
            hasValue: true,
            valueName: 'Playlist ID',
            active: this.settings.get('spotify_is_setup'),
        },
        lastfmTopGenres: {
            name: 'Last.fm: Popular genres',
            model: CHANNEL_MODEL_TYPES.genre,
            active: this.settings.get('lastfm_is_setup'),
        },
    };

    public form = this.fb.group({
        name: [''],
        slug: [''],
        config: this.fb.group({
            hideTitle: [false],
            seoTitle: [''],
            seoDescription: [''],
            carouselWhenNested: [false],
            layout: ['grid'],
            contentType: ['listAll'],
            contentModel: [this.modelTypes.track],
            contentOrder: ['popularity:desc'],
            autoUpdateMethod: [],
            autoUpdateValue: [],
        }),
    });

    public loading$ = new BehaviorSubject<boolean>(null);
    public detaching: number = null;
    public channelUrl$ = new BehaviorSubject<string>('');
    public searchControl = new FormControl();
    public searchResults$ = new BehaviorSubject<ChannelContentItem[]>([]);
    public errors: Partial<Channel> = {};

    @ViewChild(CdkDropListGroup) listGroup: CdkDropListGroup<CdkDropList>;
    @ViewChild(CdkDropList) placeholder: CdkDropList;
    public target: CdkDropList;
    public targetIndex: number;
    public source: CdkDropList;
    public sourceIndex: number;
    public activeContainer;

    constructor(
        private fb: FormBuilder,
        private channels: ChannelService,
        private route: ActivatedRoute,
        private toast: Toast,
        private search: Search,
        private cd: ChangeDetectorRef,
        public settings: Settings,
        private router: Router,
        private viewportRuler: ViewportRuler
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            if (data.api) {
                this.channel = data.api.channel;
                this.form.patchValue(data.api.channel);
                this.channelContent$.next(data.api.channel.content?.data);
            }
        });

        this.form
            .get('slug')
            .valueChanges.pipe(
                filter(value => !!value),
                distinctUntilChanged()
            )
            .subscribe(value => {
                this.channelUrl$.next(
                    this.settings.getBaseUrl() + '/channels/' + value
                );
            });

        if (!this.channel?.config?.lockSlug) {
            this.form
                .get('name')
                .valueChanges.pipe(
                    filter(value => !!value),
                    distinctUntilChanged()
                )
                .subscribe(value => {
                    if (!this.form.get('slug').dirty) {
                        this.form.get('slug').setValue(slugifyString(value));
                    }
                });
        }

        this.form.get('config.contentType').valueChanges.subscribe(value => {
            this.form
                .get('config.autoUpdateMethod')
                .setValue(
                    value === 'autoUpdate'
                        ? Object.keys(this.autoUpdateMethods)[0]
                        : null
                );
            this.form.get('config.autoUpdateValue').setValue(null);

            // prevent "listAll" from having order by "channelables.order"
            this.form.get('config.contentOrder').setValue('popularity:desc');
        });

        this.form
            .get('config.autoUpdateMethod')
            .valueChanges.subscribe((value: string) => {
                const contentModel = this.form.get('config.contentModel');
                if (!value) {
                    contentModel.enable();
                } else {
                    contentModel.setValue(this.autoUpdateMethods[value].model);
                    contentModel.disable();
                }
            });

        this.form
            .get('config.contentModel')
            .valueChanges.pipe(
                filter(value => !!value),
                distinctUntilChanged()
            )
            .subscribe(value => {
                this.searchResults$.next([]);
                const orderControl = this.form.get('config.contentOrder');
                if (value === this.modelTypes.track) {
                    this.form.get('config.layout').setValue('trackTable');
                    orderControl.enable();
                } else if (value === this.modelTypes.channel) {
                    orderControl.setValue('channelables.order');
                    orderControl.disable();
                } else {
                    this.form.get('config.layout').setValue('grid');
                    orderControl.enable();
                }
            });

        this.searchControl.valueChanges
            .pipe(
                debounceTime(200),
                distinctUntilChanged(),
                filter(query => typeof query === 'string' && !!query),
                switchMap(query => this.searchForContent(query)),
                catchError(() => of({results: []})),
            ).subscribe(response => {
                this.searchResults$.next(response.results as ChannelContentItem[]);
            });
    }

    private searchForContent(query: string) {
        const modelType = this.form.get('config.contentModel').value;
        const types = modelType ? [modelType] : Object.values(this.modelTypes);
        return this.search.media(query, {types, flatten: true, limit: 8, localOnly: true});
    }

    public submit(params = {updateContent: false}, successFn?: (channel: GenericBackendResponse<{channel: Channel}>) => void) {
        if ( ! successFn) {
            successFn = () => {
                this.router.navigate(['/admin/channels']);
                this.toast.open('Channel saved.');
            };
        }
        if (
            this.form.get('config.autoUpdateMethod').dirty ||
            this.form.get('config.autoUpdateValue').dirty
        ) {
            params.updateContent = true;
        }
        this.loading$.next(true);
        const payload = {...this.form.getRawValue(), ...params};
        if (!this.channel) {
            payload.content = this.channelContent$.value.map(i =>
                this.partialItem(i)
            );
        }
        const request = this.channel
            ? this.channels.update(this.channel.id, payload)
            : this.channels.create(payload);
        request
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(successFn, (errResponse: BackendErrorResponse) => {
                this.errors = errResponse.errors;
                this.cd.markForCheck();
            });
    }

    public detachContentItem(item: ChannelContentItem) {
        if (this.channel) {
            this.detaching = item.id;
            this.channels
                .detachItem(this.channel.id, item)
                .pipe(finalize(() => (this.detaching = null)))
                .subscribe(() => {
                    this.removeContentItem(item);
                    this.toast.open('Item detached.');
                });
        } else {
            this.removeContentItem(item);
        }
    }

    private removeContentItem(item: ChannelContentItem) {
        const newContent = [...this.channelContent$.value];
        const index = newContent.findIndex(
            c => c.id === item.id && c.model_type === item.model_type
        );
        newContent.splice(index, 1);
        this.channelContent$.next(newContent);
    }

    public attachContentItem(item: ChannelContentItem) {
        const alreadyAttached = this.channelContent$.value.find(
            attachedItem => {
                return (
                    attachedItem.id === item.id &&
                    attachedItem.model_type === item.model_type
                );
            }
        );
        if (alreadyAttached) {
            return;
        }
        if (this.channel) {
            this.channels
                .attachItem(this.channel.id, this.partialItem(item))
                .subscribe(
                    () => {
                        this.channelContent$.next([
                            ...this.channelContent$.value,
                            item]);
                    this.toast.open('Item attached.');
                }, (errResponse: BackendErrorResponse) => {
                    if (errResponse.message) {
                        this.toast.open(errResponse.message);
                    }
                });
        } else {
            this.channelContent$.next([...this.channelContent$.value, item]);
        }
    }

    public autoUpdateChanelContents() {
        this.submit({updateContent: true}, response => {
            this.channelContent$.next(response.channel.content.data);
            this.toast.open('Content updated');
        });
    }

    public isChannel(item: ChannelContentItem): boolean {
        return item.model_type === CHANNEL_MODEL;
    }

    public displayFn() {
        return null;
    }

    public getValueName(): string {
        return this.autoUpdateMethods[
            this.form.get('config.autoUpdateMethod').value
        ]?.valueName;
    }

    private partialItem(item: ChannelContentItem) {
        return {
            id: item.id,
            model_type: item.model_type,
        };
    }

    /* GRID DRAG AND DROP */

    dragMoved(e: CdkDragMove) {
        const point = this.getPointerPositionOnPage(e.event);
        this.listGroup._items.forEach(dropList => {
            if (__isInsideDropListClientRect(dropList, point.x, point.y)) {
                this.activeContainer = dropList;
                return;
            }
        });
    }

    dropListDropped() {
        if (!this.target) return;

        const phElement = this.placeholder.element.nativeElement;
        const parent = phElement.parentElement;

        phElement.style.display = 'none';

        parent.removeChild(phElement);
        parent.appendChild(phElement);
        parent.insertBefore(
            this.source.element.nativeElement,
            parent.children[this.sourceIndex]
        );

        this.target = null;
        this.source = null;

        if (this.sourceIndex !== this.targetIndex) {
            const channelContent = [...this.channelContent$.value];
            moveItemInArray(channelContent, this.sourceIndex, this.targetIndex);
            this.channelContent$.next(channelContent);
            if (this.channel) {
                const order = {};
                channelContent.forEach(
                    (item, i) => (order[i] = item.channelable_id)
                );
                this.channels.changeOrder(this.channel.id, order).subscribe();
            }
        }
    }

    dropListEnterPredicate = (drag: CdkDrag, drop: CdkDropList) => {
        if (drop === this.placeholder) return true;

        if (drop !== this.activeContainer) return false;

        const phElement = this.placeholder.element.nativeElement;
        const sourceElement = drag.dropContainer.element.nativeElement;
        const dropElement = drop.element.nativeElement;

        const dragIndex = __indexOf(dropElement.parentElement.children, (this.source ? phElement : sourceElement));
        const dropIndex = __indexOf(dropElement.parentElement.children, dropElement);

        if ( ! this.source) {
            this.sourceIndex = dragIndex;
            this.source = drag.dropContainer;

            phElement.style.width = sourceElement.clientWidth + 'px';
            phElement.style.height = sourceElement.clientHeight + 'px';

            sourceElement.parentElement.removeChild(sourceElement);
        }

        this.targetIndex = dropIndex;
        this.target = drop;

        phElement.style.display = '';
        dropElement.parentElement.insertBefore(
            phElement,
            dropIndex > dragIndex ? dropElement.nextSibling : dropElement
        );

        // this.placeholder.enterPredicate(drag, drag.element.nativeElement.offsetLeft, drag.element.nativeElement.offsetTop);
        this.placeholder._dropListRef.enter(
            drag._dragRef,
            drag.element.nativeElement.offsetLeft,
            drag.element.nativeElement.offsetTop
        );
        return false;
    };

    /** Determines the point of the page that was touched by the user. */
    getPointerPositionOnPage(event: MouseEvent | TouchEvent) {
        // `touches` will be empty for start/end events so we have to fall back to `changedTouches`.
        const point = __isTouchEvent(event)
            ? event.touches[0] || event.changedTouches[0]
            : event;
        const scrollPosition = this.viewportRuler.getViewportScrollPosition();

        return {
            x: point.pageX - scrollPosition.left,
            y: point.pageY - scrollPosition.top,
        };
    }
}

function __indexOf(collection, node) {
    return Array.prototype.indexOf.call(collection, node);
}

/** Determines whether an event is a touch event. */
function __isTouchEvent(event: MouseEvent | TouchEvent): event is TouchEvent {
    return event.type.startsWith('touch');
}

function __isInsideDropListClientRect(dropList: CdkDropList, x: number, y: number) {
    const {top, bottom, left, right} = dropList.element.nativeElement.getBoundingClientRect();
    return y >= top && y <= bottom && x >= left && x <= right;
}
