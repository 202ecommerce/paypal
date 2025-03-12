<?php

declare(strict_types=1);

namespace PaypalAddons\Prestashop\ModuleLibGuzzleAdapter\Guzzle7;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Utils;
use PaypalAddons\Prestashop\ModuleLibGuzzleAdapter\Interfaces\HttpClientInterface;
use PaypalAddons\Psr\Http\Message\RequestInterface;
use PaypalAddons\Psr\Http\Message\ResponseInterface;

/**
 * HTTP Adapter for Guzzle 7.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @see https://github.com/php-http/guzzle7-adapter/blob/master/src/Client.php
 */
class Client implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client = null)
    {
        if (!$client) {
            $client = self::buildClient();
        }

        $this->client = $client;
    }

    /**
     * Factory method to create the Guzzle 7 adapter with custom Guzzle configuration.
     *
     * @param array<string, mixed> $config
     *
     * @return self
     */
    public static function createWithConfig(array $config): Client
    {
        return new self(self::buildClient($config));
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->sendAsyncRequest($request)->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request): Promise
    {
        $promise = $this->client->sendAsync($request);

        return new Promise($promise, $request);
    }

    /**
     * Build the Guzzle client instance.
     *
     * @param array<string, mixed> $config
     */
    private static function buildClient(array $config = []): GuzzleClient
    {
        $handlerStack = new HandlerStack(Utils::chooseHandler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new GuzzleClient($config);
    }
}
