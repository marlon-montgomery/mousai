import {
    ChangeDetectionStrategy,
    Component,
    OnDestroy,
    OnInit,
} from '@angular/core';
import {FontConfig} from '@common/core/services/value-lists.service';
import {BehaviorSubject, combineLatest, Observable, Subscription} from 'rxjs';
import {map} from 'rxjs/operators';
import {
    ControlValueAccessor,
    FormControl,
    FormGroup,
    NG_VALUE_ACCESSOR,
} from '@angular/forms';
import {filterDatatableData} from '@common/datatable/utils/filter-datatable-data';
import {GoogleFontService} from '@common/shared/form-controls/google-font-selector/google-font.service';
import {BROWSER_SAFE_FONTS} from '@common/shared/form-controls/google-font-selector/browser-safe-fonts';

type propagateFn = (value: {family: string}) => void;

export interface GoogleFontSelectorValue {
    family: string;
}

@Component({
    selector: 'google-font-selector',
    templateUrl: './google-font-selector.component.html',
    styleUrls: ['./google-font-selector.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: GoogleFontSelectorComponent,
            multi: true,
        },
    ],
})
export class GoogleFontSelectorComponent
    implements OnInit, OnDestroy, ControlValueAccessor
{
    activePage$ = new BehaviorSubject<number>(0);
    chunkedFonts$ = new BehaviorSubject<FontConfig[][]>(null);
    selectedFamily$ = new BehaviorSubject<string>(null);

    filters = new FormGroup({
        query: new FormControl(),
        category: new FormControl(null),
    });

    from$ = this.activePage$.pipe(map(p => p * this.perPage + 1));
    total$ = new BehaviorSubject<number>(1052);
    to$ = this.from$.pipe(
        map(from => Math.min(from + this.perPage - 1), this.total$.value)
    );

    fonts$: Observable<FontConfig[]> = combineLatest(
        this.activePage$,
        this.chunkedFonts$
    ).pipe(
        map(([page, chunkedFonts]) => {
            return chunkedFonts ? chunkedFonts[page] : [];
        })
    );

    private allFonts: FontConfig[];
    private perPage = 20;
    private fontSub: Subscription;
    private formSub: Subscription;
    private propagateChange: propagateFn;

    constructor(private googleFonts: GoogleFontService) {}

    ngOnInit() {
        this.allFonts = [...BROWSER_SAFE_FONTS];
        this.googleFonts.getAll().subscribe(fonts => {
            this.allFonts = [...this.allFonts, ...fonts];
            this.chunkFonts(this.allFonts);
        });

        this.fontSub = this.fonts$.subscribe(fonts => {
            this.loadIntoDom(fonts);
        });

        this.formSub = this.filters.valueChanges.subscribe(val => {
            let fonts = val.query?.length
                ? filterDatatableData(this.allFonts, val.query)
                : this.allFonts;
            fonts = val.category
                ? fonts.filter(
                      f =>
                          f.category.toLowerCase() ===
                          val.category.toLowerCase()
                  )
                : fonts;
            this.activePage$.next(0);
            this.chunkFonts(fonts);
        });
    }

    ngOnDestroy() {
        this.formSub.unsubscribe();
        this.fontSub.unsubscribe();
    }

    selectFamily(family: string) {
        this.selectedFamily$.next(family);
        this.propagateChange({family});
    }

    haveNext(): boolean {
        return this.chunkedFonts$.value?.length > this.activePage$.value + 1;
    }

    havePrev(): boolean {
        return this.activePage$.value > 0;
    }

    nextPage() {
        this.activePage$.next(this.activePage$.value + 1);
    }

    prevPage() {
        this.activePage$.next(this.activePage$.value - 1);
    }

    registerOnChange(fn: propagateFn) {
        this.propagateChange = fn;
    }

    writeValue(value: {family: string}) {
        this.selectedFamily$.next(value?.family);
    }

    registerOnTouched(fn: any) {}

    private chunkFonts(fonts: FontConfig[]) {
        const chunkSize = this.perPage;
        const chunked = [];
        for (let i = 0, len = fonts.length; i < len; i += chunkSize) {
            chunked.push(fonts.slice(i, i + chunkSize));
        }
        this.chunkedFonts$.next(chunked);
        this.total$.next(fonts.length);
    }

    private loadIntoDom(fonts: FontConfig[]) {
        this.googleFonts.loadIntoDom(fonts, 'google-font-selector');
    }
}
