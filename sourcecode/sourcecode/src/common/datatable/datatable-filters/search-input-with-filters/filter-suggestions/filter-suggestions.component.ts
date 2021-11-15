import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    EventEmitter,
    Input,
    Output,
    QueryList,
    ViewChildren,
} from '@angular/core';
import {DatatableFilter} from '../filter-config/datatable-filter';

const ITEM_CLASS = 'filter-suggestion-item';

@Component({
    selector: 'filter-suggestions',
    templateUrl: './filter-suggestions.component.html',
    styleUrls: ['./filter-suggestions.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FilterSuggestionsComponent {
    @Input() filters: {[key: string]: DatatableFilter};
    @Output() filterSelected = new EventEmitter<DatatableFilter>();
    @ViewChildren('filterSuggestion') items: QueryList<ElementRef<HTMLElement>>;

    anyFocused(): boolean {
        return document.activeElement.classList.contains(ITEM_CLASS);
    }

    focusItemAt(index: number) {
        this.items.get(index).nativeElement.focus();
    }

    getFocusedFilter(): DatatableFilter {
        if (this.anyFocused()) {
            const k = (document.activeElement as HTMLElement).dataset.filterKey;
            return this.filters[k];
        }
    }

    getFocusedIndex(): number {
        if (this.anyFocused()) {
            const el = document.activeElement as HTMLElement;
            return parseInt(el.dataset.index);
        }
    }

    originalOrder = (): number => {
        return 0;
    };
}
