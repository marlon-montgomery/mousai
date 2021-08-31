import {Track} from "./Track";

export class Lyric {
    id: number;
    text: string;
    track_id: number;
    track?: Track;
    updated_at: string;

    constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}
