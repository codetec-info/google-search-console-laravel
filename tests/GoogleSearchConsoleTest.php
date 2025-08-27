<?php

namespace MichaelCrowcroft\GoogleSearchConsole\Tests;

use MichaelCrowcroft\GoogleSearchConsole\GoogleSearchConsole;
use MichaelCrowcroft\GoogleSearchConsole\Facades\GoogleSearchConsole as GoogleSearchConsoleFacade;
use MichaelCrowcroft\GoogleSearchConsole\GoogleSearchConsoleServiceProvider;
use Orchestra\Testbench\TestCase;

class GoogleSearchConsoleTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            GoogleSearchConsoleServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'GoogleSearchConsole' => GoogleSearchConsoleFacade::class,
        ];
    }

    public function test_google_search_console_can_be_instantiated()
    {
        $gsc = new GoogleSearchConsole();

        $this->assertInstanceOf(GoogleSearchConsole::class, $gsc);
    }

    public function test_google_search_console_can_be_instantiated_with_token()
    {
        $accessToken = 'test-access-token';
        $gsc = new GoogleSearchConsole($accessToken);

        $this->assertInstanceOf(GoogleSearchConsole::class, $gsc);
        $this->assertEquals($accessToken, $gsc->getAccessToken());
    }

    public function test_facade_returns_correct_instance()
    {
        $gsc = GoogleSearchConsoleFacade::getFacadeRoot();

        $this->assertInstanceOf(GoogleSearchConsole::class, $gsc);
    }

    public function test_service_can_be_resolved_from_container()
    {
        $gsc = $this->app->make(GoogleSearchConsole::class);

        $this->assertInstanceOf(GoogleSearchConsole::class, $gsc);
    }

    public function test_services_can_be_accessed()
    {
        $gsc = new GoogleSearchConsole();

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\Analytics::class,
            $gsc->analytics()
        );

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\Sitemaps::class,
            $gsc->sitemaps()
        );

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\Sites::class,
            $gsc->sites()
        );

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\UrlInspection::class,
            $gsc->urlInspection()
        );
    }

    public function test_facade_services_can_be_accessed()
    {
        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\Analytics::class,
            GoogleSearchConsoleFacade::analytics()
        );

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\Sitemaps::class,
            GoogleSearchConsoleFacade::sitemaps()
        );

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\Sites::class,
            GoogleSearchConsoleFacade::sites()
        );

        $this->assertInstanceOf(
            \MichaelCrowcroft\GoogleSearchConsole\Services\UrlInspection::class,
            GoogleSearchConsoleFacade::urlInspection()
        );
    }
}
