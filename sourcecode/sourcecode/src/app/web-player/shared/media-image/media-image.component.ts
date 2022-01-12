import {
    ChangeDetectionStrategy,
    Component,
    Input,
    OnChanges,
} from '@angular/core';
import {ARTIST_MODEL} from '../../../models/Artist';
import {Album, ALBUM_MODEL} from '../../../models/Album';
import {Track, TRACK_MODEL} from '../../../models/Track';
import {DefaultImagePaths} from '../../default-image-paths.enum';
import {USER_MODEL} from '@common/core/types/models/User';
import {PLAYLIST_MODEL} from '../../../models/Playlist';
import {ChannelContentItem} from '../../../admin/channels/channel-content-item';
import {CHANNEL_MODEL} from '../../../admin/channels/channel';
import {GENRE_MODEL} from '../../../models/Genre';

@Component({
    selector: 'media-image',
    templateUrl: './media-image.component.html',
    styleUrls: ['./media-image.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MediaImageComponent implements OnChanges {
    @Input() media: ChannelContentItem | string;
    @Input() default: 'artist' | 'album' | 'track' = 'album';
    public src: string;

    ngOnChanges() {
        this.src = this.getSrc(this.media);
    }

    private getSrc(media: ChannelContentItem | string) {
        if (!media) {
            return DefaultImagePaths.album;
        }
        if (typeof media === 'string') {
            return media;
        }
        switch (media.model_type) {
            case ARTIST_MODEL:
                return (
                    media.image_small ||
                    media.albums?.[0]?.image ||
                    DefaultImagePaths.artistSmall
                );
            case ALBUM_MODEL:
                return this.albumImage(media);
            case TRACK_MODEL:
                return this.trackImage(media);
            case USER_MODEL:
                return media.avatar;
            case PLAYLIST_MODEL:
                return media.image || DefaultImagePaths.album;
            case CHANNEL_MODEL:
                return DefaultImagePaths.album;
            case GENRE_MODEL:
                return media.image || DefaultImagePaths.artistSmall;
        }
    }

    public albumImage(album: Album): string {
        if (album && album.image) return album.image;
        return DefaultImagePaths.album;
    }

    public trackImage(track: Track) {
        if (track.image) {
            return track.image;
        } else if (track.album && track.album.image) {
            return track.album.image;
        } else {
            return DefaultImagePaths.album;
        }
    }

    public alt() {
        return this.media
            ? this.media['name'] || this.media['display_name'] || this.default
            : this.default;
    }
}
