<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\AddressCollection;
use Pralhad\Zipcoder\ZipcoderLookup;

/**
 * @method static ZipcoderLookup registerProvider(Provider $provider)
 * @method static Provider using(string $providerName)
 * @method static AddressCollection lookup(Query $query)
 * @method static list<string> getRegisteredProviders()
 *
 * @see ZipcoderLookup
 */
final class Zipcoder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zipcoder';
    }
}
