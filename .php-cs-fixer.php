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
    'no_superfluous_phpdoc_tags'                       => true,
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

$stringNotationRules = [
    'explicit_string_variable'          => true,
    'heredoc_closing_marker'            => ['closing_marker' => 'EOD', 'explicit_heredoc_style' => false, 'reserved_closing_markers' => ['CSS', 'DIFF', 'HTML', 'JS', 'JSON', 'MD', 'PHP', 'PYTHON', 'RST', 'TS', 'SQL', 'XML', 'YAML', 'TEXTAREA']],
    'heredoc_to_nowdoc'                 => true,
    'multiline_string_to_heredoc'       => true,
    'no_binary_string'                  => true,
    'no_trailing_whitespace_in_string'  => true,
    'simple_to_complex_string_variable' => true,
    'single_quote'                      => ['strings_containing_single_quote_chars' => false],
    'string_implicit_backslashes'       => false,
    'string_length_to_empty'            => true,
    'string_line_ending'                => true
];

$whitespaceRules = [
    'array_indentation'                 => true,
    'blank_line_before_statement'       => ['statements' => ['break', 'continue', 'declare', 'phpdoc', 'return', 'throw', 'try', 'yield', 'yield_from']],
    'blank_line_between_import_groups'  => true,
    'compact_nullable_type_declaration' => true,
    'heredoc_indentation'               => false, // specific for this repository
    'indentation_type'                  => true,
    'line_ending'                       => true,
    'method_chaining_indentation'       => true,
    'no_extra_blank_lines'              => ['tokens' => ['attribute', 'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use']],
    'no_spaces_around_offset'           => ['positions' => ['inside', 'outside']],
    'no_trailing_whitespace'            => true,
    'no_whitespace_in_blank_line'       => true,
    'single_blank_line_at_eof'          => true,
    'spaces_inside_parentheses'         => ['space' => 'none'],
    'statement_indentation'             => ['stick_comment_to_next_continuous_control_statement' => false],
    'type_declaration_spaces'           => ['elements' => ['constant', 'function', 'property']],
    'types_spaces'                      => ['space' => 'none', 'space_multiple_catch' => 'none']
];

$rules = \array_merge($rules,
    $stringNotationRules,
    $whitespaceRules);

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/tests'
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
