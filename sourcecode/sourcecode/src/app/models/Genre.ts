export const GENRE_MODEL = 'genre';

export interface Genre {
    id: number;
    name: string;
    display_name: string;
    model_type: 'genre';
    image: string;
}
