<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ValueObject;

/**
 * @api
 */
final class PhpVersionFeature
{
    /**
     * @var int
     */
    public const PROPERTY_MODIFIER = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_52;
    /**
     * @var int
     */
    public const CONTINUE_TO_BREAK = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_52;
    /**
     * @var int
     */
    public const NO_REFERENCE_IN_NEW = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const SERVER_VAR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const DIR_CONSTANT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const ELVIS_OPERATOR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const ANONYMOUS_FUNCTION_PARAM_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const NO_ZERO_BREAK = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_54;
    /**
     * @var int
     */
    public const NO_REFERENCE_IN_ARG = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_54;
    /**
     * @var int
     */
    public const SHORT_ARRAY = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_54;
    /**
     * @var int
     */
    public const DATE_TIME_INTERFACE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_55;
    /**
     * @see https://wiki.php.net/rfc/class_name_scalars
     * @var int
     */
    public const CLASSNAME_CONSTANT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_55;
    /**
     * @var int
     */
    public const PREG_REPLACE_CALLBACK_E_MODIFIER = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_55;
    /**
     * @var int
     */
    public const EXP_OPERATOR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_56;
    /**
     * @var int
     */
    public const REQUIRE_DEFAULT_VALUE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_56;
    /**
     * @var int
     */
    public const SCALAR_TYPES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const HAS_RETURN_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NULL_COALESCE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const LIST_SWAP_ORDER = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const SPACESHIP = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const DIRNAME_LEVELS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const CSPRNG_FUNCTIONS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const THROWABLE_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_LIST_SPLIT_STRING = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_BREAK_OUTSIDE_LOOP = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_PHP4_CONSTRUCTOR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_CALL_USER_METHOD = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_EREG_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const VARIABLE_ON_FUNC_CALL = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_MKTIME_WITHOUT_ARG = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_EMPTY_LIST = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @see https://php.watch/versions/8.0/non-static-static-call-fatal-error
     * Deprecated since PHP 7.0
     *
     * @var int
     */
    public const STATIC_CALL_ON_NON_STATIC = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const INSTANCE_CALL = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_MULTIPLE_DEFAULT_SWITCH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const WRAP_VARIABLE_VARIABLE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const ANONYMOUS_FUNCTION_RETURN_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const ITERABLE_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const VOID_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const CONSTANT_VISIBILITY = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const ARRAY_DESTRUCT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const MULTI_EXCEPTION_CATCH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const NO_ASSIGN_ARRAY_TO_STRING = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const BINARY_OP_NUMBER_STRING = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const NO_EXTRA_PARAMETERS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const RESERVED_OBJECT_KEYWORD = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const DEPRECATE_EACH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const OBJECT_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const NO_EACH_OUTSIDE_LOOP = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const DEPRECATE_CREATE_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const NO_NULL_ON_GET_CLASS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const INVERTED_BOOL_IS_OBJECT_INCOMPLETE_CLASS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const RESULT_ARG_IN_PARSE_STR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const STRING_IN_FIRST_DEFINE_ARG = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const STRING_IN_ASSERT_ARG = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const NO_UNSET_CAST = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const IS_COUNTABLE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const ARRAY_KEY_FIRST_LAST = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     * @see https://php.watch/versions/8.5/array_first-array_last
     */
    public const ARRAY_FIRST_LAST = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @var int
     */
    public const JSON_EXCEPTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const SETCOOKIE_ACCEPT_ARRAY_OPTIONS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const DEPRECATE_INSENSITIVE_CONSTANT_NAME = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const ESCAPE_DASH_IN_REGEX = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const DEPRECATE_INSENSITIVE_CONSTANT_DEFINE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const DEPRECATE_INT_IN_STR_NEEDLES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const SENSITIVE_HERE_NOW_DOC = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const ARROW_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const LITERAL_SEPARATOR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const NULL_COALESCE_ASSIGN = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const TYPED_PROPERTIES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @see https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters
     * @var int
     */
    public const COVARIANT_RETURN = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const ARRAY_SPREAD = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_CURLY_BRACKET_ARRAY_STRING = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_REAL = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_MONEY_FORMAT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const ARRAY_KEY_EXISTS_TO_PROPERTY_EXISTS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const FILTER_VAR_TO_ADD_SLASHES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const CHANGE_MB_STRPOS_ARG_POSITION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const RESERVED_FN_FUNCTION_NAME = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const REFLECTION_TYPE_GETNAME = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const EXPORT_TO_REFLECTION_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_NESTED_TERNARY = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_RESTORE_INCLUDE_PATH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_HEBREVC = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const UNION_TYPES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const CLASS_ON_OBJECT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STATIC_RETURN_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const NO_FINAL_PRIVATE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const DEPRECATE_REQUIRED_PARAMETER_AFTER_OPTIONAL = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STATIC_VISIBILITY_SET_STATE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const NULLSAFE_OPERATOR = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const IS_ITERABLE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const NULLABLE_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const PARENT_VISIBILITY_OVERRIDE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const COUNT_ON_NULL = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_71;
    /**
     * @see https://wiki.php.net/rfc/constructor_promotion
     * @var int
     */
    public const PROPERTY_PROMOTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/attributes_v2
     * @var int
     */
    public const ATTRIBUTES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STRINGABLE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const PHP_TOKEN = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STR_ENDS_WITH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STR_STARTS_WITH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STR_CONTAINS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const GET_DEBUG_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/noreturn_type
     * @var int
     */
    public const NEVER_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/variadics
     * @var int
     */
    public const VARIADIC_PARAM = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_56;
    /**
     * @see https://wiki.php.net/rfc/readonly_and_immutable_properties
     * @var int
     */
    public const READONLY_PROPERTY = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/final_class_const
     * @var int
     */
    public const FINAL_CLASS_CONSTANTS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/enumerations
     * @var int
     */
    public const ENUM = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/match_expression_v2
     * @var int
     */
    public const MATCH_EXPRESSION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/non-capturing_catches
     * @var int
     */
    public const NON_CAPTURING_CATCH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.resource2object
     * @var int
     */
    public const PHP8_RESOURCE_TO_OBJECT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/lsp_errors
     * @var int
     */
    public const FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.resource2object
     * @var int
     */
    public const PHP81_RESOURCE_TO_OBJECT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/new_in_initializers
     * @var int
     */
    public const NEW_INITIALIZERS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/pure-intersection-types
     * @var int
     */
    public const INTERSECTION_TYPES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://php.watch/versions/8.2/dnf-types
     * @var int
     */
    public const UNION_INTERSECTION_TYPES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/array_unpacking_string_keys
     * @var int
     */
    public const ARRAY_SPREAD_STRING_KEYS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/internal_method_return_types
     * @var int
     */
    public const RETURN_TYPE_WILL_CHANGE_ATTRIBUTE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/first_class_callable_syntax
     * @var int
     */
    public const FIRST_CLASS_CALLABLE_SYNTAX = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/deprecate_dynamic_properties
     * @var int
     */
    public const DEPRECATE_DYNAMIC_PROPERTIES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/readonly_classes
     * @var int
     */
    public const READONLY_CLASS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://www.php.net/manual/en/migration83.new-features.php#migration83.new-features.core.readonly-modifier-improvements
     * @var int
     */
    public const READONLY_ANONYMOUS_CLASS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @var int
     * @see https://wiki.php.net/rfc/json_validate
     */
    public const JSON_VALIDATE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @see https://wiki.php.net/rfc/mixed_type_v2
     * @var int
     */
    public const MIXED_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://3v4l.org/OWtO5
     * @var int
     */
    public const ARRAY_ON_ARRAY_MERGE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const DEPRECATE_NULL_ARG_IN_STRING_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/remove_utf8_decode_and_utf8_encode
     * @var int
     */
    public const DEPRECATE_UTF8_DECODE_ENCODE_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://www.php.net/manual/en/filesystemiterator.construct
     * @var int
     */
    public const FILESYSTEM_ITERATOR_SKIP_DOTS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/null-false-standalone-types
     * @see https://wiki.php.net/rfc/true-type
     *
     * @var int
     */
    public const NULL_FALSE_TRUE_STANDALONE_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/redact_parameters_in_back_traces
     * @var int
     */
    public const SENSITIVE_PARAMETER_ATTRIBUTE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/deprecate_dollar_brace_string_interpolation
     * @var int
     */
    public const DEPRECATE_VARIABLE_IN_STRING_INTERPOLATION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/marking_overriden_methods
     * @var int
     */
    public const OVERRIDE_ATTRIBUTE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @see https://wiki.php.net/rfc/typed_class_constants
     * @var int
     */
    public const TYPED_CLASS_CONSTANTS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @see https://wiki.php.net/rfc/dynamic_class_constant_fetch
     * @var int
     */
    public const DYNAMIC_CLASS_CONST_FETCH = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @see https://wiki.php.net/rfc/deprecate-implicitly-nullable-types
     * @var int
     */
    public const DEPRECATE_IMPLICIT_NULLABLE_PARAM_TYPE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://wiki.php.net/rfc/new_without_parentheses
     * @var int
     */
    public const NEW_METHOD_CALL_WITHOUT_PARENTHESES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://wiki.php.net/rfc/correctly_name_the_rounding_mode_and_make_it_an_enum
     * @var int
     */
    public const ROUNDING_MODES = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://php.watch/versions/8.4/csv-functions-escape-parameter
     * @var int
     */
    public const REQUIRED_ESCAPE_PARAMETER = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://www.php.net/manual/en/migration83.deprecated.php#migration83.deprecated.ldap
     * @var int
     */
    public const DEPRECATE_HOST_PORT_SEPARATE_ARGS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @see https://www.php.net/manual/en/migration83.deprecated.php#migration83.deprecated.core.get-class
     * @see https://php.watch/versions/8.3/get_class-get_parent_class-parameterless-deprecated
     * @var int
     */
    public const DEPRECATE_GET_CLASS_WITHOUT_ARGS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_83;
    /**
     * @see https://wiki.php.net/rfc/deprecated_attribute
     * @var int
     */
    public const DEPRECATED_ATTRIBUTE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
     * @var int
     */
    public const ARRAY_FIND = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
     * @var int
     */
    public const ARRAY_FIND_KEY = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
     * @var int
     */
    public const ARRAY_ALL = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
     * @var int
     */
    public const ARRAY_ANY = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_context_parameter_for_finfo_buffer
     * @var int
     */
    public const DEPRECATE_FINFO_BUFFER_CONTEXT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_debuginfo_returning_null
     * @var int
     */
    public const DEPRECATED_NULL_DEBUG_INFO_RETURN = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/attributes-on-constants
     * @var int
     */
    public const DEPRECATED_ATTRIBUTE_ON_CONSTANT = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_semicolon_after_case_in_switch_statement
     * @var int
     */
    public const COLON_AFTER_SWITCH_CASE = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_using_values_null_as_an_array_offset_and_when_calling_array_key_exists
     * @var int
     */
    public const DEPRECATE_NULL_ARG_IN_ARRAY_KEY_EXISTS_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#eprecate_passing_integers_outside_the_interval_0_255_to_chr
     * @var int
     */
    public const DEPRECATE_OUTSIDE_INTERVEL_VAL_IN_CHR_FUNCTION = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_sleep_and_wakeup_magic_methods
     * @var int
     */
    public const DEPRECATED_METHOD_SLEEP = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_sleep_and_wakeup_magic_methods
     * @var int
     */
    public const DEPRECATED_METHOD_WAKEUP = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_passing_string_which_are_not_one_byte_long_to_ord
     * @var int
     */
    public const DEPRECATE_ORD_WITH_MULTIBYTE_STRING = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/property-hooks
     * @var int
     */
    public const PROPERTY_HOOKS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_84;
    /**
     * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_backticks_as_an_alias_for_shell_exec
     * @var int
     */
    public const DEPRECATE_BACKTICKS = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
    /**
     * @see https://wiki.php.net/rfc/pipe-operator-v3
     * @var int
     */
    public const PIPE_OPERATOER = \Argtyper202511\Rector\ValueObject\PhpVersion::PHP_85;
}
