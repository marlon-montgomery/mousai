import {ComponentType, OverlayRef} from '@angular/cdk/overlay';
import {BehaviorSubject, Observable} from 'rxjs';
import {map, skip, take} from 'rxjs/operators';
import {randomString} from '@common/core/utils/random-string';
import {ComponentRef} from '@angular/core';

export class OverlayPanelRef<T = ComponentType<any>, V = any> {
    public id: string = randomString(15);
    private value = new BehaviorSubject<V>(null);
    public componentRef: ComponentRef<T>;

    constructor(public overlayRef: OverlayRef) {}

    public isOpen(): boolean {
        return this.overlayRef && this.overlayRef.hasAttached();
    }

    public close(value?: V) {
        if (typeof value !== 'undefined') {
            this.emitValue(value);
        }
        if (this.overlayRef) {
            this.overlayRef.dispose();
        }
    }

    public emitValue(value: V) {
        this.value.next(value);
    }

    public valueChanged(): Observable<V> {
        return this.value.pipe(skip(1));
    }

    public getPanelEl() {
        return this.overlayRef.overlayElement;
    }

    public updatePosition() {
        return this.overlayRef.updatePosition();
    }

    public afterClosed() {
        return this.overlayRef.detachments().pipe(
            take(1),
            map(() => this.value.value)
        );
    }

    public afterOpened() {
        return this.overlayRef.attachments().pipe(take(1));
    }
}
