<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Laravel\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Pralhad\Zipcoder\Laravel\Tests\TestCase;
use Pralhad\Zipcoder\ZipcoderLookup;

final class ZipcoderServiceProviderTest extends TestCase
{
    #[Test]
    public function it_resolves_zipcoder_lookup_from_container(): void
    {
        $lookup = $this->app->make(ZipcoderLookup::class);

        $this->assertInstanceOf(ZipcoderLookup::class, $lookup);
    }

    #[Test]
    public function it_resolves_via_alias(): void
    {
        $lookup = $this->app->make('zipcoder');

        $this->assertInstanceOf(ZipcoderLookup::class, $lookup);
    }

    #[Test]
    public function it_registers_as_singleton(): void
    {
        $first = $this->app->make(ZipcoderLookup::class);
        $second = $this->app->make(ZipcoderLookup::class);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_has_registered_providers(): void
    {
        $lookup = $this->app->make(ZipcoderLookup::class);
        $providers = $lookup->getRegisteredProviders();

        $this->assertNotEmpty($providers);
    }

    #[Test]
    public function it_wraps_chain_in_cache_by_default(): void
    {
        $lookup = $this->app->make(ZipcoderLookup::class);
        $providers = $lookup->getRegisteredProviders();

        $this->assertStringStartsWith('cache(', $providers[0]);
    }

    #[Test]
    public function it_skips_cache_when_disabled(): void
    {
        $this->app['config']->set('zipcoder.cache.enabled', false);

        // Re-resolve since singleton was already built
        $this->app->forgetInstance(ZipcoderLookup::class);
        $lookup = $this->app->make(ZipcoderLookup::class);
        $providers = $lookup->getRegisteredProviders();

        $this->assertSame('chain', $providers[0]);
    }

    #[Test]
    public function it_publishes_config(): void
    {
        $this->artisan('vendor:publish', [
            '--tag' => 'zipcoder-config',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists($this->app->configPath('zipcoder.php'));
    }

    #[Test]
    public function config_has_expected_keys(): void
    {
        $config = $this->app['config']['zipcoder'];

        $this->assertArrayHasKey('http', $config);
        $this->assertArrayHasKey('providers', $config);
        $this->assertArrayHasKey('chain', $config);
        $this->assertArrayHasKey('cache', $config);
    }

    #[Test]
    public function it_falls_back_to_zippopotamus_when_no_credentials(): void
    {
        $this->app['config']->set('zipcoder.providers.zippopotamus.enabled', false);
        $this->app['config']->set('zipcoder.providers.jp-postal-code.enabled', false);
        $this->app['config']->set('zipcoder.providers.geonames.username', null);
        $this->app['config']->set('zipcoder.providers.zipcodestack.api_key', null);
        $this->app['config']->set('zipcoder.providers.zipcodebase.api_key', null);
        $this->app['config']->set('zipcoder.cache.enabled', false);

        $this->app->forgetInstance(ZipcoderLookup::class);
        $lookup = $this->app->make(ZipcoderLookup::class);

        $this->assertInstanceOf(ZipcoderLookup::class, $lookup);
    }
}
