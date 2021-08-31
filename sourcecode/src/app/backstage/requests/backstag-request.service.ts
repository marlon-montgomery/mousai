import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {BackendResponse} from '@common/core/types/backend-response';
import {BackstageRequest} from '../../models/backstage-request';

@Injectable({
    providedIn: 'root'
})
export class BackstagRequestService {

    constructor(private http: AppHttpClient) {
    }

    public getRequest(requestId: number): BackendResponse<{request: BackstageRequest}> {
        return this.http.get(`backstage-request/${requestId}`);
    }

    public submitRequest(params: object): BackendResponse<{request: {id: number}}> {
        return this.http.post('backstage-request', params);
    }

    public approveRequest(requestId: number, params: {markArtistAsVerified?: boolean, notes?: string} = {}) {
        return this.http.post(`backstage-request/${requestId}/approve`, params);
    }

    public denyRequest(requestId: number, params: {notes?: string} = {}) {
        return this.http.post(`backstage-request/${requestId}/deny`, params);
    }

    public deleteRequests(requestIds: number[]): BackendResponse<void> {
        return this.http.delete(`backstage-request/${requestIds.join(',')}`);
    }
}
