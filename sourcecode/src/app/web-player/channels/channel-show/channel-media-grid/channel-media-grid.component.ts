import {
    ChangeDetectionStrategy,
    Component,
    Input,
    NgZone,
    OnDestroy,
    OnInit
} from '@angular/core';
import {Channel} from '../../../../admin/channels/channel';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {WebPlayerState} from '../../../web-player-state.service';
import {BehaviorSubject} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {finalize} from 'rxjs/operators';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {ChannelService} from '../../../../admin/channels/channel.service';
import {ChannelContentItem} from '../../../../admin/channels/channel-content-item';
import {CHANNEL_MODEL_TYPES} from '../../../../models/model_types';
import {ActivatedRoute} from '@angular/router';

@Component({
    selector: 'channel-media-grid',
    templateUrl: './channel-media-grid.component.html',
    styleUrls: ['./channel-media-grid.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ChannelMediaGridComponent extends InfiniteScroll implements OnInit, OnDestroy {
    @Input() channel: Channel;
    @Input() nested = false;

    public isCarousel: boolean;
    public pagination$ = new BehaviorSubject<PaginationResponse<ChannelContentItem>>(null);
    public loading$ = new BehaviorSubject<boolean>(false);
    public modelTypes = CHANNEL_MODEL_TYPES;

    constructor(
        private state: WebPlayerState,
        protected zone: NgZone,
        private http: AppHttpClient,
        private route: ActivatedRoute,
    ) {
        super();
    }

    ngOnInit(): void {
        this.el = this.state.scrollContainer;
        this.pagination$.next(this.channel.content);
        this.isCarousel = this.channel.config.carouselWhenNested && this.nested;
        if ( ! this.isCarousel && ! this.nested && ! this.channel.config.disablePagination) {
            super.ngOnInit();
        }
    }

    ngOnDestroy() {
        super.ngOnDestroy();
    }

    protected canLoadMore(): boolean {
        return this.pagination$.value?.last_page >= this.pagination$.value?.current_page;
    }

    protected isLoading(): boolean {
        return this.loading$.value;
    }

    protected loadMoreItems() {
        this.loading$.next(true);
        const filter = this.route.snapshot.params.filter || '';
        this.http.get<{pagination: PaginationResponse<ChannelContentItem>}>(
            `${ChannelService.BASE_URI}/${this.channel.id}?returnContentOnly=true&filter=${filter}`,
            {page: this.currentPage() + 1}
        ).pipe(finalize(() => this.loading$.next(false)))
        .subscribe(response => {
            this.pagination$.next({
                ...response.pagination,
                data: [...this.currentData(), ...response.pagination.data],
            });
        });
    }

    currentPage(): number {
        return this.pagination$.value?.current_page ?? 0;
    }

    currentData(): ChannelContentItem[] {
        return this.pagination$.value?.data ?? [];
    }
}
