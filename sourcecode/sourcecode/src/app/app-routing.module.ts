import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {ContactComponent} from '@common/contact/contact.component';
import {AuthGuard} from '@common/guards/auth-guard.service';
import {BackstageHostComponent} from './web-player/backstage-host/backstage-host.component';

const routes: Routes = [
    {path: 'admin', loadChildren: () => import('src/app/admin/app-admin.module').then(m => m.AppAdminModule), canLoad: [AuthGuard]},
    {path: 'billing', loadChildren: () => import('@common/billing/billing.module').then(m => m.BillingModule)},
    {path: 'notifications', loadChildren: () => import('@common/notifications/notifications.module').then(m => m.NotificationsModule)},
    {
        path: 'backstage',
        component: BackstageHostComponent,
        canLoad: [AuthGuard],
        loadChildren: () => import('src/app/backstage/backstage.module').then(m => m.BackstageModule),
    },
    {path: 'api-docs', loadChildren: () => import('@common/api-docs/api-docs.module').then(m => m.ApiDocsModule)},
    {path: 'contact', component: ContactComponent},
    {path: 'pricing', redirectTo: 'billing/pricing'},
];

@NgModule({
    imports: [RouterModule.forRoot(routes, { relativeLinkResolution: 'legacy' })],
    exports: [RouterModule]
})
export class AppRoutingModule {
}
