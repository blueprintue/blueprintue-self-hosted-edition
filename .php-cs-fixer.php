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

$classNotationRules = [
    'class_attributes_separation'              => ['elements' => ['const' => 'one', 'method' => 'one', 'property' => 'one', 'trait_import' => 'none', 'case' => 'none']],
    'class_definition'                         => ['inline_constructor_arguments' => true, 'multi_line_extends_each_single_line' => false, 'single_item_single_line' => true, 'single_line' => true, 'space_before_parenthesis' => false],
    'final_class'                              => false,
    'final_internal_class'                     => false,
    'final_public_method_for_abstract_class'   => false,
    'no_blank_lines_after_class_opening'       => true,
    'no_null_property_initialization'          => true,
    'no_php4_constructor'                      => true,
    'no_unneeded_final_method'                 => ['private_methods' => true],
    'ordered_class_elements'                   => ['case_sensitive' => false, 'order' => ['use_trait', 'case', 'constant_public', 'constant_protected', 'constant_private', 'property_public', 'property_protected', 'property_private', 'construct', 'destruct', 'magic', 'phpunit'], 'sort_algorithm' => 'none'],
    'ordered_interfaces'                       => ['case_sensitive' => false, 'direction' => 'ascend', 'order' => 'alpha'],
    'ordered_traits'                           => ['case_sensitive' => false],
    'ordered_types'                            => ['case_sensitive' => false, 'null_adjustment' => 'always_last', 'sort_algorithm' => 'alpha'],
    'phpdoc_readonly_class_comment_to_keyword' => true,
    'protected_to_private'                     => false,
    'self_accessor'                            => true,
    'self_static_accessor'                     => true,
    'single_class_element_per_statement'       => ['elements' => ['const', 'property']],
    'single_trait_insert_per_statement'        => true,
    'visibility_required'                      => ['elements' => ['const', 'method', 'property']]
];

$classUsageRules = [
    'date_time_immutable' => true
];

$commentRules = [
    'comment_to_phpdoc'                 => ['ignored_tags' => ['codeCoverageIgnoreStart', 'codeCoverageIgnoreEnd']],
    'header_comment'                    => false,
    'multiline_comment_opening_closing' => true,
    'no_empty_comment'                  => true,
    'no_trailing_whitespace_in_comment' => true,
    'single_line_comment_spacing'       => true,
    'single_line_comment_style'         => ['comment_types' => ['hash']]
];

$constantNotationRules = [
    'native_constant_invocation' => ['exclude' => ['null', 'false', 'true'], 'fix_built_in' => true, 'include' => [], 'scope' => 'all', 'strict' => false],
];

$controlStructureRules = [
    'control_structure_braces'                => true,
    'control_structure_continuation_position' => ['position' => 'same_line'],
    'elseif'                                  => true,
    'empty_loop_body'                         => ['style' => 'braces'],
    'empty_loop_condition'                    => ['style' => 'while'],
    'include'                                 => true,
    'no_alternative_syntax'                   => ['fix_non_monolithic_code' => true],
    'no_break_comment'                        => ['comment_text' => 'no break'],
    'no_superfluous_elseif'                   => true,
    'no_unneeded_braces'                      => ['namespaces' => true],
    'no_unneeded_control_parentheses'         => ['statements' => ['break', 'clone', 'continue', 'echo_print', 'others', 'return', 'switch_case', 'yield', 'yield_from']],
    'no_useless_else'                         => true,
    'simplified_if_return'                    => true,
    'switch_case_semicolon_to_colon'          => true,
    'switch_case_space'                       => true,
    'switch_continue_to_break'                => true,
    'trailing_comma_in_multiline'             => false,
    'yoda_style'                              => false
];

$doctrineAnnotationRules = [
    'doctrine_annotation_array_assignment' => false,
    'doctrine_annotation_braces'           => false,
    'doctrine_annotation_indentation'      => false,
    'doctrine_annotation_spaces'           => false
];

$functionNotationRules = [
    'combine_nested_dirname'                           => true,
    'date_time_create_from_format_call'                => true,
    'fopen_flag_order'                                 => true,
    'fopen_flags'                                      => ['b_mode' => false],
    'function_declaration'                             => ['closure_fn_spacing' => 'one', 'closure_function_spacing' => 'one', 'trailing_comma_single_line' => false],
    'implode_call'                                     => true,
    'lambda_not_used_import'                           => true,
    'method_argument_space'                            => ['after_heredoc' => false, 'attribute_placement' => 'ignore', 'keep_multiple_spaces_after_comma' => false, 'on_multiline' => 'ignore'],
    'native_function_invocation'                       => ['exclude' => [], 'include' => ['@all', '@compiler_optimized', '@internal'], 'scope' => 'all', 'strict' => false],
    'no_spaces_after_function_name'                    => true,
    'no_unreachable_default_argument_value'            => true,
    'no_useless_sprintf'                               => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'phpdoc_to_param_type'                             => false,
    'phpdoc_to_property_type'                          => false,
    'phpdoc_to_return_type'                            => false,
    'regular_callable_call'                            => true,
    'return_type_declaration'                          => ['space_before' => 'none'],
    'single_line_throw'                                => false,
    'static_lambda'                                    => true,
    'use_arrow_functions'                              => false,
    'void_return'                                      => true
];

$importRules = [
    'fully_qualified_strict_types' => ['import_symbols' => false, 'leading_backslash_in_global_namespace' => false, 'phpdoc_tags' => ['param', 'phpstan-param', 'phpstan-property', 'phpstan-property-read', 'phpstan-property-write', 'phpstan-return', 'phpstan-var', 'property', 'property-read', 'property-write', 'psalm-param', 'psalm-property', 'psalm-property-read', 'psalm-property-write', 'psalm-return', 'psalm-var', 'return', 'see', 'throws', 'var']],
    'global_namespace_import'      => ['import_classes' => false, 'import_constants' => false, 'import_functions' => false],
    'group_import'                 => false,
    'no_leading_import_slash'      => true,
    'no_unneeded_import_alias'     => true,
    'no_unused_imports'            => true,
    'ordered_imports'              => ['case_sensitive' => false, 'imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
    'single_import_per_statement'  => ['group_to_single_imports' => true],
    'single_line_after_imports'    => true
];

$languageConstructRules = [
    'class_keyword'                 => false, // specific for this repository
    'combine_consecutive_issets'    => true,
    'combine_consecutive_unsets'    => true,
    'declare_equal_normalize'       => ['space' => 'none'],
    'declare_parentheses'           => true,
    'dir_constant'                  => true,
    'error_suppression'             => ['mute_deprecation_error' => true, 'noise_remaining_usages' => false, 'noise_remaining_usages_exclude' => []],
    'explicit_indirect_variable'    => true,
    'function_to_constant'          => ['functions' => ['get_called_class', 'get_class', 'get_class_this', 'php_sapi_name', 'phpversion', 'pi']],
    'get_class_to_class_keyword'    => true,
    'is_null'                       => true,
    'no_unset_on_property'          => true,
    'nullable_type_declaration'     => ['syntax' => 'question_mark'],
    'single_space_around_construct' => ['constructs_contain_a_single_space' => ['yield_from'], 'constructs_followed_by_a_single_space' => ['abstract', 'as', 'attribute', 'break', 'case', 'catch', 'class', 'clone', 'comment', 'const', 'const_import', 'continue', 'do', 'echo', 'else', 'elseif', 'enum', 'extends', 'final', 'finally', 'for', 'foreach', 'function', 'function_import', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'match', 'named_argument', 'namespace', 'new', 'open_tag_with_echo', 'php_doc', 'php_open', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'type_colon', 'use', 'use_lambda', 'use_trait', 'var', 'while', 'yield', 'yield_from'], 'constructs_preceded_by_a_single_space' => ['as', 'else', 'elseif', 'use_lambda']]
];

$listNotationRules = [
    'list_syntax' => ['syntax' => 'short']
];

$namespaceNotationRules = [
    'blank_line_after_namespace'      => true,
    'blank_lines_before_namespace'    => ['min_line_breaks' => 2, 'max_line_breaks' => 2],
    'clean_namespace'                 => true,
    'no_leading_namespace_whitespace' => true
];

$namingRules = [
    'no_homoglyph_names' => true
];

$operatorRules = [
    'assign_null_coalescing_to_coalesce_equal' => true,
    'binary_operator_spaces'                   => ['default' => 'single_space', 'operators' => ['=>' => 'align']],
    'concat_space'                             => ['spacing' => 'one'],
    'increment_style'                          => ['style' => 'pre'],
    'logical_operators'                        => true,
    'long_to_shorthand_operator'               => true,
    'new_with_parentheses'                     => ['anonymous_class' => true, 'named_class' => true],
    'no_space_around_double_colon'             => true,
    'no_useless_concat_operator'               => ['juggle_simple_strings' => false],
    'no_useless_nullsafe_operator'             => true,
    'not_operator_with_space'                  => false,
    'not_operator_with_successor_space'        => false,
    'object_operator_without_whitespace'       => true,
    'operator_linebreak'                       => ['only_booleans' => true, 'position' => 'beginning'],
    'standardize_increment'                    => true,
    'standardize_not_equals'                   => true,
    'ternary_operator_spaces'                  => true,
    'ternary_to_elvis_operator'                => true,
    'ternary_to_null_coalescing'               => true,
    'unary_operator_spaces'                    => ['only_dec_inc' => false]
];

$phpTagRules = [
    'blank_line_after_opening_tag' => true,
    'echo_tag_syntax'              => ['format' => 'long', 'long_function' => 'echo', 'shorten_simple_statements_only' => true],
    'full_opening_tag'             => true,
    'linebreak_after_opening_tag'  => true,
    'no_closing_tag'               => true
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
    $classNotationRules,
    $classUsageRules,
    $commentRules,
    $constantNotationRules,
    $controlStructureRules,
    $doctrineAnnotationRules,
    $functionNotationRules,
    $importRules,
    $languageConstructRules,
    $listNotationRules,
    $namespaceNotationRules,
    $namingRules,
    $operatorRules,
    $phpTagRules,
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
