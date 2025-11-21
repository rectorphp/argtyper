<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Argtyper202511\Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Argtyper202511\Rector\Arguments\ValueObject\ArgumentAdder;
use Argtyper202511\Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Argtyper202511\Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Argtyper202511\Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Argtyper202511\Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Argtyper202511\Rector\Php80\Rector\Class_\StringableForToStringRector;
use Argtyper202511\Rector\Php80\Rector\ClassConstFetch\ClassOnThisVariableObjectRector;
use Argtyper202511\Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector;
use Argtyper202511\Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector;
use Argtyper202511\Rector\Php80\Rector\ClassMethod\SetStateToStaticRector;
use Argtyper202511\Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Argtyper202511\Rector\Php80\Rector\Identical\StrEndsWithRector;
use Argtyper202511\Rector\Php80\Rector\Identical\StrStartsWithRector;
use Argtyper202511\Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Argtyper202511\Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Argtyper202511\Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Argtyper202511\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Argtyper202511\Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([StrContainsRector::class, StrStartsWithRector::class, StrEndsWithRector::class, StringableForToStringRector::class, ClassOnObjectRector::class, GetDebugTypeRector::class, RemoveUnusedVariableInCatchRector::class, ClassPropertyAssignToConstructorPromotionRector::class, ChangeSwitchToMatchRector::class, RemoveParentCallWithoutParentRector::class, SetStateToStaticRector::class, FinalPrivateToPrivateVisibilityRector::class, AddParamBasedOnParentClassMethodRector::class, ClassOnThisVariableObjectRector::class, ConsistentImplodeRector::class, OptionalParametersAfterRequiredRector::class]);
    $rectorConfig->ruleWithConfiguration(StaticCallToFuncCallRector::class, [new StaticCallToFuncCall('Argtyper202511\Nette\Utils\Strings', 'startsWith', 'str_starts_with'), new StaticCallToFuncCall('Argtyper202511\Nette\Utils\Strings', 'endsWith', 'str_ends_with'), new StaticCallToFuncCall('Argtyper202511\Nette\Utils\Strings', 'contains', 'str_contains')]);
    // nette\utils and Strings::replace()
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Argtyper202511\Nette\Utils\Strings', 'replace', 2, 'replacement', '')]);
    // @see https://php.watch/versions/8.0/pgsql-aliases-deprecated
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['pg_clientencoding' => 'pg_client_encoding', 'pg_cmdtuples' => 'pg_affected_rows', 'pg_errormessage' => 'pg_last_error', 'pg_fieldisnull' => 'pg_field_is_null', 'pg_fieldname' => 'pg_field_name', 'pg_fieldnum' => 'pg_field_num', 'pg_fieldprtlen' => 'pg_field_prtlen', 'pg_fieldsize' => 'pg_field_size', 'pg_fieldtype' => 'pg_field_type', 'pg_freeresult' => 'pg_free_result', 'pg_getlastoid' => 'pg_last_oid', 'pg_loclose' => 'pg_lo_close', 'pg_locreate' => 'pg_lo_create', 'pg_loexport' => 'pg_lo_export', 'pg_loimport' => 'pg_lo_import', 'pg_loopen' => 'pg_lo_open', 'pg_loread' => 'pg_lo_read', 'pg_loreadall' => 'pg_lo_read_all', 'pg_lounlink' => 'pg_lo_unlink', 'pg_lowrite' => 'pg_lo_write', 'pg_numfields' => 'pg_num_fields', 'pg_numrows' => 'pg_num_rows', 'pg_result' => 'pg_fetch_result', 'pg_setclientencoding' => 'pg_set_client_encoding']);
    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'gte', 'ge'), new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'lte', 'le'), new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, '', '!='), new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, '!', '!='), new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'g', 'gt'), new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'l', 'lt'), new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'n', 'ne'), new ReplaceFuncCallArgumentDefaultValue('get_headers', 1, 0, \false), new ReplaceFuncCallArgumentDefaultValue('get_headers', 1, 1, \true)]);
};
