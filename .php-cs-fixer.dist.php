<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'no_superfluous_phpdoc_tags' => true,
        'ordered_imports' => true,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'simplified_null_return' => false,
        'single_line_throw' => false,
        'void_return' => true,
        'yoda_style' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
