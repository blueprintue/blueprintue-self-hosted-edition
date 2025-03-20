<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@Symfony'       => true,
    '@Symfony:risky' => true,
    'array_syntax'   => [
        'syntax' => 'short'
    ],
    'binary_operator_spaces' => [
        'operators' => [
            '=>' => 'align'
        ]
    ],
    'concat_space' => [
        'spacing' => 'one'
    ],
    'declare_strict_types'        => true,
    'get_class_to_class_keyword'  => false,
    'global_namespace_import'     => false,
    'linebreak_after_opening_tag' => true,
    'mb_str_functions'            => false, // turn off because of PHP 8.4
    'native_function_invocation'  => [
        'include' => [
            '@all'
        ]
    ],
    'no_php4_constructor'                              => true,
    'no_superfluous_phpdoc_tags'                       => false,
    'no_unreachable_default_argument_value'            => true,
    'no_useless_else'                                  => true,
    'no_useless_return'                                => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'ordered_imports'                                  => true,
    'php_unit_strict'                                  => true,
    'phpdoc_order'                                     => true,
    'semicolon_after_instruction'                      => true,
    'single_import_per_statement'                      => false,
    'strict_comparison'                                => true,
    'strict_param'                                     => true,
    'single_line_throw'                                => false,
    'trailing_comma_in_multiline'                      => false,
    'yoda_style'                                       => [
        'equal'            => false,
        'identical'        => false,
        'less_and_greater' => false
    ],
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->exclude(['views/www/pages', 'views/www/parts'])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new Config();

return $config->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());
