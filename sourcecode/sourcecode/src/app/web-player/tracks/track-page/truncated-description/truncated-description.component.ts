import {ChangeDetectionStrategy, Component, ElementRef, Input, OnChanges, OnInit, ViewChild} from '@angular/core';
import linkifyStr from 'linkifyjs/string';
import {BehaviorSubject} from 'rxjs';
import {delay} from 'rxjs/operators';

@Component({
    selector: 'truncated-description',
    templateUrl: './truncated-description.component.html',
    styleUrls: ['./truncated-description.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class TruncatedDescriptionComponent implements OnChanges, OnInit {
    @Input() content: string;
    @ViewChild('contentEl', {static: true}) contentEl: ElementRef<HTMLElement>;
    @ViewChild('contentWrapperEl', {static: true}) contentWrapperEl: ElementRef<HTMLElement>;
    public linkifiedContent$ = new BehaviorSubject<string>('');
    public moreButtonVisible$ = new BehaviorSubject<boolean>(false);
    public showingAll$ = new BehaviorSubject<boolean>(false);

    public ngOnInit(): void {
        this.linkifiedContent$
            .pipe(delay(0))
            .subscribe(() => {
                const contentH = this.contentEl.nativeElement.getBoundingClientRect().height,
                    wrapperH = this.contentWrapperEl.nativeElement.getBoundingClientRect().height;
                this.moreButtonVisible$.next(contentH > wrapperH);
            });
    }

    ngOnChanges() {
        this.linkifiedContent$.next(
            this.content ?
                linkifyStr(this.content, {nl2br: true, attributes: {rel: 'nofollow'}}) :
                ''
        );
    }

    public toggleContent() {
        this.showingAll$.next(!this.showingAll$.value);
    }
}
