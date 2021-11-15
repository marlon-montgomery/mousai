import {
    ChangeDetectionStrategy,
    Component,
    Input,
    NgZone,
    OnDestroy,
    OnInit
} from '@angular/core';
import {DatatableService} from '@common/datatable/datatable.service';
import {Track} from '../../../../models/Track';
import {ChannelService} from '../../../../admin/channels/channel.service';
import {Channel} from '../../../../admin/channels/channel';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {WebPlayerState} from '../../../web-player-state.service';
import {ActivatedRoute} from '@angular/router';

@Component({
    selector: 'channel-track-table',
    templateUrl: './channel-track-table.component.html',
    styleUrls: ['./channel-track-table.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class ChannelTrackTableComponent extends InfiniteScroll implements OnInit, OnDestroy {
    @Input() channel: Channel;
    @Input() nested = false;

    constructor(
        public datatable: DatatableService<Track>,
        private state: WebPlayerState,
        protected zone: NgZone,
        private route: ActivatedRoute,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit(): void {
        this.el = this.state.scrollContainer;
        const filter = this.route.snapshot.params.filter || '';
        this.datatable.init({
            uri: `${ChannelService.BASE_URI}/${this.channel.id}`,
            initialData: this.channel.content as any,
            infiniteScroll: true,
            staticParams: {filter, returnContentOnly: true},
        });
        if ( ! this.nested && ! this.channel.config.disablePagination) {
            super.ngOnInit();
        }
    }

    ngOnDestroy() {
        this.datatable.destroy();
        super.ngOnDestroy();
    }

    public canLoadMore() {
        return this.datatable.paginator.canLoadNextPage();
    }

    protected isLoading() {
        return this.datatable.paginator.loading$.value;
    }

    protected loadMoreItems() {
        this.datatable.paginator.nextPage();
    }

}
