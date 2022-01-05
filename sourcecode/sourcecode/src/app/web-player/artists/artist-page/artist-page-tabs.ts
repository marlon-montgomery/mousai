export interface ArtistPageTabConfig {
    name: string;
    queryParam: string;
    description: string;
}

export const ARTIST_PAGE_TABS: {[key: number]: ArtistPageTabConfig} = {
    1: {name: 'Discography', queryParam: 'discography', description: 'Shows all artist albums in grid or list view as well as top tracks.'},
    2: {name: 'Similar Artists', queryParam: 'similar', description: 'Shows similar artists.'},
    3: {name: 'About', queryParam: 'about', description: 'Shows artist biography/description as well as extra images'},
    4: {name: 'Tracks', queryParam: 'tracks', description: 'Shows all artist tracks in a list view.'},
    5: {name: 'Albums', queryParam: 'albums', description: 'Shows all artist albums in a list view.'},
    6: { name: 'Followers', queryParam: 'followers', description: 'Shows all users that are currently following an artist.'},
};
