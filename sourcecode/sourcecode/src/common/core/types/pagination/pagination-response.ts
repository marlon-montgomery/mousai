export interface PaginationResponse<T> {
    data: T[];
    from?: number;
    to?: number;
    total?: number;
    per_page: number;
    current_page?: number;
    last_page?: number;
    prev_cursor?: string;
    next_cursor?: string;
}

export const EMPTY_PAGINATION_RESPONSE = {
    pagination: {data: []}
};
