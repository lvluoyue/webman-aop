<?php

$finder = PhpCsFixer\Finder::create()
    ->in('./src')
    ->notPath('process');
$config = new PhpCsFixer\Config;

return $config->setRules([
    '@PSR2' => true,
    '@PHP80Migration' => true,
    '@PHP81Migration' => true,
    '@PHP82Migration' => true,
    '@PHP83Migration' => true,
    '@PHP84Migration' => true,
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'array_syntax' => ['syntax' => 'short'],
    'single_space_around_construct' => true,
    'control_structure_braces' => true,
    'control_structure_continuation_position' => true,
    'declare_parentheses' => true,
    'no_multiple_statements_per_line' => true,
    'braces_position' => true,
    'statement_indentation' => true,
    'no_extra_blank_lines' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'declare_strict_types' => false,
    'heredoc_to_nowdoc' => true,
    'linebreak_after_opening_tag' => true,
    'new_with_parentheses' => false,
    'multiline_whitespace_before_semicolons' => false,
    'no_php4_constructor' => true,
    'no_unreachable_default_argument_value' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'ordered_imports' => true,
    'php_unit_strict' => false,
    'phpdoc_add_missing_param_annotation' => false,
    'phpdoc_align' => false,
    'phpdoc_annotation_without_dot' => false,
    'phpdoc_separation' => false,
    'phpdoc_to_comment' => false,
    'phpdoc_var_without_name' => true,
    'pow_to_exponentiation' => true,
    'unary_operator_spaces' => false,
    'semicolon_after_instruction' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    'yoda_style' => false,
    'native_function_invocation' => [
        'strict' => false
    ],
    'single_line_throw' => false,
    'php_unit_method_casing' => false,
    'blank_line_between_import_groups' => false,
    'global_namespace_import' => false,
    'nullable_type_declaration_for_default_null_value' => true,
])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
