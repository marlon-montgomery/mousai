import {Component, Input} from '@angular/core';
import {Track} from '../../../../models/Track';
import {UserLibrary} from '../user-library.service';

@Component({
    selector: 'library-track-toggle-button',
    templateUrl: './library-track-toggle-button.component.html',
    styleUrls: ['./library-track-toggle-button.component.scss'],
})
export class LibraryTrackToggleButtonComponent {
    @Input() track: Track;

    constructor(public library: UserLibrary) {}

    public toggle(track: Track) {
        if (this.library.has(track)) {
            this.library.add([track]);
        } else {
            this.library.remove([track]);
        }
    }
}
