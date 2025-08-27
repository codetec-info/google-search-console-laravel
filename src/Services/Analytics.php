<?php

namespace MichaelCrowcroft\GoogleSearchConsole\Services;

use Google\Service\Webmasters;
use Google\Service\Webmasters\SearchAnalyticsQueryRequest;

class Analytics
{
    protected Webmasters $webmasters;
    protected ?string $defaultSiteUrl;

    /**
     * Create a new Analytics instance
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
     * Query search analytics data
     *
     * @param string|null $siteUrl The site's URL (e.g., 'https://www.example.com/'). If null, uses default site URL.
     * @param array $options Query options
     * @return array
     *
     * @throws \Google\Service\Exception
     * @throws \InvalidArgumentException
     */
    public function query(?string $siteUrl = null, array $options = []): array
    {
        $siteUrl = $this->resolveSiteUrl($siteUrl);

        $request = new SearchAnalyticsQueryRequest();

        // Set start and end dates
        if (isset($options['start_date'])) {
            $request->setStartDate($options['start_date']);
        }

        if (isset($options['end_date'])) {
            $request->setEndDate($options['end_date']);
        }

        // Set dimensions (query, page, country, device, searchAppearance)
        if (isset($options['dimensions'])) {
            $request->setDimensions($options['dimensions']);
        }

        // Set filters
        if (isset($options['filters'])) {
            $request->setDimensionFilterGroups($options['filters']);
        }

        // Set row limit
        if (isset($options['row_limit'])) {
            $request->setRowLimit($options['row_limit']);
        } else {
            $request->setRowLimit(1000); // Default limit
        }

        // Set start row for pagination
        if (isset($options['start_row'])) {
            $request->setStartRow($options['start_row']);
        }

        try {
            $response = $this->webmasters->searchanalytics->query($siteUrl, $request);

            return $this->formatResponse($response);
        } catch (\Exception $e) {
            throw new \Google\Service\Exception($e->getMessage(), $e->getCode(), $e, null);
        }
    }

    /**
     * Get search analytics data with simplified parameters
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param array $dimensions Dimensions to group by
     * @param int $rowLimit Maximum number of rows to return
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getData(
        ?string $siteUrl = null,
        string $startDate = '',
        string $endDate = '',
        array $dimensions = [],
        int $rowLimit = 1000
    ): array {
        return $this->query($siteUrl, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dimensions' => $dimensions,
            'row_limit' => $rowLimit,
        ]);
    }

    /**
     * Get query performance data
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $rowLimit Maximum number of rows to return
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getQueryData(
        ?string $siteUrl = null,
        string $startDate = '',
        string $endDate = '',
        int $rowLimit = 1000
    ): array {
        return $this->getData($siteUrl, $startDate, $endDate, ['query'], $rowLimit);
    }

    /**
     * Get page performance data
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $rowLimit Maximum number of rows to return
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getPageData(
        ?string $siteUrl = null,
        string $startDate = '',
        string $endDate = '',
        int $rowLimit = 1000
    ): array {
        return $this->getData($siteUrl, $startDate, $endDate, ['page'], $rowLimit);
    }

    /**
     * Get country performance data
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $rowLimit Maximum number of rows to return
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getCountryData(
        ?string $siteUrl = null,
        string $startDate = '',
        string $endDate = '',
        int $rowLimit = 1000
    ): array {
        return $this->getData($siteUrl, $startDate, $endDate, ['country'], $rowLimit);
    }

    /**
     * Get device performance data
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $rowLimit Maximum number of rows to return
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getDeviceData(
        ?string $siteUrl = null,
        string $startDate = '',
        string $endDate = '',
        int $rowLimit = 1000
    ): array {
        return $this->getData($siteUrl, $startDate, $endDate, ['device'], $rowLimit);
    }

    /**
     * Get query and page combination data
     *
     * @param string|null $siteUrl The site's URL. If null, uses default site URL.
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $rowLimit Maximum number of rows to return
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getQueryPageData(
        ?string $siteUrl = null,
        string $startDate = '',
        string $endDate = '',
        int $rowLimit = 1000
    ): array {
        return $this->getData($siteUrl, $startDate, $endDate, ['query', 'page'], $rowLimit);
    }

    /**
     * Format the API response into a more usable array
     *
     * @param mixed $response
     * @return array
     */
    protected function formatResponse($response): array
    {
        $result = [
            'rows' => [],
            'response_aggregation_type' => $response->getResponseAggregationType(),
        ];

        $rows = $response->getRows();

        if ($rows) {
            foreach ($rows as $row) {
                $formattedRow = [
                    'keys' => $row->getKeys(),
                    'clicks' => $row->getClicks(),
                    'impressions' => $row->getImpressions(),
                    'ctr' => $row->getCtr(),
                    'position' => $row->getPosition(),
                ];

                $result['rows'][] = $formattedRow;
            }
        }

        return $result;
    }
}
