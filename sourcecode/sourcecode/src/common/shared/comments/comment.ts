import {User} from '@common/core/types/models/User';

export interface Comment {
    id: number;
    content: string;
    user_id: number;
    user?: Partial<User>;
    depth: number;
    deleted: boolean;
    commentable_id: number;
    commentable_type: string;
    children: Comment[];
    created_at?: string;
}
