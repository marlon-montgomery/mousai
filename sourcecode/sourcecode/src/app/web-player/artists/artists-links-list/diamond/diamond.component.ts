import {Router} from '@angular/router';
import {MatDialog} from '@angular/material/dialog';
import {
    ChangeDetectionStrategy,
    Component,
    Input,
    ViewEncapsulation
} from '@angular/core';

import {Toast} from '@common/core/ui/toast.service';
import {CurrentUser} from '@common/auth/current-user';
import {AuthService} from '@common/auth/auth.service';
import {BitcloutService} from '@common/auth/bitclout.service';

import {NormalizedArtist} from '../artists-links-list.component';
import {DiamondModalComponent} from '../diamond-modal/diamond-modal.component';
import {CelebrateService} from '@common/celebrate.service';

@Component({
    selector: 'diamond',
    templateUrl: './diamond.component.html',
    styleUrls: ['./diamond.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class DiamondComponent {
    @Input() artist: NormalizedArtist;

    constructor(
        protected router: Router,
        protected authService: AuthService,
        protected currentUser: CurrentUser,
        protected toast: Toast,
        protected dialog: MatDialog,
        protected celebrateService: CelebrateService) {
    }

    onAction(): any {
        if (this.currentUser) {
            return this.authService.logOut();
        }

        return this.router.navigate(['/login']);
    }

    protected openModal() {
        const data = {artist: this.artist};
        const dialogRef = this.dialog.open(DiamondModalComponent, {data});
        const afterCloseSubscription = dialogRef.afterClosed().subscribe((value: boolean) => {
            if (value) {
                this.celebrateService.rain().diamond();
            }
            afterCloseSubscription.unsubscribe();
        });
    }

    protected throwError() {
        const matSnackBarRef = this.toast.open('To send a diamond, you need to login with DeSo.', {action: 'Login'});

        const onActionSubscription = matSnackBarRef.onAction().subscribe(() => this.onAction());
        const afterDismissedSubscription = matSnackBarRef.afterDismissed().subscribe(() => {
            onActionSubscription.unsubscribe();
            afterDismissedSubscription.unsubscribe();
        });
    }

    onClick(event: MouseEvent) {
        if (this.currentUser.isLoggedIn() && BitcloutService.CurrentUser) {
            this.openModal();
        } else {
            this.throwError();
        }

        event.preventDefault();
        return false;
    }
}
