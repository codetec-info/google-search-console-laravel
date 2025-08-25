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

    /**
     * Create a new UrlInspection instance
     *
     * @param SearchConsole $searchConsole
     */
    public function __construct(SearchConsole $searchConsole)
    {
        $this->searchConsole = $searchConsole;
    }

    /**
     * Inspect a URL's indexing status
     *
     * @param string $siteUrl The site's URL (e.g., 'https://www.example.com/')
     * @param string $inspectionUrl The specific URL to inspect
     * @param string $languageCode Optional language code for the inspection
     * @return array
     *
     * @throws \Google\Service\Exception
     */
    public function inspect(string $siteUrl, string $inspectionUrl, string $languageCode = 'en-US'): array
    {
        try {
            $request = new \Google\Service\SearchConsole\InspectUrlIndexRequest();
            $request->setInspectionUrl($inspectionUrl);
            $request->setLanguageCode($languageCode);

            $response = $this->searchConsole->urlInspection_index->inspect($request, $siteUrl);

            return $this->formatInspectionResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Inspect multiple URLs
     *
     * @param string $siteUrl The site's URL
     * @param array $inspectionUrls Array of URLs to inspect
     * @param string $languageCode Optional language code for the inspection
     * @return array
     */
    public function inspectMultiple(string $siteUrl, array $inspectionUrls, string $languageCode = 'en-US'): array
    {
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
     * @param string $siteUrl The site's URL
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     */
    public function getResult(string $siteUrl, string $inspectionUrl): array
    {
        return $this->inspect($siteUrl, $inspectionUrl);
    }

    /**
     * Check if a URL is indexed
     *
     * @param string $siteUrl The site's URL
     * @param string $inspectionUrl The specific URL to check
     * @return bool
     */
    public function isIndexed(string $siteUrl, string $inspectionUrl): bool
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
     * @param string $siteUrl The site's URL
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     */
    public function getIndexingIssues(string $siteUrl, string $inspectionUrl): array
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
     * @param string $siteUrl The site's URL
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     */
    public function getMobileUsabilityIssues(string $siteUrl, string $inspectionUrl): array
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
     * @param string $siteUrl The site's URL
     * @param string $inspectionUrl The specific URL to inspect
     * @return array
     */
    public function getRichResults(string $siteUrl, string $inspectionUrl): array
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
            'inspection_url' => $response->getInspectionUrl(),
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
