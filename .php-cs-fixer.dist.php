<?php

$psconfig = new PrestaShop\CodingStandards\CsFixer\Config();
$rules = $psconfig->getRules();
unset($rules['visibility_required']);
$rules['modifier_keywords'] = [
    'elements' => ['property', 'method'], // exclude 'const'
];
$rules['nullable_type_declaration_for_default_null_value'] = false;
$rules['blank_line_after_opening_tag'] = false;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath('classes/API/Model/WebhookEvent.php')
    ->notPath('classes/API/Model/Webhook.php')
    ->exclude(['vendor', 'node_modules', '202']);
$config = new PhpCsFixer\Config();
$config
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder($finder);


return $config;
