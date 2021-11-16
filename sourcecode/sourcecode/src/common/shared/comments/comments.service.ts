import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {BackendResponse} from '@common/core/types/backend-response';
import {PaginationParams} from '@common/core/types/pagination/pagination-params';
import {Comment} from '@common/shared/comments/comment';

@Injectable({
    providedIn: 'root'
})
export class CommentsService {
    static BASE_URI = 'comment';
    constructor(private http: AppHttpClient) {}

    public all(params?: PaginationParams): PaginatedBackendResponse<Comment> {
        return this.http.get(CommentsService.BASE_URI, params);
    }

    public get(id: number): BackendResponse<{comment: Comment}> {
        return this.http.get(`${CommentsService.BASE_URI}/${id}`);
    }

    public create<T = Comment>(params: Partial<T> & {inReplyTo?: T}): BackendResponse<{comment: T}> {
        return this.http.post(CommentsService.BASE_URI, params);
    }

    public update(id: number, params: object): BackendResponse<{comment: Comment}> {
        return this.http.put(`${CommentsService.BASE_URI}/${id}`, params);
    }

    public delete(ids: number[]): BackendResponse<{allDeleted: number[], allMarkedAsDeleted: number[]}> {
        return this.http.delete(`${CommentsService.BASE_URI}/${ids}`);
    }

    public restore(ids: number[]) {
        return this.http.post(`${CommentsService.BASE_URI}/restore`, {commentIds: ids});
    }
}
