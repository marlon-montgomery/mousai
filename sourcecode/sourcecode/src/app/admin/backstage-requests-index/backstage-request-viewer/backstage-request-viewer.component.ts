import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {BackstagRequestService} from '../../../backstage/requests/backstag-request.service';
import {ActivatedRoute, Router} from '@angular/router';
import {BehaviorSubject} from 'rxjs';
import {OverlayPanel} from '@common/core/ui/overlay-panel/overlay-panel.service';
import {ImageGalleryOverlayComponent} from '@common/shared/image-gallery-overlay/image-gallery-overlay.component';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {Toast} from '@common/core/ui/toast.service';
import {finalize} from 'rxjs/operators';
import {BackstageRequest} from '../../../models/backstage-request';
import {
    ConfirmRequestHandledModalComponent,
    ConfirmRequestApprovalResult,
} from './confirm-request-handled-modal/confirm-request-handled-modal.component';
import {WebPlayerUrls} from '../../../web-player/web-player-urls.service';

@Component({
    selector: 'backstage-request-viewer',
    templateUrl: './backstage-request-viewer.component.html',
    styleUrls: ['./backstage-request-viewer.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BackstageRequestViewerComponent implements OnInit {
    public request$ = new BehaviorSubject<Partial<BackstageRequest>>({});
    public loading$ = new BehaviorSubject<boolean>(false);

    constructor(
        private backstage: BackstagRequestService,
        private route: ActivatedRoute,
        private overlay: OverlayPanel,
        private modal: Modal,
        private toast: Toast,
        private router: Router,
        public urls: WebPlayerUrls
    ) {}

    ngOnInit(): void {
        this.route.params.subscribe(params => {
            this.backstage.getRequest(params.requestId).subscribe(response => {
                this.request$.next(response.request);
            });
        });
    }

    public openPassportScanModal() {
        this.overlay.open(ImageGalleryOverlayComponent, {
            origin: 'global',
            position: 'center',
            panelClass: 'image-gallery-overlay-container',
            backdropClass: 'image-gallery-overlay-backdrop',
            hasBackdrop: true,
            data: {
                images: [this.request$.value.data.passportScanEntry],
            },
        });
    }

    public handleRequest(type: 'approve' | 'deny') {
        this.modal
            .show(ConfirmRequestHandledModalComponent, {
                request: this.request$.value,
                type,
            })
            .afterClosed()
            .subscribe((r: ConfirmRequestApprovalResult) => {
                if (!r.confirmed) return;
                this.loading$.next(true);
                const request =
                    type === 'approve'
                        ? this.backstage.approveRequest(
                              this.request$.value.id,
                              {
                                  markArtistAsVerified: r.verifyArtist,
                                  notes: r.notes,
                              }
                          )
                        : this.backstage.denyRequest(this.request$.value.id, {
                              notes: r.notes,
                          });
                request
                    .pipe(finalize(() => this.loading$.next(false)))
                    .subscribe(() => {
                        this.router.navigate(['/admin/backstage-requests']);
                        this.toast.open(
                            'Request ' +
                                (type === 'approve' ? 'approved' : 'denied')
                        );
                    });
            });
    }

    public deleteRequest() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Approve Request',
            body: 'Are you sure you want to delete this request?',
            ok: 'Delete'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            this.loading$.next(true);
            this.backstage.deleteRequests([this.request$.value.id])
                .pipe(finalize(() => this.loading$.next(false)))
                .subscribe(() => {
                    this.toast.open('Request has been deleted');
                    this.router.navigate(['/admin/backstage-requests']);
                });
        });
    }
}
