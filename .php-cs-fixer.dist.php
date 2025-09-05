<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

$config
    ->setUsingCache(true)
    ->getFinder()
    ->in(__DIR__)
    ->notPath('classes/API/Model/WebhookEvent.php')
    ->exclude(['vendor', 'node_modules', '202']);

return $config;
