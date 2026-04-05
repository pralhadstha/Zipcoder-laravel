<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Laravel;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Provider\Cache;
use Pralhad\Zipcoder\Provider\Chain;
use Pralhad\Zipcoder\Provider\GeoNames;
use Pralhad\Zipcoder\Provider\JpPostalCode;
use Pralhad\Zipcoder\Provider\Zipcodebase;
use Pralhad\Zipcoder\Provider\Zipcodestack;
use Pralhad\Zipcoder\Provider\Zippopotamus;
use Pralhad\Zipcoder\ZipcoderLookup;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class ZipcoderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/zipcoder.php', 'zipcoder');

        $this->app->singleton(ZipcoderLookup::class, function (Application $app): ZipcoderLookup {
            $config = $app['config']['zipcoder'];

            $client = $this->resolveHttpClient($app, $config);
            $requestFactory = $this->resolveRequestFactory($app, $client);

            $providers = $this->buildChainProviders($config, $client, $requestFactory, $app);

            $chain = new Chain($providers, $app['log']->driver());

            $provider = $chain;

            if ($config['cache']['enabled'] ?? true) {
                $store = $app['cache']->store($config['cache']['store'] ?? null);
                $ttl = (int) ($config['cache']['ttl'] ?? 86400);
                $provider = new Cache($chain, $store, $ttl);
            }

            $lookup = new ZipcoderLookup;
            $lookup->registerProvider($provider);

            return $lookup;
        });

        $this->app->alias(ZipcoderLookup::class, 'zipcoder');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/zipcoder.php' => $this->app->configPath('zipcoder.php'),
            ], 'zipcoder-config');
        }
    }

    public function provides(): array
    {
        return [ZipcoderLookup::class, 'zipcoder'];
    }

    /**
     * Resolve a PSR-18 HTTP client.
     *
     * Uses Guzzle 7+ which ships with Laravel and implements PSR-18 ClientInterface.
     */
    private function resolveHttpClient(Application $app, array $config): ClientInterface
    {
        if ($app->bound(ClientInterface::class)) {
            return $app->make(ClientInterface::class);
        }

        return new Client([
            'timeout' => (int) ($config['http']['timeout'] ?? 10),
            'connect_timeout' => (int) ($config['http']['connect_timeout'] ?? 5),
        ]);
    }

    private function resolveRequestFactory(Application $app, ClientInterface $client): RequestFactoryInterface
    {
        if ($app->bound(RequestFactoryInterface::class)) {
            return $app->make(RequestFactoryInterface::class);
        }

        if ($client instanceof RequestFactoryInterface) {
            return $client;
        }

        return new HttpFactory;
    }

    /**
     * @return list<Provider>
     */
    private function buildChainProviders(
        array $config,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        Application $app,
    ): array {
        $providerConfigs = $config['providers'] ?? [];
        $chainOrder = $config['chain'] ?? [];

        $builders = [
            'geonames' => function () use ($providerConfigs, $client, $requestFactory) {
                $username = $providerConfigs['geonames']['username'] ?? null;
                if (! $username) {
                    return null;
                }

                return new GeoNames($client, $requestFactory, $username);
            },
            'zippopotamus' => function () use ($providerConfigs, $client, $requestFactory) {
                if (! ($providerConfigs['zippopotamus']['enabled'] ?? true)) {
                    return null;
                }

                return new Zippopotamus($client, $requestFactory);
            },
            'zipcodestack' => function () use ($providerConfigs, $client, $requestFactory) {
                $apiKey = $providerConfigs['zipcodestack']['api_key'] ?? null;
                if (! $apiKey) {
                    return null;
                }

                return new Zipcodestack($client, $requestFactory, $apiKey);
            },
            'zipcodebase' => function () use ($providerConfigs, $client, $requestFactory) {
                $apiKey = $providerConfigs['zipcodebase']['api_key'] ?? null;
                if (! $apiKey) {
                    return null;
                }

                return new Zipcodebase($client, $requestFactory, $apiKey);
            },
            'jp-postal-code' => function () use ($providerConfigs, $client, $requestFactory) {
                if (! ($providerConfigs['jp-postal-code']['enabled'] ?? true)) {
                    return null;
                }

                $locale = $providerConfigs['jp-postal-code']['locale'] ?? 'en';

                return new JpPostalCode($client, $requestFactory, $locale);
            },
        ];

        $providers = [];

        foreach ($chainOrder as $name) {
            // Built-in provider
            if (isset($builders[$name])) {
                $provider = $builders[$name]();
                if ($provider !== null) {
                    $providers[] = $provider;
                }

                continue;
            }

            // Custom provider from config
            $customConfig = $providerConfigs[$name] ?? null;
            if ($customConfig === null) {
                continue;
            }

            $customProvider = $this->resolveCustomProvider($customConfig, $app, $client, $requestFactory);
            if ($customProvider !== null) {
                $providers[] = $customProvider;
            }
        }

        if ($providers === []) {
            $providers[] = new Zippopotamus($client, $requestFactory);
        }

        return $providers;
    }

    /**
     * Resolve a custom provider from config.
     *
     * Config format:
     *   'my-provider' => [
     *       'class' => \App\Zipcoder\MyProvider::class,
     *       'enabled' => true,
     *       // ...any extra constructor params
     *   ],
     */
    private function resolveCustomProvider(
        array $config,
        Application $app,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
    ): ?Provider {
        if (! ($config['enabled'] ?? true)) {
            return null;
        }

        $class = $config['class'] ?? null;

        if ($class === null) {
            return null;
        }

        if ($app->bound($class)) {
            $provider = $app->make($class);

            return $provider instanceof Provider ? $provider : null;
        }

        return $app->make($class, [
            'client' => $client,
            'requestFactory' => $requestFactory,
            ...Arr::except($config, ['class', 'enabled']),
        ]);
    }
}
