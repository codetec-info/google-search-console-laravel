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

    /**
     * Create a new Sitemaps instance
     *
     * @param Webmasters $webmasters
     */
    public function __construct(Webmasters $webmasters)
    {
        $this->webmasters = $webmasters;
    }

    /**
     * List all sitemaps for a site
     *
     * @param string $siteUrl The site's URL (e.g., 'https://www.example.com/')
     * @return array
     *
     * @throws \Google\Service\Exception
     */
    public function list(string $siteUrl): array
    {
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
     * @param string $siteUrl The site's URL
     * @param string $feedpath The sitemap feedpath (e.g., 'sitemap.xml')
     * @return array|null
     *
     * @throws \Google\Service\Exception
     */
    public function get(string $siteUrl, string $feedpath): ?array
    {
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
     * @param string $siteUrl The site's URL
     * @param string $feedpath The sitemap feedpath (e.g., 'sitemap.xml')
     * @return array
     *
     * @throws \Google\Service\Exception
     */
    public function submit(string $siteUrl, string $feedpath): array
    {
        try {
            $sitemap = new WmxSitemap();
            $sitemap->setPath($feedpath);
            $sitemap->setType('WEB'); // Default type for web sitemaps

            $response = $this->webmasters->sitemaps->submit($siteUrl, $feedpath, $sitemap);

            return $this->formatSitemapResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Delete a sitemap
     *
     * @param string $siteUrl The site's URL
     * @param string $feedpath The sitemap feedpath to delete
     * @return void
     *
     * @throws \Google\Service\Exception
     */
    public function delete(string $siteUrl, string $feedpath): void
    {
        try {
            $this->webmasters->sitemaps->delete($siteUrl, $feedpath);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Get sitemap submission status and errors
     *
     * @param string $siteUrl The site's URL
     * @param string $feedpath The sitemap feedpath
     * @return array|null
     */
    public function getStatus(string $siteUrl, string $feedpath): ?array
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
     * @param string $siteUrl The site's URL
     * @return array
     */
    public function getAllWithStatus(string $siteUrl): array
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
