import {PaginationParams} from '@common/core/types/pagination/pagination-params';

interface MediaItem {
    id: number;
    model_type: string;
}

export function queueId(model: MediaItem, kind: string = 'general', params: PaginationParams = {}): string {
    let base = `${model.model_type}.${model.id}.${kind}`;
    if (params.orderBy && params.orderDir) {
        base += `.${params.orderBy}|${params.orderDir}`;
    }
    return base;
}
