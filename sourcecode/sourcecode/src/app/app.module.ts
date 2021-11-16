import {NgModule} from '@angular/core';
import {AppComponent} from './app.component';
import {MatButtonModule} from '@angular/material/button';
import {MatMenuModule} from '@angular/material/menu';
import {BEMUSIC_CONFIG} from './bemusic-config';
import {APP_CONFIG} from '@common/core/config/app-config';
import {BrowserModule} from '@angular/platform-browser';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {RouteReuseStrategy, RouterModule} from '@angular/router';
import {AuthModule} from '@common/auth/auth.module';
import {AppRoutingModule} from './app-routing.module';
import {WebPlayerModule} from './web-player/web-player.module';
import {AccountSettingsModule} from '@common/account-settings/account-settings.module';
import {Bootstrapper} from '@common/core/bootstrapper.service';
import {BeMusicBootstrapper} from './bootstrapper.service';
import {CustomRouteReuseStrategy} from '@common/shared/custom-route-reuse-strategy';
import {CORE_PROVIDERS} from '@common/core/core-providers';
import {HttpClientModule} from '@angular/common/http';
import {CommonModule} from '@angular/common';
import {MatSnackBarModule} from '@angular/material/snack-bar';
import {ContactPageModule} from '@common/contact/contact-page.module';
// noinspection ES6UnusedImports
import * as User from 'src/app/models/App-User';
import {CookieNoticeModule} from '@common/gdpr/cookie-notice/cookie-notice.module';
import {PagesModule} from '@common/pages/shared/pages.module';
import {CurrentUser} from '@common/auth/current-user';
import {AppCurrentUser} from './app-current-user';

@NgModule({
    declarations: [
        AppComponent,
    ],
    imports: [
        CommonModule,
        BrowserModule,
        BrowserAnimationsModule,
        HttpClientModule,
        RouterModule.forRoot([], { scrollPositionRestoration: 'top', relativeLinkResolution: 'legacy' }),
        AuthModule,
        CookieNoticeModule,

        AppRoutingModule,
        WebPlayerModule,
        // account settings and pages modules must come after web player
        // module for proper account settings and custom page route override
        AccountSettingsModule,
        PagesModule,
        ContactPageModule,

        // material
        MatMenuModule,
        MatButtonModule,
        MatSnackBarModule,
    ],
    providers: [
        ...CORE_PROVIDERS,
        {provide: APP_CONFIG, useValue: BEMUSIC_CONFIG, multi: true},
        {provide: Bootstrapper, useClass: BeMusicBootstrapper},
        {provide: CurrentUser, useExisting: AppCurrentUser},
        {provide: RouteReuseStrategy, useClass: CustomRouteReuseStrategy},
    ],
    bootstrap: [AppComponent]
})
export class AppModule {
}
