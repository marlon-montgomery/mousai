import {
    AfterViewInit,
    ChangeDetectorRef,
    Component,
    ElementRef,
    HostBinding,
    Input,
    ViewChild,
    ViewEncapsulation
} from '@angular/core';
import {BehaviorSubject} from 'rxjs';

@Component({
    selector: 'media-grid',
    templateUrl: './media-grid.component.html',
    styleUrls: ['./media-grid.component.scss', './media-grid-item.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class MediaGridComponent implements AfterViewInit {
    @Input() @HostBinding('class.carousel') carousel: boolean;
    @ViewChild('gridContentEl') gridContentEl: ElementRef<HTMLElement>;

    private currentPage = 0;
    private pageWidth: number;
    public hasNext$ = new BehaviorSubject<boolean>(false);
    public hasPrevious$ = new BehaviorSubject<boolean>(false);
    private pages = 0;

    constructor(
        private el: ElementRef<HTMLElement>,
        private cd: ChangeDetectorRef,
    ) {}

    ngAfterViewInit() {
        if (this.carousel) {
            const gridItems = this.el.nativeElement.querySelectorAll('.media-grid-item');
            if (gridItems.length) {
                const fullWidth = Math.round(gridItems[0].getBoundingClientRect().width * gridItems.length);
                this.pageWidth = this.el.nativeElement.getBoundingClientRect().width;
                this.pages = fullWidth / this.pageWidth;
            }
            this.setNextPrev();
        }
    }

    public slidePrevious() {
        if ( ! this.hasPrevious$.value) return;
        this.currentPage--;
        const width = this.currentPage * this.pageWidth;
        this.gridContentEl.nativeElement.style.transform = `translateX(-${width}px)`;
        this.setNextPrev();
    }

    public slideNext() {
        if ( ! this.hasNext$.value) return;
        this.currentPage++;
        const width = this.currentPage * this.pageWidth;
        this.gridContentEl.nativeElement.style.transform = `translateX(-${width}px)`;
        this.setNextPrev();
    }

    private setNextPrev() {
        this.hasNext$.next(this.pages > (this.currentPage + 1));
        this.hasPrevious$.next(this.currentPage >= 1);
        this.cd.detectChanges();
    }
}
