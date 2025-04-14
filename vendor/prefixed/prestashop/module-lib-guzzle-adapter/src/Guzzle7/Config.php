<?php

declare(strict_types=1);

namespace PaypalAddons\Prestashop\ModuleLibGuzzleAdapter\Guzzle7;

use PaypalAddons\Prestashop\ModuleLibGuzzleAdapter\Interfaces\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public static function fixConfig(array $config): array
    {
        if (isset($config['defaults'])) {
            if (isset($config['defaults']['timeout'])) {
                $config['timeout'] = $config['defaults']['timeout'];
            }

            if (isset($config['defaults']['exceptions'])) {
                $config['http_errors'] = $config['defaults']['exceptions'];
            }

            if (isset($config['defaults']['headers'])) {
                $config['headers'] = $config['defaults']['headers'];
            }

            unset($config['defaults']);
        }

        if (isset($config['base_url'])) {
            $config['base_uri'] = $config['base_url'];

            unset($config['base_url']);
        }

        return $config;
    }
}
