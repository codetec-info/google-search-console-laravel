<?php

namespace MichaelCrowcroft\GoogleSearchConsole\Services;

use Google\Service\Webmasters;
use Google\Service\Webmasters\WmxSitemap;

/**
 * Sitemaps service for Google Search Console
 *
 * This class provides methods to manage sitemaps including
 * submitting, deleting, and retrieving sitemap information.
 */
class Sitemaps
{
    protected Webmasters $webmasters;
    protected ?string $defaultSiteUrl;

    /**
     * Create a new Sitemaps instance
     *
     * @param Webmasters $webmasters
     * @param string|null $defaultSiteUrl
     */
    public function __construct(Webmasters $webmasters, ?string $defaultSiteUrl = null)
    {
        $this->webmasters = $webmasters;
        $this->defaultSiteUrl = $defaultSiteUrl;
    }

    /**
     * Resolve site URL - use provided URL or fall back to default
     *
     * @param string|null $siteUrl
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function resolveSiteUrl(?string $siteUrl): string
    {
        $url = $siteUrl ?: $this->defaultSiteUrl;

        if (!$url) {
            throw new \InvalidArgumentException('Site URL is required. Set a default site URL with setSiteUrl() or pass it as a parameter.');
        }

        return $url;
    }

    /**
     * List all sitemaps for a site
     *
     * @param string|null $siteUrl The site's URL (e.g., 'https://www.example.com/'). If null, uses default site URL.
     * @return array
     *
     * @throws \Google\Service\Exception
     * @throws \InvalidArgumentException
     */
    public function list(?string $siteUrl = null): array
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        try {
            $response = $this->webmasters->sitemaps->listSitemaps($siteUrl);

            return $this->formatSitemapsResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Get a specific sitemap
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $feedpath The sitemap feedpath (e.g., 'sitemap.xml')
     * @return array|null
     *
     * @throws \Google\Service\Exception
     * @throws \InvalidArgumentException
     */
    public function get(?string $siteUrl = null, string $feedpath): ?array
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        try {
            $response = $this->webmasters->sitemaps->get($siteUrl, $feedpath);

            return $this->formatSitemapResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Submit a sitemap for indexing
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $feedpath The sitemap feedpath (e.g., 'sitemap.xml')
     * @return array
     *
     * @throws \Google\Service\Exception
     * @throws \InvalidArgumentException
     */
    public function submit(?string $siteUrl = null, string $feedpath): array
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        try {
            $response = $this->webmasters->sitemaps->submit($siteUrl, $feedpath);

            return $this->formatSitemapResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Delete a sitemap
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $feedpath The sitemap feedpath to delete
     * @return void
     *
     * @throws \Google\Service\Exception
     * @throws \InvalidArgumentException
     */
    public function delete(?string $siteUrl = null, string $feedpath): void
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        try {
            $this->webmasters->sitemaps->delete($siteUrl, $feedpath);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Get sitemap submission status and errors
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $feedpath The sitemap feedpath
     * @return array|null
     *
     * @throws \InvalidArgumentException
     */
    public function getStatus(?string $siteUrl = null, string $feedpath): ?array
    {
        $sitemap = $this->get($siteUrl, $feedpath);

        if (!$sitemap) {
            return null;
        }

        return [
            'path' => $sitemap['path'],
            'last_submitted' => $sitemap['last_submitted'],
            'last_downloaded' => $sitemap['last_downloaded'],
            'is_pending' => $sitemap['is_pending'],
            'is_sitemaps_index' => $sitemap['is_sitemaps_index'],
            'type' => $sitemap['type'],
            'errors' => $sitemap['errors'] ?? 0,
            'warnings' => $sitemap['warnings'] ?? 0,
            'contents' => $sitemap['contents'] ?? [],
        ];
    }

    /**
     * Get all sitemaps with their status
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getAllWithStatus(?string $siteUrl = null): array
    {
        $sitemaps = $this->list($siteUrl);

        return array_map(function ($sitemap) use ($siteUrl) {
            return $this->getStatus($siteUrl, $sitemap['path']);
        }, $sitemaps);
    }

    /**
     * Format sitemaps list response
     *
     * @param mixed $response
     * @return array
     */
    protected function formatSitemapsResponse($response): array
    {
        $result = [];

        if ($response && $response->getSitemap()) {
            foreach ($response->getSitemap() as $sitemap) {
                $result[] = $this->formatSitemapResponse($sitemap);
            }
        }

        return $result;
    }

    /**
     * Format single sitemap response
     *
     * @param mixed $sitemap
     * @return array
     */
    protected function formatSitemapResponse($sitemap): array
    {
        $contents = $sitemap->getContents();

        $formattedContents = [];
        if ($contents) {
            foreach ($contents as $content) {
                $formattedContents[] = [
                    'type' => $content->getType(),
                    'submitted' => $content->getSubmitted(),
                    'indexed' => $content->getIndexed(),
                ];
            }
        }

        return [
            'path' => $sitemap->getPath(),
            'last_submitted' => $sitemap->getLastSubmitted(),
            'last_downloaded' => $sitemap->getLastDownloaded(),
            'is_pending' => $sitemap->getIsPending(),
            'is_sitemaps_index' => $sitemap->getIsSitemapsIndex(),
            'type' => $sitemap->getType(),
            'errors' => $sitemap->getErrors(),
            'warnings' => $sitemap->getWarnings(),
            'contents' => $formattedContents,
        ];
    }
}
