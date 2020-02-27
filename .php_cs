<?php

$rules = [
    '@PSR2'        => true,
    'single_quote' => true,
    'yoda_style'   => true,
];

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests');

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setUsingCache(true);
