import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    NgZone,
    OnInit
} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {ActivatedRoute} from '@angular/router';
import {BehaviorSubject} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Track} from '../../../models/Track';
import {MatTabChangeEvent} from '@angular/material/tabs';
import {Tag} from '@common/core/types/models/Tag';
import {Album} from '../../../models/Album';
import {finalize} from 'rxjs/operators';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';

interface TagMediaIndexResponse {
    tag: Tag;
    tracks?: PaginationResponse<Track>;
    albums?: PaginationResponse<Album>;
}

type MediaType = 'tracks'|'albums';

@Component({
    selector: 'tag-media-page',
    templateUrl: './tag-media-page.component.html',
    styleUrls: ['./tag-media-page.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class TagMediaPageComponent extends InfiniteScroll implements OnInit, AfterViewInit {
    public trackPagination$ = new BehaviorSubject<PaginationResponse<Track>>(null);
    public albumPagination$ = new BehaviorSubject<PaginationResponse<Album>>(null);
    public tagName$ = new BehaviorSubject<string>(null);
    public activeTab: MediaType = 'tracks';
    public loading$ = new BehaviorSubject<boolean>(false);

    constructor(
        private http: AppHttpClient,
        private route: ActivatedRoute,
        protected zone: NgZone,
        protected el: ElementRef<HTMLElement>,
    ) {
        super();
    }

    ngOnInit() {
        this.route.params.subscribe(params => {
            this.tagName$.next(params.name);
            this.loadMoreItems();
        });
    }

    ngAfterViewInit() {
        const scrollContainer = this.el.nativeElement.closest('.page-wrapper') as HTMLElement;
        this.el = new ElementRef(scrollContainer);
        super.ngOnInit();
    }

    public onTabChange(e: MatTabChangeEvent) {
        this.activeTab = e.index === 0 ? 'tracks' : 'albums';
        if (
            this.activeTab === 'tracks' && !this.trackPagination$.value ||
            this.activeTab === 'albums' && !this.albumPagination$.value
        ) {
            this.loadMoreItems();
        }
    }

    protected loadMoreItems() {
        this.loading$.next(true);
        this.http.get<TagMediaIndexResponse>(`tags/${this.tagName$.value}/${this.activeTab}`, {page: this.currentPage() + 1})
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.pagination().next({
                    ...response[this.activeTab],
                    data: [...this.currentData(), ...(response[this.activeTab].data as any)],
                });
            });
    }

    protected canLoadMore(): boolean {
        return !this.pagination().value || (this.pagination().value.last_page > this.pagination().value.current_page);
    }

    protected isLoading(): boolean {
        return this.loading$.value;
    }

    private pagination() {
        return this.activeTab === 'albums' ? this.albumPagination$ : this.trackPagination$;
    }

    protected currentPage() {
        return this.pagination().value ? this.pagination().value.current_page : 0;
    }

    protected currentData() {
        return this.pagination().value ? this.pagination().value.data : [];
    }
}
