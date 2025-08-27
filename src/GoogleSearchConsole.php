<?php

namespace MichaelCrowcroft\GoogleSearchConsole;

use Google\Client;
use Google\Service\Webmasters;
use Google\Service\SearchConsole;
use MichaelCrowcroft\GoogleSearchConsole\Services\Analytics;
use MichaelCrowcroft\GoogleSearchConsole\Services\Sitemaps;
use MichaelCrowcroft\GoogleSearchConsole\Services\Sites;
use MichaelCrowcroft\GoogleSearchConsole\Services\UrlInspection;

/**
 * Main Google Search Console API client
 *
 * This class provides access to all Google Search Console API endpoints
 * and handles OAuth token management.
 */
class GoogleSearchConsole
{
    protected Client $client;
    protected Webmasters $webmasters;
    protected SearchConsole $searchConsole;
    protected ?string $accessToken = null;
    protected ?string $defaultSiteUrl = null;

    /**
     * Create a new Google Search Console instance
     *
     * @param string|null $accessToken OAuth2 access token
     * @param array $config Additional configuration options
     */
    public function __construct(?string $accessToken = null, array $config = [])
    {
        $this->accessToken = $accessToken;
        $this->initializeClient($config);
    }

    /**
     * Initialize the Google API client
     *
     * @param array $config
     * @return void
     */
    protected function initializeClient(array $config = []): void
    {
        $this->client = new Client($config);

        $this->client->setApplicationName('Google Search Console Laravel Package');

        if ($this->accessToken) {
            $this->client->setAccessToken($this->accessToken);
        }

        $this->webmasters = new Webmasters($this->client);

        $this->searchConsole = new SearchConsole($this->client);
    }

    /**
     * Set the OAuth2 access token
     *
     * @param string $accessToken
     * @return self
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        $this->client->setAccessToken($accessToken);

        return $this;
    }

    /**
     * Get the current access token
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Check if the access token is valid
     *
     * @return bool
     */
    public function isAccessTokenValid(): bool
    {
        if (!$this->accessToken) {
            return false;
        }

        // Check if token is expired
        if ($this->client->isAccessTokenExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Set the default site URL
     *
     * @param string $siteUrl
     * @return self
     */
    public function setSiteUrl(string $siteUrl): self
    {
        $this->defaultSiteUrl = $siteUrl;

        return $this;
    }

    /**
     * Get the default site URL
     *
     * @return string|null
     */
    public function getSiteUrl(): ?string
    {
        return $this->defaultSiteUrl;
    }

    /**
     * Get the Google API client instance
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the Webmasters service instance
     *
     * @return Webmasters
     */
    public function getWebmastersService(): Webmasters
    {
        return $this->webmasters;
    }

    /**
     * Get the SearchConsole service instance
     *
     * @return SearchConsole
     */
    public function getSearchConsoleService(): SearchConsole
    {
        return $this->searchConsole;
    }

    /**
     * Get Analytics service
     *
     * @return Analytics
     */
    public function analytics(): Analytics
    {
        return new Analytics($this->webmasters, $this->defaultSiteUrl);
    }

    /**
     * Get Sitemaps service
     *
     * @return Sitemaps
     */
    public function sitemaps(): Sitemaps
    {
        return new Sitemaps($this->webmasters, $this->defaultSiteUrl);
    }

    /**
     * Get Sites service
     *
     * @return Sites
     */
    public function sites(): Sites
    {
        return new Sites($this->webmasters, $this->defaultSiteUrl);
    }

    /**
     * Get URL Inspection service
     *
     * @return UrlInspection
     */
    public function urlInspection(): UrlInspection
    {
        return new UrlInspection($this->searchConsole, $this->defaultSiteUrl);
    }
}
