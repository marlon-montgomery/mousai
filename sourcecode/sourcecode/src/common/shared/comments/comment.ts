import {User} from '@common/core/types/models/User';
import {NormalizedModel} from '@common/core/types/models/normalized-model';

export interface Comment {
    id: number;
    content: string;
    user_id: number;
    user?: Partial<User>;
    depth: number;
    deleted: boolean;
    commentable_id: number;
    commentable_type: string;
    commentable?: NormalizedModel;
    children: Comment[];
    created_at?: string;
}
