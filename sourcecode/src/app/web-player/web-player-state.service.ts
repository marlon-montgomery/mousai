import {ElementRef, Injectable} from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class WebPlayerState {
    public loading = false;
    public isMobile = false;
    public scrollContainer: ElementRef<HTMLElement>;

    constructor() {
       this.isMobile = window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
    }
}
