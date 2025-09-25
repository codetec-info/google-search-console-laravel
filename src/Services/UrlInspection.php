<?php

namespace MichaelCrowcroft\GoogleSearchConsole\Services;

use Google\Service\SearchConsole;

/**
 * URL Inspection service for Google Search Console
 *
 * This class provides methods to inspect URLs and get detailed
 * information about their indexing status, coverage, and issues.
 */
class UrlInspection
{
    protected SearchConsole $searchConsole;
    protected ?string $defaultSiteUrl;

    /**
     * Create a new UrlInspection instance
     *
     * @param SearchConsole $searchConsole
     * @param string|null $defaultSiteUrl
     */
    public function __construct(SearchConsole $searchConsole, ?string $defaultSiteUrl = null)
    {
        $this->searchConsole = $searchConsole;
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
     * Inspect a URL's indexing status
     *
     * @param string|null $siteUrl The site's URL (e.g., 'https://www.example.com/'). If null, uses default site URL.
     * @param string $inspectionUrl The specific URL to inspect
     * @param string $languageCode Optional language code for the inspection
     * @return array
     *
     * @throws \Google\Service\Exception
     * @throws \InvalidArgumentException
     */
    public function inspect(?string $siteUrl = null, string $inspectionUrl, string $languageCode = 'en-US'): array
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        try {
            $request = new \Google\Service\SearchConsole\InspectUrlIndexRequest();
            $request->setInspectionUrl($inspectionUrl);
            $request->setLanguageCode($languageCode);
            $request->setSiteUrl($siteUrl);

            $response = $this->searchConsole->urlInspection_index->inspect($request);

            return $this->formatInspectionResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Inspect multiple URLs
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param array $inspectionUrls Array of URLs to inspect
     * @param string $languageCode Optional language code for the inspection
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function inspectMultiple(?string $siteUrl = null, array $inspectionUrls, string $languageCode = 'en-US'): array
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        $results = [];

        foreach ($inspectionUrls as $url) {
            try {
                $result = $this->inspect($siteUrl, $url, $languageCode);
                $results[] = $result;
            } catch (\Exception $e) {
                $results[] = [
                    'url' => $url,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        }

        return $results;
    }

    /**
     * Get URL inspection result with simplified interface
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getResult(?string $siteUrl = null, string $inspectionUrl): array
    {
        return $this->inspect($siteUrl, $inspectionUrl);
    }

    /**
     * Check if a URL is indexed
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $inspectionUrl The specific URL to check
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function isIndexed(?string $siteUrl = null, string $inspectionUrl): bool
    {
        try {
            $result = $this->inspect($siteUrl, $inspectionUrl);
            return $result['indexing_state'] === 'INDEXING_ALLOWED';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get indexing issues for a URL
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getIndexingIssues(?string $siteUrl = null, string $inspectionUrl): array
    {
        try {
            $result = $this->inspect($siteUrl, $inspectionUrl);

            return [
                'url' => $inspectionUrl,
                'indexing_state' => $result['indexing_state'],
                'coverage_state' => $result['coverage_state'],
                'verdict' => $result['verdict'],
                'last_crawl_time' => $result['last_crawl_time'],
                'page_fetch_state' => $result['page_fetch_state'],
                'robots_txt_state' => $result['robots_txt_state'],
                'sitemap' => $result['sitemap'] ?? null,
                'referring_urls' => $result['referring_urls'] ?? [],
                'crawled_as' => $result['crawled_as'],
            ];
        } catch (\Exception $e) {
            return [
                'url' => $inspectionUrl,
                'error' => $e->getMessage(),
                'success' => false,
            ];
        }
    }

    /**
     * Get mobile usability issues for a URL
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getMobileUsabilityIssues(?string $siteUrl = null, string $inspectionUrl): array
    {
        try {
            $result = $this->inspect($siteUrl, $inspectionUrl);

            return $result['mobile_usability_result'] ?? [];
        } catch (\Exception $e) {
            return [
                'url' => $inspectionUrl,
                'error' => $e->getMessage(),
                'success' => false,
            ];
        }
    }

    /**
     * Get rich results for a URL
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getRichResults(?string $siteUrl = null, string $inspectionUrl): array
    {
        try {
            $result = $this->inspect($siteUrl, $inspectionUrl);

            return $result['rich_results_result'] ?? [];
        } catch (\Exception $e) {
            return [
                'url' => $inspectionUrl,
                'error' => $e->getMessage(),
                'success' => false,
            ];
        }
    }

    /**
     * Format the inspection response into a more usable array
     *
     * @param mixed $response
     * @return array
     */
    protected function formatInspectionResponse($response): array
    {
        $inspectionResult = $response->getInspectionResult();

        $result = [
            'inspection_url' => $inspectionResult->getInspectionUrl(),
            'indexing_state' => $inspectionResult->getIndexingState(),
            'coverage_state' => $inspectionResult->getCoverageState(),
            'verdict' => $inspectionResult->getVerdict(),
            'last_crawl_time' => $inspectionResult->getLastCrawlTime(),
            'page_fetch_state' => $inspectionResult->getPageFetchState(),
            'robots_txt_state' => $inspectionResult->getRobotsTxtState(),
            'crawled_as' => $inspectionResult->getCrawledAs(),
        ];

        // Add mobile usability result if available
        $mobileUsabilityResult = $inspectionResult->getMobileUsabilityResult();
        if ($mobileUsabilityResult) {
            $result['mobile_usability_result'] = [
                'verdict' => $mobileUsabilityResult->getVerdict(),
                'issues' => $this->formatMobileUsabilityIssues($mobileUsabilityResult->getIssues()),
            ];
        }

        // Add rich results if available
        $richResultsResult = $inspectionResult->getRichResultsResult();
        if ($richResultsResult) {
            $result['rich_results_result'] = [
                'verdict' => $richResultsResult->getVerdict(),
                'detected_items' => $this->formatRichResultsItems($richResultsResult->getDetectedItems()),
                'items' => $this->formatRichResultsItems($richResultsResult->getItems()),
            ];
        }

        return $result;
    }

    /**
     * Format mobile usability issues
     *
     * @param mixed $issues
     * @return array
     */
    protected function formatMobileUsabilityIssues($issues): array
    {
        if (!$issues) {
            return [];
        }

        $formattedIssues = [];
        foreach ($issues as $issue) {
            $formattedIssues[] = [
                'issue_type' => $issue->getIssueType(),
                'severity' => $issue->getSeverity(),
                'message' => $issue->getMessage(),
            ];
        }

        return $formattedIssues;
    }

    /**
     * Format rich results items
     *
     * @param mixed $items
     * @return array
     */
    protected function formatRichResultsItems($items): array
    {
        if (!$items) {
            return [];
        }

        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'name' => $item->getName(),
                'issues' => $this->formatRichResultsIssues($item->getIssues()),
            ];
        }

        return $formattedItems;
    }

    /**
     * Format rich results issues
     *
     * @param mixed $issues
     * @return array
     */
    protected function formatRichResultsIssues($issues): array
    {
        if (!$issues) {
            return [];
        }

        $formattedIssues = [];
        foreach ($issues as $issue) {
            $formattedIssues[] = [
                'severity' => $issue->getSeverity(),
                'issue_message' => $issue->getIssueMessage(),
                'directive' => $issue->getDirective(),
            ];
        }

        return $formattedIssues;
    }
}
