import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {BackendResponse} from '@common/core/types/backend-response';
import {PaginationParams} from '@common/core/types/pagination/pagination-params';
import {Comment} from '@common/shared/comments/comment';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';

@Injectable({
    providedIn: 'root',
})
export class CommentsService {
    static BASE_URI = 'comment';
    constructor(private http: AppHttpClient) {}

    all(params?: PaginationParams): PaginatedBackendResponse<Comment> {
        return this.http.get(CommentsService.BASE_URI, params);
    }

    forCommentable(
        params: PaginationParams & {
            commentableType: string;
            commentableId: number;
        }
    ): BackendResponse<{
        pagination: PaginationResponse<Comment>;
        commentCount: number;
    }> {
        return this.http.get('commentable/comments', params);
    }

    get(id: number): BackendResponse<{comment: Comment}> {
        return this.http.get(`${CommentsService.BASE_URI}/${id}`);
    }

    create<T = Comment>(
        params: Partial<T> & {inReplyTo?: T}
    ): BackendResponse<{comment: T}> {
        return this.http.post(CommentsService.BASE_URI, params);
    }

    update(id: number, params: object): BackendResponse<{comment: Comment}> {
        return this.http.put(`${CommentsService.BASE_URI}/${id}`, params);
    }

    delete(
        ids: number[]
    ): BackendResponse<{allDeleted: number[]; allMarkedAsDeleted: number[]}> {
        return this.http.delete(`${CommentsService.BASE_URI}/${ids}`);
    }

    restore(ids: number[]) {
        return this.http.post(`${CommentsService.BASE_URI}/restore`, {
            commentIds: ids,
        });
    }
}
