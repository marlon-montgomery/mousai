import {NgModule} from '@angular/core';
import {NavSidebarComponent} from './nav-sidebar/nav-sidebar.component';
import {WebPlayerComponent} from './web-player.component';
import {SearchSlideoutPanelComponent} from './search/search-slideout-panel/search-slideout-panel.component';
import {WebPlayerRoutingModule} from './web-player-routing.module';
import {AlbumComponent} from './albums/album/album.component';
import {TrackTableComponent} from './tracks/track-table/track-table.component';
import {QueueSidebarComponent} from './queue-sidebar/queue-sidebar.component';
import {FullscreenOverlayComponent} from './fullscreen-overlay/fullscreen-overlay.component';
import {LibraryTrackToggleButtonComponent} from './users/user-library/library-track-toggle-button/library-track-toggle-button.component';
import {LibraryTracksComponent} from './users/user-library/library-tracks/library-tracks.component';
import {LibraryAlbumsComponent} from './users/user-library/library-albums/library-albums.component';
import {LibraryArtistsComponent} from './users/user-library/library-artists/library-artists.component';
import {AlbumItemComponent} from './albums/album-item/album-item.component';
import {ArtistItemComponent} from './artists/artist-item/artist-item.component';
import {ArtistPageComponent} from './artists/artist-page/artist-page.component';
import {AlbumContextMenuComponent} from './albums/album-context-menu/album-context-menu.component';
import {CrupdatePlaylistModalComponent} from './playlists/crupdate-playlist-modal/crupdate-playlist-modal.component';
import {TrackContextMenuComponent} from './tracks/track-context-menu/track-context-menu.component';
import {ArtistContextMenuComponent} from './artists/artist-context-menu/artist-context-menu.component';
import {ContextMenuPlaylistPanelComponent} from './context-menu/context-menu-playlist-panel/context-menu-playlist-panel.component';
import {PlaylistComponent} from './playlists/playlist/playlist.component';
import {SearchComponent} from './search/search/search.component';
import {UserItemComponent} from './users/user-item/user-item.component';
import {PlaylistTrackContextMenuComponent} from './playlists/playlist-track-context-menu/playlist-track-context-menu.component';
import {PlaylistContextMenuComponent} from './playlists/playlist-context-menu/playlist-context-menu.component';
import {TrackPageComponent} from './tracks/track-page/track-page.component';
import {RadioPageComponent} from './radio-page/radio-page.component';
import {ShareMediaItemModalComponent} from './context-menu/share-media-item-modal/share-media-item-modal.component';
import {LyricsModalComponent} from './lyrics/lyrics-modal/lyrics-modal.component';
import {UserLibraryComponent} from './users/user-library/user-library.component';
import {LibraryPlaylistsComponent} from './users/user-library/library-playlists/library-playlists.component';
import {MatBadgeModule} from '@angular/material/badge';
import {MatChipsModule} from '@angular/material/chips';
import {MatDialogModule} from '@angular/material/dialog';
import {MatSidenavModule} from '@angular/material/sidenav';
import {WEB_PLAYER_CONTEXT_MENUS} from './available-context-menus';
import {DragDropModule} from '@angular/cdk/drag-drop';
import {AVAILABLE_CONTEXT_MENUS} from '@common/core/ui/context-menu/available-context-menus';
import {MediaImageModule} from './shared/media-image/media-image.module';
import {UploadImageControlModule} from '@common/shared/form-controls/upload-image-control/upload-image-control.module';
import {UploadsModule} from '@common/uploads/uploads.module';
import {ChannelShowComponent} from './channels/channel-show/channel-show.component';
import {ChannelContentComponent} from './channels/channel-show/channel-content/channel-content.component';
import {PlayHistoryComponent} from './users/user-library/play-history/play-history.component';
import {TrackItemComponent} from './tracks/track-item/track-item.component';
import {GenreItemComponent} from './genres/genre-item/genre-item.component';
import {TruncatedDescriptionComponent} from './tracks/track-page/truncated-description/truncated-description.component';
import {TrackEmbedComponent} from './tracks/track-embed/track-embed.component';
import {ChannelFallbackHostComponent} from './channels/channel-fallback-host/channel-fallback-host.component';
import {LandingComponent} from './landing/landing.component';
import {CommentListComponent} from './tracks/track-page/comments/comment-list/comment-list.component';
import {CommentListItemComponent} from './tracks/track-page/comments/comment-list-item/comment-list-item.component';
import {WaveformModule} from './tracks/waveform/waveform.module';
import {MediaListItemModule} from './tracks/media-list-item/media-list-item.module';
import {NewCommentFormModule} from './tracks/new-comment-form/new-comment-form.module';
import {PlayerUiModule} from './player-ui.module';
import {TrackActionsBarModule} from './tracks/track-actions-bar/track-actions-bar.module';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatButtonModule} from '@angular/material/button';
import {CommonModule} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
import {FormatPipesModule} from '@common/core/ui/format-pipes/format-pipes.module';
import {AdHostModule} from '@common/core/ui/ad-host/ad-host.module';
import {ContextMenuModule} from '@common/core/ui/context-menu/context-menu.module';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {MatTooltipModule} from '@angular/material/tooltip';
import {MatMenuModule} from '@angular/material/menu';
import {EmptyRouteComponent} from '@common/core/ui/empty-route/empty-route.component';
import {TagMediaPageComponent} from './tags/tag-media-page/tag-media-page.component';
import {MatTabsModule} from '@angular/material/tabs';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {HomepageHostComponent} from './homepage-host/homepage-host.component';
import {PagesModule} from '@common/pages/shared/pages.module';
import {DatatableModule} from '@common/datatable/datatable.module';
import {FullscreenOverlayQueueComponent} from './fullscreen-overlay/fullscreen-overlay-queue/fullscreen-overlay-queue.component';
import {SearchPageTrackListComponent} from './search/search/search-page-track-list/search-page-track-list.component';
import {ArtistPagePopularTracksComponent} from './artists/artist-page/artist-page-popular-tracks/artist-page-popular-tracks.component';
import {ArtistPageAlbumListItemComponent} from './artists/artist-page/artist-page-album-list-item/artist-page-album-list-item.component';
import {SkeletonModule} from '@common/core/ui/skeleton/skeleton.module';
import {NavSidebarUserMenuComponent} from './nav-sidebar/nav-sidebar-user-menu/nav-sidebar-user-menu.component';
import {ArtistPageHeaderComponent} from './artists/artist-page/artist-page-header/artist-page-header.component';
import {ImageGalleryOverlayModule} from '@common/shared/image-gallery-overlay/image-gallery-overlay.module';
import {ProfileDescriptionModule} from './shared/profile-description/profile-description.module';
import {ArtistOverviewTabComponent} from './artists/artist-page/tabs/artist-overview-tab/artist-overview-tab.component';
import {ArtistBioTabComponent} from './artists/artist-page/tabs/artist-bio-tab/artist-bio-tab.component';
import {SimilarArtistsTabComponent} from './artists/artist-page/tabs/similar-artists-tab/similar-artists-tab.component';
import {MatRippleModule} from '@angular/material/core';
import {PaginatedTrackListComponent} from './tracks/paginated-track-list/paginated-track-list.component';
import {PaginatedAlbumListComponent} from './albums/paginated-album-list/paginated-album-list.component';
import {PaginatedFollowersTabComponent} from './artists/artist-page/tabs/paginated-followers-tab/paginated-followers-tab.component';
import {ProfileFollowerInfoModule} from './shared/profile-follower-info/profile-follower-info.module';
import {UserProfilePageComponent} from './users/user-profile-page/user-profile-page.component';
import {EditUserProfileModalComponent} from './users/user-profile-page/edit-user-profile-modal/edit-user-profile-modal.component';
import {PaginatedPlaylistTabComponent} from './playlists/paginated-playlist-tab/paginated-playlist-tab.component';
import {MatSlideToggleModule} from '@angular/material/slide-toggle';
import {PlaylistEditorsWidgetComponent} from './playlists/playlist-editors-widget/playlist-editors-widget.component';
import {ConfirmCommentDeletionPopoverComponent} from './tracks/track-page/comments/confirm-comment-deletion-popover/confirm-comment-deletion-popover.component';
import {ChannelTrackTableComponent} from './channels/channel-show/channel-track-table/channel-track-table.component';
import {ChannelMediaGridComponent} from './channels/channel-show/channel-media-grid/channel-media-grid.component';
import {ProfileLinksFormControlModule} from './shared/profile-links-form-control/profile-links-form-control.module';
import {PaginatedArtistListComponent} from './artists/paginated-artist-list/paginated-artist-list.component';
import {BackstageHostComponent} from './backstage-host/backstage-host.component';
import {ChannelTrackListComponent} from './channels/channel-show/channel-track-list/channel-track-list.component';

@NgModule({
    imports: [
        CommonModule,
        WebPlayerRoutingModule,
        UploadImageControlModule,
        PagesModule,
        WaveformModule,
        MediaListItemModule,
        NewCommentFormModule,
        PlayerUiModule,
        TrackActionsBarModule,
        TranslationsModule,
        FormatPipesModule,
        AdHostModule,
        ContextMenuModule,
        LoadingIndicatorModule,
        FormsModule,
        ReactiveFormsModule,
        NoResultsMessageModule,
        MaterialNavbarModule,
        DatatableModule,
        SkeletonModule,
        ImageGalleryOverlayModule,
        ProfileDescriptionModule,
        ProfileFollowerInfoModule,
        UploadImageControlModule,
        ProfileLinksFormControlModule,

        // material
        MatSidenavModule,
        MatDialogModule,
        DragDropModule,
        MediaImageModule,
        MatChipsModule,
        UploadsModule,
        MatBadgeModule,
        MatButtonModule,
        MatIconModule,
        MatTooltipModule,
        MatMenuModule,
        MatTabsModule,
        MatCheckboxModule,
        MatRippleModule,
        MatSlideToggleModule,
    ],
    declarations: [
        HomepageHostComponent,
        WebPlayerComponent,
        NavSidebarComponent,
        NavSidebarUserMenuComponent,
        SearchSlideoutPanelComponent,
        AlbumComponent,
        PaginatedAlbumListComponent,
        PaginatedArtistListComponent,
        PaginatedFollowersTabComponent,
        TrackTableComponent,
        PaginatedTrackListComponent,
        QueueSidebarComponent,
        FullscreenOverlayComponent,
        FullscreenOverlayQueueComponent,
        LibraryTrackToggleButtonComponent,
        LibraryTracksComponent,
        LibraryAlbumsComponent,
        LibraryArtistsComponent,
        AlbumItemComponent,
        ArtistItemComponent,
        UserItemComponent,
        ArtistPageComponent,
        ArtistPageHeaderComponent,
        ArtistPagePopularTracksComponent,
        ArtistPageAlbumListItemComponent,
        ArtistOverviewTabComponent,
        ArtistBioTabComponent,
        SimilarArtistsTabComponent,
        AlbumContextMenuComponent,
        CrupdatePlaylistModalComponent,
        TrackContextMenuComponent,
        ArtistContextMenuComponent,
        TagMediaPageComponent,
        ContextMenuPlaylistPanelComponent,
        PlaylistComponent,
        SearchComponent,
        SearchPageTrackListComponent,
        UserItemComponent,
        PlaylistTrackContextMenuComponent,
        PlaylistContextMenuComponent,
        RadioPageComponent,
        ShareMediaItemModalComponent,
        LyricsModalComponent,
        UserLibraryComponent,
        LibraryPlaylistsComponent,
        ChannelShowComponent,
        ChannelContentComponent,
        ChannelTrackTableComponent,
        ChannelMediaGridComponent,
        ChannelTrackListComponent,
        PlayHistoryComponent,
        TrackItemComponent,
        GenreItemComponent,
        TruncatedDescriptionComponent,
        TrackEmbedComponent,
        ChannelFallbackHostComponent,
        LandingComponent,
        BackstageHostComponent,
        EmptyRouteComponent,

        UserProfilePageComponent,
        EditUserProfileModalComponent,
        PaginatedPlaylistTabComponent,

        CommentListComponent,
        CommentListItemComponent,
        ConfirmCommentDeletionPopoverComponent,
        TrackPageComponent,
    ],
    exports: [
        PlaylistEditorsWidgetComponent,
    ],
    providers: [
        {
            provide: AVAILABLE_CONTEXT_MENUS,
            useValue: WEB_PLAYER_CONTEXT_MENUS,
            multi: true,
        },
    ]
})
export class WebPlayerModule {}
