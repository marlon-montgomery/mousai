import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {MenuEditor} from '@common/admin/appearance/panels/menus-appearance-panel/menus/menu-editor.service';
import {AppearanceEditor} from '@common/admin/appearance/appearance-editor/appearance-editor.service';
import {FormBuilder} from '@angular/forms';
import {MenuItemCategory} from '@common/admin/appearance/panels/menus-appearance-panel/menus/item-categories/menu-item-category';
import {MenuItemCategoriesService} from '@common/admin/appearance/panels/menus-appearance-panel/menus/item-categories/menu-item-categories.service';
import {MenuItem} from '@common/core/ui/custom-menu/menu-item';

@Component({
    selector: 'select-menu-item-action',
    templateUrl: './select-menu-item-action.component.html',
    styleUrls: ['./select-menu-item-action.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SelectMenuItemActionComponent implements OnInit {
    public linkForm = this.fb.group({
        action: [''],
        label: [''],
    });
    public menuItemCategories: MenuItemCategory[];

    constructor(
        public editor: MenuEditor,
        public appearance: AppearanceEditor,
        private overlayPanelRef: OverlayPanelRef,
        private fb: FormBuilder,
        private itemCategories: MenuItemCategoriesService
    ) {}

    ngOnInit() {
        this.itemCategories.get().subscribe(response => {
            this.menuItemCategories = response.categories;
        });
    }

    addLinkMenuItem() {
        this.close({
            type: 'link',
            label: this.linkForm.value.label,
            action: this.linkForm.value.action,
        });
    }

    addRouteMenuItem(route: string) {
        this.close({
            type: 'route',
            label: route,
            action: route,
        });
    }

    addCustomMenuItem(item: Partial<MenuItem>) {
        this.close(item);
    }

    close(destination?: Partial<MenuItem>) {
        this.overlayPanelRef.close(destination);
    }
}
