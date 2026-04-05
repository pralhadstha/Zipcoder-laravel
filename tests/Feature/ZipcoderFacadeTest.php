<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Laravel\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Pralhad\Zipcoder\Laravel\Facades\Zipcoder;
use Pralhad\Zipcoder\Laravel\Tests\TestCase;

final class ZipcoderFacadeTest extends TestCase
{
    #[Test]
    public function facade_resolves_to_zipcoder_lookup(): void
    {
        $providers = Zipcoder::getRegisteredProviders();

        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);
    }

    #[Test]
    public function facade_using_returns_provider(): void
    {
        $providers = Zipcoder::getRegisteredProviders();
        $provider = Zipcoder::using($providers[0]);

        $this->assertSame($providers[0], $provider->getName());
    }
}
