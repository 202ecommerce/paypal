<?php

namespace PaypalAddons\Prestashop\ModuleLibGuzzleAdapter\Guzzle5;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception as GuzzleExceptions;
use GuzzleHttp\Message\RequestInterface as GuzzleRequest;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;
use PaypalAddons\GuzzleHttp\Psr7\Response;
use PaypalAddons\Http\Client\Exception as HttplugException;
use PaypalAddons\Prestashop\ModuleLibGuzzleAdapter\Interfaces\HttpClientInterface;
use PaypalAddons\Psr\Http\Message\RequestInterface;
use PaypalAddons\Psr\Http\Message\ResponseInterface;

/**
 * @author GeLo <geloen.eric@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @see https://github.com/php-http/guzzle5-adapter/blob/master/src/Client.php
 */
class Client implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface|null $client
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client ?: new GuzzleClient();
    }

    /**
     * Factory method to create the Guzzle 5 adapter with custom Guzzle configuration.
     * Added after duplication of adapter.
     *
     * @param array<string, mixed> $config
     *
     * @return self
     */
    public static function createWithConfig(array $config)
    {
        return new self(new GuzzleClient($config));
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $guzzleRequest = $this->createRequest($request);

        try {
            $response = $this->client->send($guzzleRequest);
        } catch (GuzzleExceptions\TransferException $e) {
            throw $this->handleException($e, $request);
        }

        return $this->createResponse($response);
    }

    /**
     * Converts a PSR request into a Guzzle request.
     *
     * @param RequestInterface $request
     *
     * @return GuzzleRequest
     */
    private function createRequest(RequestInterface $request)
    {
        $options = [
            'exceptions' => false,
            'allow_redirects' => false,
        ];

        $options['version'] = $request->getProtocolVersion();
        $options['headers'] = $request->getHeaders();
        $body = (string) $request->getBody();
        $options['body'] = '' === $body ? null : $body;

        return $this->client->createRequest(
            $request->getMethod(),
            (string) $request->getUri(),
            $options
        );
    }

    /**
     * Converts a Guzzle response into a PSR response.
     *
     * @param GuzzleResponse $response
     *
     * @return ResponseInterface
     */
    private function createResponse(GuzzleResponse $response)
    {
        $body = $response->getBody();

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            isset($body) ? $body->detach() : null,
            $response->getProtocolVersion()
        );
    }

    /**
     * Converts a Guzzle exception into an Httplug exception.
     *
     * @param GuzzleExceptions\TransferException $exception
     * @param RequestInterface $request
     *
     * @return \Exception
     */
    private function handleException(GuzzleExceptions\TransferException $exception, RequestInterface $request)
    {
        if ($exception instanceof GuzzleExceptions\ConnectException) {
            return new HttplugException\NetworkException($exception->getMessage(), $request, $exception);
        }

        if ($exception instanceof GuzzleExceptions\RequestException) {
            // Make sure we have a response for the HttpException
            if ($exception->hasResponse()) {
                $psr7Response = $this->createResponse($exception->getResponse());

                return new HttplugException\HttpException(
                    $exception->getMessage(),
                    $request,
                    $psr7Response,
                    $exception
                );
            }

            return new HttplugException\RequestException($exception->getMessage(), $request, $exception);
        }

        return new HttplugException\TransferException($exception->getMessage(), 0, $exception);
    }
}
