<?php

namespace App\Actions\Backstage;

use App\BackstageRequest;
use App\Notifications\BackstageRequestWasHandled;
use Common\Auth\Roles\Role;
use Exception;

class ApproveBackstageRequest
{
    public function execute(BackstageRequest $backstageRequest, array $actionParams): BackstageRequest
    {
        if ($backstageRequest->type === 'become-artist') {
            $this->approveBecomeArtistRequest($backstageRequest, $actionParams);
        } else if ($backstageRequest->type === 'verify-artist') {
            $backstageRequest->artist()->update(['verified' => true]);
        } else if ($backstageRequest->type === 'claim-artist') {
            $this->approveClaimArtistRequest($backstageRequest);
        }

        $backstageRequest->fill(['status' => 'approved'])->save();
        $backstageRequest->user->notify(new BackstageRequestWasHandled($backstageRequest, $actionParams['notes'] ?? null));
        return $backstageRequest;
    }

    private function approveBecomeArtistRequest(BackstageRequest $backstageRequest, array $actionParams)
    {
        if ($backstageRequest->user->primaryArtist()) {
            throw new BackstageApprovalException('This user already has an artist profile.');
        }
        $params = $backstageRequest->toArray();
        $params['verified'] = $actionParams['markArtistAsVerified'] ?? false;
        $artist = $backstageRequest->user->getOrCreateArtist($params);
        $this->attachArtistRoleToUser($backstageRequest);
        $backstageRequest->artist_id = $artist->id;
    }

    private function approveClaimArtistRequest(BackstageRequest $backstageRequest)
    {
        if ($backstageRequest->user->artists()->where('artist_id', $backstageRequest->artist_id)->exists()) {
            throw new BackstageApprovalException('This user is already attached to this artist.');
        }
        $backstageRequest->user->artists()->attach(
            $backstageRequest->artist_id,
            ['role' => $backstageRequest->data['role'] ?? 'artist']
        );
        $this->attachArtistRoleToUser($backstageRequest);
    }

    private function attachArtistRoleToUser(BackstageRequest $backstageRequest)
    {
        $artistRole = Role::where('artists', true)->first();
        if ( ! $backstageRequest->user->roles()->where('artists', true)->exists()) {
            $backstageRequest->user->roles()->attach($artistRole);
        }
    }
}

class BackstageApprovalException extends Exception {}
