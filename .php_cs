<?php

$rules = [
    '@PSR2' => true,
];

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests');

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setUsingCache(true);
