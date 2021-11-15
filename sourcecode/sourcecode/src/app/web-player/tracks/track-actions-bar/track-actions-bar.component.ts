import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    Input,
    OnChanges
} from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';
import {Track} from '../../../models/Track';
import {Album, ALBUM_MODEL} from '../../../models/Album';
import {RepostsService} from '../../shared/reposts.service';
import {UserLibrary} from '../../users/user-library/user-library.service';
import {CurrentUser} from '@common/auth/current-user';
import {AlbumContextMenuComponent} from '../../albums/album-context-menu/album-context-menu.component';
import {TrackContextMenuComponent} from '../track-context-menu/track-context-menu.component';
import {ComponentType} from '@angular/cdk/portal';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {ShareMediaItemModalComponent} from '../../context-menu/share-media-item-modal/share-media-item-modal.component';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {Settings} from '@common/core/config/settings.service';
import {AppCurrentUser} from '../../../app-current-user';

type Media = Track | Album;

@Component({
    selector: 'track-actions-bar',
    templateUrl: './track-actions-bar.component.html',
    styleUrls: ['./track-actions-bar.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class TrackActionsBarComponent implements OnChanges {
    @Input() media: Media;
    public liking$ = new BehaviorSubject<number|null>(null);
    public reposting$ = new BehaviorSubject<number|null>(null);
    public userOwnsMedia = false;

    constructor(
        private reposts: RepostsService,
        public userLibrary: UserLibrary,
        private cd: ChangeDetectorRef,
        private currentUser: AppCurrentUser,
        private contextMenu: ContextMenu,
        private modal: Modal,
        public settings: Settings,
    ) {}

    ngOnChanges() {
        if (this.media) {
            const artists = this.media.artists || [];
            this.userOwnsMedia = !!artists.find(artist => {
                return artist?.id === this.currentUser.primaryArtist()?.id;
            });
        }
    }

    public toggleRepost(media: Media) {
        this.reposting$.next(media.id);
        this.reposts.crupdate(media)
            .pipe(finalize(() => this.reposting$.next(null)))
            .subscribe(response => {
                response.action === 'added' ?
                    media.reposts_count++ :
                    media.reposts_count--;
                this.cd.markForCheck();
            });
    }

    public toggleLike(media: Media) {
        this.liking$.next(media.id);
        if (this.userLibrary.has(media)) {
            this.userLibrary.remove([media]).then(() => {
                media.likes_count--;
                this.liking$.next(null);
            });
        } else {
            this.userLibrary.add([media]).then(() => {
                media.likes_count++;
                this.liking$.next(null);
            });
        }
    }

    public openShareModal() {
        const data = {mediaItem: this.media};
        this.modal.open(ShareMediaItemModalComponent, data).afterClosed().subscribe(shared => {
            if ( ! shared) return;
        });
    }

    public showContextMenu(media: Media, e: MouseEvent) {
        e.stopPropagation();
        const c = this.isAlbum(media) ? AlbumContextMenuComponent : TrackContextMenuComponent;
        this.contextMenu.open(c as ComponentType<any>, e.target, {data: {item: media}});
    }

    public isAlbum(media?: Album|Track): media is Album {
        if ( ! media) media = this.media;
        return media.model_type === ALBUM_MODEL;
    }
}
