import {ChangeDetectionStrategy, Component, OnInit, ViewChild} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {Track} from '../../../models/Track';
import {ComponentCanDeactivate} from '@common/guards/pending-changes/component-can-deactivate';
import {AlbumFormComponent} from '../../../uploading/album-form/album-form.component';
import {TrackFormComponent} from '../../../uploading/track-form/track-form.component';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'crupdate-track-page',
    templateUrl: './crupdate-track-page.component.html',
    styleUrls: ['./crupdate-track-page.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrupdateTrackPageComponent implements OnInit, ComponentCanDeactivate {
    @ViewChild(TrackFormComponent, {static: true}) trackForm: AlbumFormComponent;
    public track: Track;

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        public settings: Settings,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            if (data.api) {
                this.track = data.api.track;
            }
        });
    }

    public toTracksPage() {
        if (this.insideAdmin()) {
            this.router.navigate(['/admin/tracks']);
        } else {
            this.router.navigate(['/']);
        }
    }

    public canDeactivate() {
        return !this.trackForm.form.dirty;
    }

    public insideAdmin(): boolean {
        return this.router.url.includes('admin');
    }
}
