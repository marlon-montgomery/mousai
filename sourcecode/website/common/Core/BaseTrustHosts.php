<?php

namespace Common\Core;

use Common\Domains\CustomDomainController;
use Illuminate\Http\Middleware\TrustHosts as Middleware;

class BaseTrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    public function hosts()
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }

    protected function shouldSpecifyTrustedHosts()
    {
        // allow custom domain validation
        if (request()->path() === CustomDomainController::VALIDATE_CUSTOM_DOMAIN_PATH) {
            return false;
        } else {
            return parent::shouldSpecifyTrustedHosts();
        }
    }
}
