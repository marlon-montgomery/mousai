import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {ControlValueAccessor, FormControl, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {Tag} from '@common/core/types/models/Tag';
import {TagsService} from '@common/core/services/tags.service';
import {slugifyString} from '@common/core/utils/slugify-string';
import {
    catchError,
    debounceTime,
    distinctUntilChanged,
    map,
    switchMap,
} from 'rxjs/operators';

@Component({
    selector: 'tags-manager',
    templateUrl: './tags-manager.component.html',
    styleUrls: ['./tags-manager.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: TagsManagerComponent,
            multi: true,
        },
    ],
})
export class TagsManagerComponent implements OnInit, ControlValueAccessor {
    @Input() public readonly = false;
    @Input() public tagType: string;
    @Input() public pluralName = 'tags';

    formControl = new FormControl();
    selectedTags$ = new BehaviorSubject<string[]>([]);
    existingTags$ = new BehaviorSubject<Tag[]>([]);
    suggestedTags$ = new BehaviorSubject<Tag[]>([]);

    private propagateChange: (tags: string[]) => void;

    constructor(private tagService: TagsService) {}

    ngOnInit() {
        this.tagService.index({perPage: 15, type: this.tagType}).subscribe(response => {
            this.existingTags$.next(
                response.pagination.data.filter(tag => tag.type !== 'status')
            );
        });

        this.formControl.valueChanges
            .pipe(
                debounceTime(250),
                distinctUntilChanged(),
                switchMap(query => this.searchTags(query)),
                catchError(() => of([]))
            )
            .subscribe(tags => {
                this.suggestedTags$.next(tags);
            });
    }

    writeValue(value: string[]) {
        this.selectTags(value, {skipPropagate: true, override: true});
    }

    registerOnChange(fn: (tags: string[]) => void) {
        this.propagateChange = fn;
    }

    registerOnTouched() {}

    selectTags(
        tags?: string[],
        options: {skipPropagate?: boolean; override?: boolean} = {}
    ) {
        const newTags = (tags || [])
            .map(t => t.trim())
            .filter(t => !this.selectedTags$.value.includes(t));
        if (options.override) {
            this.selectedTags$.next(newTags);
        } else if (newTags.length) {
            this.selectedTags$.next([...this.selectedTags$.value, ...newTags]);
        }
        this.formControl.reset();
        if (!options.skipPropagate) {
            this.propagateChange(this.selectedTags$.value);
        }
    }

    deselectTag(tagName: string) {
        const selectedTags = this.selectedTags$.value.slice();
        selectedTags.splice(selectedTags.indexOf(tagName), 1);
        this.selectedTags$.next(selectedTags);
        this.propagateChange(this.selectedTags$.value);
    }

    selectTagsFromString(tagString: string) {
        const tags = tagString.split(',').map(t => slugifyString(t));
        this.selectTags(tags);
    }

    private searchTags(query: string): Observable<Tag[]> {
        if (!query) {
            return of([]);
        }
        return this.tagService.index({query}).pipe(
            map(response => {
                return response.pagination.data;
            })
        );
    }
}
