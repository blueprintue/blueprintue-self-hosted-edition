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

$phpUnitRules = [
    'php_unit_assert_new_names'              => true,
    'php_unit_attributes'                    => ['keep_annotations' => false],
    'php_unit_construct'                     => ['assertions' => ['assertEquals', 'assertNotEquals', 'assertNotSame', 'assertSame']],
    'php_unit_data_provider_method_order'    => ['placement' => 'before'],
    'php_unit_data_provider_name'            => ['prefix' => 'provide', 'suffix' => 'DataCases'],
    'php_unit_data_provider_return_type'     => true,
    'php_unit_data_provider_static'          => ['force' => true],
    'php_unit_dedicate_assert'               => ['target' => 'newest'],
    'php_unit_dedicate_assert_internal_type' => ['target' => 'newest'],
    'php_unit_expectation'                   => ['target' => 'newest'],
    'php_unit_fqcn_annotation'               => true,
    'php_unit_internal_class'                => ['types' => ['abstract', 'final', 'normal']],
    'php_unit_method_casing'                 => ['case' => 'camel_case'],
    'php_unit_mock'                          => ['target' => 'newest'],
    'php_unit_mock_short_will_return'        => true,
    'php_unit_namespaced'                    => ['target' => 'newest'],
    'php_unit_no_expectation_annotation'     => ['target' => 'newest', 'use_class_const' => true],
    'php_unit_set_up_tear_down_visibility'   => true,
    'php_unit_size_class'                    => false,
    'php_unit_strict'                        => ['assertions' => ['assertAttributeEquals', 'assertAttributeNotEquals', 'assertEquals', 'assertNotEquals']],
    'php_unit_test_annotation'               => ['style' => 'prefix'],
    'php_unit_test_case_static_method_calls' => ['call_type' => 'static', 'methods' => []],
    'php_unit_test_class_requires_covers'    => false
];

$phpDocRules = [
    'align_multiline_comment'                       => ['comment_type' => 'phpdocs_only'],
    'general_phpdoc_annotation_remove'              => ['annotations' => [], 'case_sensitive' => false],
    'general_phpdoc_tag_rename'                     => ['case_sensitive' => false, 'fix_annotation' => true, 'fix_inline' => true, 'replacements' => ['inheritDocs' => 'inheritDoc']],
    'no_blank_lines_after_phpdoc'                   => true,
    'no_empty_phpdoc'                               => true,
    'no_superfluous_phpdoc_tags'                    => ['allow_hidden_params' => false, 'allow_mixed' => false, 'allow_unused_params' => false, 'remove_inheritdoc' => true],
    'phpdoc_add_missing_param_annotation'           => ['only_untyped' => true],
    'phpdoc_align'                                  => ['align' => 'vertical', 'spacing' => ['_default' => 1], 'tags' => ['method', 'param', 'property', 'property-read', 'property-write', 'return', 'throws', 'type', 'var']],
    'phpdoc_annotation_without_dot'                 => true,
    'phpdoc_array_type'                             => false,
    'phpdoc_indent'                                 => true,
    'phpdoc_inline_tag_normalizer'                  => ['tags' => ['example', 'id', 'internal', 'inheritdoc', 'inheritdocs', 'link', 'source', 'toc', 'tutorial']],
    'phpdoc_line_span'                              => ['const' => 'single', 'method' => 'single', 'property' => 'single'],
    'phpdoc_list_type'                              => false,
    'phpdoc_no_access'                              => true,
    'phpdoc_no_alias_tag'                           => ['replacements' => ['property-read' => 'property', 'property-write' => 'property', 'type' => 'var', 'link' => 'see']],
    'phpdoc_no_empty_return'                        => true,
    'phpdoc_no_package'                             => true,
    'phpdoc_no_useless_inheritdoc'                  => true,
    'phpdoc_order_by_value'                         => ['annotations' => ['author', 'covers', 'coversNothing', 'dataProvider', 'depends', 'group', 'internal', 'method', 'mixin', 'property', 'property-read', 'property-write', 'requires', 'throws', 'uses']],
    'phpdoc_order'                                  => ['order' => ['param', 'throws', 'return']],
    'phpdoc_param_order'                            => true,
    'phpdoc_return_self_reference'                  => ['replacements' => ['this' => '$this', '@this' => '$this', '$self' => 'self', '@self' => 'self', '$static' => 'static', '@static' => 'static']],
    'phpdoc_scalar'                                 => ['types' => ['boolean', 'callback', 'double', 'integer', 'real', 'str']],
    'phpdoc_separation'                             => ['groups' => [['author', 'copyright', 'license'], ['category', 'package', 'subpackage'], ['property', 'property-read', 'property-write'], ['deprecated', 'link', 'see', 'since']], 'skip_unlisted_annotations' => true],
    'phpdoc_single_line_var_spacing'                => true,
    'phpdoc_summary'                                => true,
    'phpdoc_tag_casing'                             => ['tags' => ['inheritDoc']],
    'phpdoc_tag_type'                               => ['tags' => ['inheritDoc' => 'inline']],
    'phpdoc_to_comment'                             => ['allow_before_return_statement' => false, 'ignored_tags' => []],
    'phpdoc_trim_consecutive_blank_line_separation' => true,
    'phpdoc_trim'                                   => true,
    'phpdoc_types'                                  => ['groups' => ['alias', 'meta', 'simple']],
    'phpdoc_types_order'                            => ['case_sensitive' => false, 'null_adjustment' => 'always_last', 'sort_algorithm' => 'alpha'],
    'phpdoc_var_annotation_correct_order'           => true,
    'phpdoc_var_without_name'                       => true
];

$returnNotationRules = [
    'no_useless_return'      => true,
    'return_assignment'      => true,
    'simplified_null_return' => true
];

$semicolonRules = [
    'multiline_whitespace_before_semicolons'     => ['strategy' => 'no_multi_line'],
    'no_empty_statement'                         => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'semicolon_after_instruction'                => true,
    'space_after_semicolon'                      => ['remove_in_empty_for_expressions' => true]
];

$strictRules = [
    'declare_strict_types' => true,
    'strict_comparison'    => true,
    'strict_param'         => true
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
    $phpUnitRules,
    $phpDocRules,
    $returnNotationRules,
    $semicolonRules,
    $strictRules,
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
