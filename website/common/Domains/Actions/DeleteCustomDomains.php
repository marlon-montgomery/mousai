<?php


namespace Common\Domains\Actions;


use Common\Domains\CustomDomain;
use Common\Domains\DeletedCustomDomains;
use Common\Settings\Settings;

class DeleteCustomDomains
{
    /**
     * @var CustomDomain
     */
    private $customDomain;

    /**
     * @param CustomDomain $customDomain
     */
    public function __construct(CustomDomain $customDomain)
    {
        $this->customDomain = $customDomain;
    }

    /**
     * @param int[] $domainIds
     */
    public function execute($domainIds)
    {
        $hosts = $this->customDomain->whereIn('id', $domainIds)->pluck('host');

        // unset default host, if matching custom_domain is removed
        $defaultHost = app(Settings::class)->get('custom_domains.default_host');
        if ($defaultHost && $hosts->contains($defaultHost)) {
            app(Settings::class)->save(['custom_domains.default_host' => null]);
        }

        $this->customDomain->whereIn('id', $domainIds)->delete();

        event(new DeletedCustomDomains($domainIds));
    }
}
