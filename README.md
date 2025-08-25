# Google Search Console for Laravel

A Laravel package that provides an elegant interface for accessing Google Search Console API endpoints. This package handles OAuth token management and provides fluent, eloquent-like methods for working with Search Analytics, Sitemaps, Sites, and URL Inspection.

## Installation

You can install the package via composer:

```bash
composer require michaelcrowcroft/google-search-console-laravel
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="MichaelCrowcroft\\GoogleSearchConsole\\GoogleSearchConsoleServiceProvider" --tag="google-search-console-config"
```

## Usage

### Basic Setup in Laravel

First, publish the configuration file:

```bash
php artisan vendor:publish --provider="MichaelCrowcroft\\GoogleSearchConsole\\GoogleSearchConsoleServiceProvider" --tag="google-search-console-config"
```

### Using the Facade (Recommended)

```php
use GoogleSearchConsole;
use Illuminate\Support\Facades\Log;

class SearchConsoleController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            // Get access token from session, database, or wherever you store it
            $accessToken = $request->session()->get('gsc_access_token');

            if (!$accessToken) {
                return redirect()->route('gsc.auth');
            }

            // Set the access token
            GoogleSearchConsole::setAccessToken($accessToken);

            // Get verified sites
            $sites = GoogleSearchConsole::sites()->list();

            if (empty($sites)) {
                return view('gsc.dashboard', [
                    'error' => 'No verified sites found in Google Search Console.'
                ]);
            }

            $siteUrl = $sites[0]['site_url'];

            // Get recent search analytics
            $endDate = now()->subDay()->format('Y-m-d');
            $startDate = now()->subDays(30)->format('Y-m-d');

            $searchData = GoogleSearchConsole::searchAnalytics()->getQueryData(
                $siteUrl,
                $startDate,
                $endDate,
                10
            );

            return view('gsc.dashboard', [
                'sites' => $sites,
                'searchData' => $searchData,
                'siteUrl' => $siteUrl
            ]);

        } catch (\Google\Service\Exception $e) {
            Log::error('Google Search Console API error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return view('gsc.dashboard', [
                'error' => 'Failed to fetch data from Google Search Console. Please try again later.'
            ]);
        }
    }
}
```

### Dependency Injection

```php
use MichaelCrowcroft\GoogleSearchConsole\GoogleSearchConsole as GSCClient;
use Illuminate\Support\Facades\Log;

class SearchConsoleService
{
    private GSCClient $gsc;

    public function __construct(GSCClient $gsc)
    {
        $this->gsc = $gsc;
    }

    public function getSitePerformance(string $siteUrl, int $days = 30): array
    {
        $this->gsc->setAccessToken($this->getToken());

        $endDate = now()->subDay()->format('Y-m-d');
        $startDate = now()->subDays($days)->format('Y-m-d');

        return [
            'queries' => $this->gsc->searchAnalytics()->getQueryData($siteUrl, $startDate, $endDate, 50),
            'pages' => $this->gsc->searchAnalytics()->getPageData($siteUrl, $startDate, $endDate, 50),
            'countries' => $this->gsc->searchAnalytics()->getCountryData($siteUrl, $startDate, $endDate, 20),
            'period' => "$startDate to $endDate",
        ];
    }

    private function getToken(): string
    {
        return config('google-search-console.access_token') ?: throw new \RuntimeException('GSC token not configured');
    }
}
```

### Using in Laravel Commands

```php
<?php

namespace App\Console\Commands;

use GoogleSearchConsole;
use Illuminate\Console\Command;

class GenerateGSCReport extends Command
{
    protected $signature = 'gsc:report {site} {--days=30}';
    protected $description = 'Generate Google Search Console report';

    public function handle()
    {
        $siteUrl = $this->argument('site');
        $days = (int) $this->option('days');

        GoogleSearchConsole::setAccessToken(config('google-search-console.access_token'));

        $this->info("Generating GSC report for $siteUrl ($days days)...");

        $endDate = now()->subDay()->format('Y-m-d');
        $startDate = now()->subDays($days)->format('Y-m-d');

        $data = GoogleSearchConsole::searchAnalytics()->getQueryData($siteUrl, $startDate, $endDate, 20);

        $this->table(
            ['Query', 'Clicks', 'Impressions', 'CTR', 'Position'],
            collect($data['rows'] ?? [])->map(function ($row) {
                return [
                    $row['keys'][0] ?? 'N/A',
                    $row['clicks'],
                    $row['impressions'],
                    number_format($row['ctr'] * 100, 2) . '%',
                    number_format($row['position'], 1),
                ];
            })->toArray()
        );

        $this->info('Report generated successfully!');
    }
}
```

## API Endpoints

### Search Analytics

Get search analytics data for your site:

```php
// Get query performance data
$queryData = GoogleSearchConsole::searchAnalytics()->getQueryData(
    'https://www.example.com/',
    '2024-01-01',
    '2024-01-31'
);

// Get page performance data
$pageData = GoogleSearchConsole::searchAnalytics()->getPageData(
    'https://www.example.com/',
    '2024-01-01',
    '2024-01-31'
);

// Get country performance data
$countryData = GoogleSearchConsole::searchAnalytics()->getCountryData(
    'https://www.example.com/',
    '2024-01-01',
    '2024-01-31'
);

// Custom query with filters
$customData = GoogleSearchConsole::searchAnalytics()->query(
    'https://www.example.com/',
    [
        'start_date' => '2024-01-01',
        'end_date' => '2024-01-31',
        'dimensions' => ['query', 'page'],
        'filters' => [
            // Add dimension filters here
        ],
        'row_limit' => 1000,
    ]
);
```

### Sitemaps

Manage your sitemaps:

```php
// List all sitemaps
$sitemaps = GoogleSearchConsole::sitemaps()->list('https://www.example.com/');

// Submit a sitemap
GoogleSearchConsole::sitemaps()->submit('https://www.example.com/', 'sitemap.xml');

// Get sitemap details
$sitemap = GoogleSearchConsole::sitemaps()->get('https://www.example.com/', 'sitemap.xml');

// Delete a sitemap
GoogleSearchConsole::sitemaps()->delete('https://www.example.com/', 'old-sitemap.xml');

// Get all sitemaps with status
$sitemapStatuses = GoogleSearchConsole::sitemaps()->getAllWithStatus('https://www.example.com/');
```

### Sites

List your verified sites:

```php
// List all verified sites
$sites = GoogleSearchConsole::sites()->list();

// Get site details
$site = GoogleSearchConsole::sites()->get('https://www.example.com/');

// Check if site is verified
$isVerified = GoogleSearchConsole::sites()->isVerified('https://www.example.com/');

// Get domain properties
$domainSites = GoogleSearchConsole::sites()->getDomainProperties();

// Get regular site properties
$siteProperties = GoogleSearchConsole::sites()->getSiteProperties();
```

### URL Inspection

Inspect specific URLs:

```php
// Inspect a URL
$inspection = GoogleSearchConsole::urlInspection()->inspect(
    'https://www.example.com/',
    'https://www.example.com/page'
);

// Check if URL is indexed
$isIndexed = GoogleSearchConsole::urlInspection()->isIndexed(
    'https://www.example.com/',
    'https://www.example.com/page'
);

// Get indexing issues
$issues = GoogleSearchConsole::urlInspection()->getIndexingIssues(
    'https://www.example.com/',
    'https://www.example.com/page'
);

// Get mobile usability issues
$mobileIssues = GoogleSearchConsole::urlInspection()->getMobileUsabilityIssues(
    'https://www.example.com/',
    'https://www.example.com/page'
);

// Get rich results
$richResults = GoogleSearchConsole::urlInspection()->getRichResults(
    'https://www.example.com/',
    'https://www.example.com/page'
);
```

## OAuth Token Management

This package assumes you are managing OAuth tokens yourself. Here's how to set them up:

```php
// Set access token
GoogleSearchConsole::setAccessToken($accessToken);

// Check if token is valid
if (GoogleSearchConsole::isAccessTokenValid()) {
    // Token is valid, proceed with API calls
}

// Get current access token
$currentToken = GoogleSearchConsole::getAccessToken();
```

## Configuration Options

The package comes with a comprehensive configuration file. Here are some key options:

```php
// config/google-search-console.php
return [
    'application_name' => 'My App Name',
    'timeout' => 30,
    'retry_attempts' => 3,
    'search_analytics' => [
        'default_row_limit' => 1000,
        'default_dimensions' => [],
    ],
    'cache' => [
        'enabled' => false,
        'ttl' => 3600,
    ],
    'debug' => false,
];
```

## Error Handling

The package throws `\Google\Service\Exception` for API errors. Make sure to handle these appropriately:

```php
try {
    $data = GoogleSearchConsole::searchAnalytics()->getQueryData(
        'https://www.example.com/',
        '2024-01-01',
        '2024-01-31'
    );
} catch (\Google\Service\Exception $e) {
    // Handle API error
    Log::error('Google Search Console API error: ' . $e->getMessage());
}
```

## Testing

Run the tests with:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@michaelcrowcroft.com instead of using the issue tracker.

## Credits

- [Michael Crowcroft](https://github.com/michaelcrowcroft)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).