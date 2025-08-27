<?php

namespace MichaelCrowcroft\GoogleSearchConsole\Services;

use Google\Service\Webmasters;

/**
 * Sites service for Google Search Console
 *
 * This class provides methods to list verified sites.
 * Adding and removing sites is not implemented as per requirements.
 */
class Sites
{
    protected Webmasters $webmasters;

    /**
     * Create a new Sites instance
     *
     * @param Webmasters $webmasters
     */
    public function __construct(Webmasters $webmasters)
    {
        $this->webmasters = $webmasters;
    }

    /**
     * List all verified sites
     *
     * @return array Array of sites with 'site_url' and 'permission_level' fields
     *
     * @throws \Google\Service\Exception
     */
    public function list(): array
    {
        try {
            $response = $this->webmasters->sites->listSites();

            return $this->formatSitesResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Get details about a specific site
     *
     * @param string $siteUrl The site's URL (e.g., 'https://www.example.com/')
     * @return array|null Array with 'site_url' and 'permission_level' fields, or null if not found
     *
     * @throws \Google\Service\Exception
     */
    public function get(string $siteUrl): ?array
    {
        try {
            $response = $this->webmasters->sites->get($siteUrl);

            return $this->formatSiteResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Check if a site is verified
     *
     * @param string $siteUrl The site's URL
     * @return bool
     */
    public function isVerified(string $siteUrl): bool
    {
        try {
            $site = $this->get($siteUrl);
            return $site !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all verified sites with their details
     *
     * @return array Array of sites with 'site_url' and 'permission_level' fields
     */
    public function getAllWithDetails(): array
    {
        $sites = $this->list();

        $detailedSites = [];
        foreach ($sites as $site) {
            $details = $this->get($site['site_url']);
            if ($details) {
                $detailedSites[] = $details;
            }
        }

        return $detailedSites;
    }

    /**
     * Get sites by type
     *
     * Note: The Google Search Console API no longer provides site type information.
     * This method is deprecated and will return all sites.
     *
     * @param string $type Site type ('SITE' or 'DOMAIN')
     * @return array
     * @deprecated Site type information is no longer available from the API
     */
    public function getByType(string $type): array
    {
        // Since the API no longer provides type information, return all sites
        return $this->list();
    }

    /**
     * Get domain properties (sites with type 'DOMAIN')
     *
     * Note: The Google Search Console API no longer provides site type information.
     * This method will return all sites.
     *
     * @return array
     * @deprecated Site type information is no longer available from the API
     */
    public function getDomainProperties(): array
    {
        return $this->list();
    }

    /**
     * Get regular site properties (sites with type 'SITE')
     *
     * Note: The Google Search Console API no longer provides site type information.
     * This method will return all sites.
     *
     * @return array
     * @deprecated Site type information is no longer available from the API
     */
    public function getSiteProperties(): array
    {
        return $this->list();
    }

    /**
     * Format sites list response
     *
     * @param mixed $response
     * @return array
     */
    protected function formatSitesResponse($response): array
    {
        $result = [];

        if ($response && $response->getSiteEntry()) {
            foreach ($response->getSiteEntry() as $site) {
                $result[] = $this->formatSiteResponse($site);
            }
        }

        return $result;
    }

    /**
     * Format single site response
     *
     * @param mixed $site
     * @return array
     */
    protected function formatSiteResponse($site): array
    {
        return [
            'site_url' => $site->getSiteUrl(),
            'permission_level' => $site->getPermissionLevel(),
        ];
    }
}
