import {Component, Inject, Optional, ViewEncapsulation} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

export interface LyricsModalData {
    lyrics: string;
}

@Component({
    selector: 'lyrics-modal',
    templateUrl: './lyrics-modal.component.html',
    styleUrls: ['./lyrics-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class LyricsModalComponent {
    public lyrics: string;

    constructor(
        private dialogRef: MatDialogRef<LyricsModalComponent>,
        @Optional() @Inject(MAT_DIALOG_DATA) public data: LyricsModalData,
    ) {
        this.lyrics = data.lyrics;
    }
}
