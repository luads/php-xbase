<?php

$rules = [
    '@PSR2'                      => true,
    'single_quote'               => true,
    'yoda_style'                 => true,
    'no_empty_phpdoc'            => true,
    'no_extra_blank_lines'       => true,
    'phpdoc_align'               => true,
    'phpdoc_trim'                => true,
    'no_superfluous_phpdoc_tags' => true,
];

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests');

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setUsingCache(true);
