import {ElementRef, Injectable} from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {TrackComment} from '../../models/TrackComment';
import {CommentsService} from '@common/shared/comments/comments.service';
import {Track, TRACK_MODEL} from '../../models/Track';
import {Toast} from '@common/core/ui/toast.service';
import {CurrentUser} from '@common/auth/current-user';
import {Player} from '../player/player.service';
import {finalize} from 'rxjs/operators';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Tracks} from './tracks.service';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {BOTTOM_POSITION} from '@common/core/ui/overlay-panel/positions/bottom-position';
import {OverlayPanel} from '@common/core/ui/overlay-panel/overlay-panel.service';
import {ConfirmCommentDeletionPopoverComponent} from './track-page/comments/confirm-comment-deletion-popover/confirm-comment-deletion-popover.component';
import {Comment} from '@common/shared/comments/comment';
import {Album} from '../../models/Album';

@Injectable({
    providedIn: 'root'
})
export class TrackCommentsService {
    public waveComments$ = new BehaviorSubject<TrackComment[]>([]);
    public pagination$ = new BehaviorSubject<PaginationResponse<TrackComment>>(null);
    public mediaItem: Track|Album;
    public loading$ = new BehaviorSubject<boolean>(false);
    public loadingMore$ = new BehaviorSubject<boolean>(false);
    public markerPosition$ = new BehaviorSubject<number|null>(null);
    public markerActive$ = new BehaviorSubject<boolean>(false);
    public canDeleteAllComments = false;
    private confirmDeleteOverlayRef: OverlayPanelRef<ConfirmCommentDeletionPopoverComponent>;

    constructor(
        private comments: CommentsService,
        private toast: Toast,
        private currentUser: CurrentUser,
        private player: Player,
        private tracks: Tracks,
        private overlay: OverlayPanel,
    ) {}

    public create(content: string, inReplyTo?: TrackComment): Promise<TrackComment> {
        return new Promise(resolve => {
            this.loading$.next(true);
            const position = this.markerPosition$.value != null ?
                this.markerPosition$.value :
                (this.player.getCurrentTime() / this.player.getDuration()) * 100;
            this.comments.create<TrackComment>({
                commentable_id: this.mediaItem.id,
                commentable_type: this.mediaItem.model_type,
                content,
                position: position || 0,
                inReplyTo,
            }).pipe(finalize(() => this.loading$.next(false))).subscribe(response => {
                const newComment = response.comment;
                newComment.user = this.currentUser.getModel();
                if (inReplyTo) {
                    const i = this.pagination$.value.data.findIndex(c => c.id === inReplyTo.id);
                    this.pagination$.value.data.splice(i + 1, 0, newComment);
                    this.pagination$.next({...this.pagination$.value});
                } else {
                    // wave comments
                    this.waveComments$.next([
                        ...this.waveComments$.value, newComment
                    ]);

                    // paginated comments (if on main track page)
                    if (this.pagination$.value) {
                        this.pagination$.value.data.unshift(newComment);
                        this.pagination$.next({...this.pagination$.value});
                    }
                }
                this.mediaItem.comments_count++;
                this.markerPosition$.next(null);
                this.markerActive$.next(false);
                this.toast.open('Comment posted.');
                resolve(response.comment);
            });
        });
    }

    public loadMore() {
        this.loadingMore$.next(true);
        return this.tracks.loadComments(this.mediaItem.id, {page: this.pagination$.value.current_page + 1})
            .pipe(finalize(() => this.loadingMore$.next(false)))
            .subscribe(response => {
                this.pagination$.next({
                    ...response.pagination, data: [...this.pagination$.value.data, ...response.pagination.data]
                });
            });
    }

    public setMediaItem(mediaItem: Track|Album) {
        this.mediaItem = mediaItem;
        if (this.currentUser.isLoggedIn()) {
            const artistIds = this.mediaItem.artists.map(a => a.id);
            const managesItem = !!this.currentUser.get('artists').find(a => artistIds.includes(a.id as number));
            this.canDeleteAllComments = this.currentUser.hasPermissions(['comments.delete', 'music.update']) || managesItem;
        }
    }

    public confirmDeletion(origin: ElementRef<HTMLElement>, comment: Comment) {
        if (this.confirmDeleteOverlayRef) {
            this.confirmDeleteOverlayRef.close();
            this.confirmDeleteOverlayRef = null;
        }
        const position = [...BOTTOM_POSITION];
        this.confirmDeleteOverlayRef = this.overlay.open(ConfirmCommentDeletionPopoverComponent, {
            origin,
            position,
            backdropClass: 'cdk-overlay-transparent-backdrop',
        });

        this.confirmDeleteOverlayRef.afterClosed().subscribe(confirmed => {
            if (confirmed) {
                this.comments.delete([comment.id]).subscribe(response => {
                    const newData = [];
                    this.pagination$.value.data.forEach(c => {
                       if ( ! response.allDeleted.includes(c.id)) {
                           if (response.allMarkedAsDeleted.includes(c.id)) {
                               c = {...c, content: null, user: null, deleted: true};
                           }
                           newData.push(c);
                       }
                    });
                    this.pagination$.next({
                        ...this.pagination$.value,
                        data: newData,
                    });
                });
            }
        });
    }
}
