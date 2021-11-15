import {Comment} from '@common/shared/comments/comment';

export interface TrackComment extends Comment {
    position: number;
    relative_created_at: string;
}
