# Google Search Console for Laravel

A simple Laravel package for the Google Search Console API.

## Installation

```bash
composer require michaelcrowcroft/google-search-console-laravel

php artisan vendor:publish --provider="MichaelCrowcroft\\GoogleSearchConsole\\GoogleSearchConsoleServiceProvider" --tag="google-search-console-config"
```

## Usage

```php
use GoogleSearchConsole;

// Set your access token and default site URL
GoogleSearchConsole::setAccessToken($token);
GoogleSearchConsole::setSiteUrl('https://example.com');

// Query search analytics
$data = GoogleSearchConsole::analytics()->query([
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'dimensions' => ['query', 'page'],
    'row_limit' => 100
]);

// Helper methods
$queries = GoogleSearchConsole::analytics()->getQueryData('2024-01-01', '2024-01-31');
$pages = GoogleSearchConsole::analytics()->getPageData('2024-01-01', '2024-01-31');
$countries = GoogleSearchConsole::analytics()->getCountryData('2024-01-01', '2024-01-31');

// Pass a different site if you want to overwrite the set URL, or haven't set one already.
$queries = GoogleSearchConsole::analytics()->getQueryData('https://othersite.com', '2024-01-01', '2024-01-31');
```

## Analytics

### The Query Method

The most powerful way to get search analytics data:

```php
// Basic query (uses default site URL)
$data = GoogleSearchConsole::analytics()->query([
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'dimensions' => ['query'],
    'row_limit' => 100
]);

// Multiple dimensions
$data = GoogleSearchConsole::analytics()->query([
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'dimensions' => ['query', 'page', 'country'],
    'row_limit' => 1000
]);

// With filters
$data = GoogleSearchConsole::analytics()->query([
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'dimensions' => ['query'],
    'filters' => [
        // Dimension filter groups
    ],
    'row_limit' => 500
]);

// Or specify different site URL
$data = GoogleSearchConsole::analytics()->query('https://othersite.com', [
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'dimensions' => ['query']
]);
```

### Helper Methods

For common use cases:

```php
// Get top queries (uses default site URL)
$queries = GoogleSearchConsole::analytics()->getQueryData('2024-01-01', '2024-01-31', 100);

// Get top pages
$pages = GoogleSearchConsole::analytics()->getPageData('2024-01-01', '2024-01-31', 100);

// Get country performance
$countries = GoogleSearchConsole::analytics()->getCountryData('2024-01-01', '2024-01-31', 50);

// Get device performance
$devices = GoogleSearchConsole::analytics()->getDeviceData('2024-01-01', '2024-01-31');

// Get query and page combinations
$queryPages = GoogleSearchConsole::analytics()->getQueryPageData('2024-01-01', '2024-01-31');

// Or specify different site URL for specific calls
$queries = GoogleSearchConsole::analytics()->getQueryData('https://othersite.com', '2024-01-01', '2024-01-31');
```

## Sitemaps

```php
// List sitemaps (uses default site URL)
$sitemaps = GoogleSearchConsole::sitemaps()->list();

// Submit sitemap
GoogleSearchConsole::sitemaps()->submit('sitemap.xml');

// Get sitemap details
$sitemap = GoogleSearchConsole::sitemaps()->get('sitemap.xml');

// Delete sitemap
GoogleSearchConsole::sitemaps()->delete('old-sitemap.xml');

// Or specify different site URL
$sitemaps = GoogleSearchConsole::sitemaps()->list('https://othersite.com');
```

## Sites

```php
// List all verified sites
$sites = GoogleSearchConsole::sites()->list();

// Get site details (uses default site URL)
$site = GoogleSearchConsole::sites()->get();

// Check verification
$isVerified = GoogleSearchConsole::sites()->isVerified();

// Or specify different site URL
$site = GoogleSearchConsole::sites()->get('https://othersite.com');
```

## URL Inspection

```php
// Inspect URL (uses default site URL)
$result = GoogleSearchConsole::urlInspection()->inspect('https://example.com/page');

// Check if indexed
$isIndexed = GoogleSearchConsole::urlInspection()->isIndexed('https://example.com/page');

// Or specify different site URL
$result = GoogleSearchConsole::urlInspection()->inspect('https://othersite.com', 'https://othersite.com/page');
```

## Token & Site Management

```php
// Set access token
GoogleSearchConsole::setAccessToken($token);

// Set default site URL
GoogleSearchConsole::setSiteUrl('https://example.com');

// Check validity
if (GoogleSearchConsole::isAccessTokenValid()) {
    // Proceed
}

// Get current values
$token = GoogleSearchConsole::getAccessToken();
$siteUrl = GoogleSearchConsole::getSiteUrl();
```

## Error Handling

The package throws `\Google\Service\Exception` for API errors:

```php
try {
    $data = GoogleSearchConsole::analytics()->query('https://example.com', [
        'start_date' => '2024-01-01',
        'end_date' => '2024-01-31'
    ]);
} catch (\Google\Service\Exception $e) {
    Log::error('GSC Error: ' . $e->getMessage());
}
```

```bash
php artisan vendor:publish --provider="MichaelCrowcroft\\GoogleSearchConsole\\GoogleSearchConsoleServiceProvider" --tag="google-search-console-config"
```

## License

MIT