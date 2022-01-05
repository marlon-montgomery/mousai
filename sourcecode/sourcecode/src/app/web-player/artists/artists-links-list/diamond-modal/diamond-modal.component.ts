import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    Inject,
    ViewEncapsulation
} from '@angular/core';
import {NodeService} from '@common/node.service';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {BitcloutService} from '@common/auth/bitclout.service';
import {NormalizedArtist} from '../artists-links-list.component';
import {Toast} from '@common/core/ui/toast.service';
import {AppHttpClient} from '@common/core/http/app-http-client.service';

type Artist = {
    URL: string,
    Name: string,
    Username: string
};

@Component({
    selector: 'diamond-modal',
    templateUrl: './diamond-modal.component.html',
    styleUrls: ['./diamond-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class DiamondModalComponent {
    initialized = false;
    sendingDiamond = false;
    selectedIndex = 0;

    diamondCount = 6;
    diamondIndexes = Array<number>(this.diamondCount).fill(0).map((v, i) => i);

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: { artist: NormalizedArtist },
        protected toast: Toast,
        protected http: AppHttpClient,
        protected cdr: ChangeDetectorRef,
        protected nodeService: NodeService,
        protected dialogRef: MatDialogRef<DiamondModalComponent>
    ) {
        nodeService.makeSureServiceIsInitialized().then(() => {
            this.initialized = true;
            this.cdr.detectChanges();
        });
    }

    get Artist(): Artist {
        const {meta: {bitclout: username}, name: name} = this.data.artist;

        return {
            URL: this.nodeService.nodeUrl(`u/${username}?tab=posts`),
            Name: name,
            Username: username
        };
    }

    get SendingDiamond(): boolean {
        return this.sendingDiamond;
    }

    set SendingDiamond(value: boolean) {
        this.dialogRef.disableClose = value;
        this.sendingDiamond = value;

        this.cdr.detectChanges();
    }

    get CanCancel(): boolean {
        return this.SendingDiamond === false;
    }

    get CanSend(): boolean {
        return this.CanCancel;
    }

    public valueOf(index: number) {
        return this.nodeService.getUSDForDiamond(index + 1);
    }

    public select(value: number) {
        if (this.SendingDiamond) {
            return;
        }

        this.selectedIndex = value;
    }

    public onCancel() {
        this.dialogRef.close(false);
    }

    public async onSend() {
        try {
            this.SendingDiamond = true;

            const nanosForIndex = this.nodeService.getNanosForDiamond(this.selectedIndex + 1);

            await this.nodeService.sendDeso({
                senderPublicKeyBase58Check: BitcloutService.CurrentUserPublicKey,
                recipientPublicKeyOrUsername: this.Artist.Username,
                amountNanos: nanosForIndex,
            });

            this.dialogRef.close(true);
        } catch (error) {
            this.SendingDiamond = false;

            if (typeof error === 'object' && error.hasOwnProperty('status') && error.status === 400) {
                const message = 'Cannot execute your order. Please make sure you have sufficient balance for this transaction.';
                this.toast.open(message, {duration: 15000});
            }
        }
    }
}
