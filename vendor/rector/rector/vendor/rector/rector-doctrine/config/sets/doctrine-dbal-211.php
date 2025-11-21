<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\Dbal211\Rector\MethodCall\ExtractArrayArgOnQueryBuilderSelectRector;
use Argtyper202511\Rector\Doctrine\Dbal211\Rector\MethodCall\ReplaceFetchAllMethodCallRector;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig): void {
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'TARRAY', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'ARRAY'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'SIMPLE_ARRAY', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'SIMPLE_ARRAY'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'JSON_ARRAY', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'JSON_ARRAY'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'JSON', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'JSON'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'BIGINT', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'BIGINT'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'BOOLEAN', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'BOOLEAN'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATETIME', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATETIME_MUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATETIME_IMMUTABLE', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATETIME_IMMUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATETIMETZ', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATETIMETZ_MUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATETIMETZ_IMMUTABLE', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATETIMETZ_IMMUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATE', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATE_MUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATE_IMMUTABLE', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATE_IMMUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'TIME', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'TIME_MUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'TIME_IMMUTABLE', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'TIME_IMMUTABLE'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DECIMAL', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DECIMAL'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'INTEGER', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'INTEGER'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'OBJECT', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'OBJECT'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'SMALLINT', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'SMALLINT'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'STRING', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'STRING'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'TEXT', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'TEXT'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'BINARY', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'BINARY'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'BLOB', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'BLOB'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'FLOAT', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'FLOAT'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'GUID', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'GUID'), new RenameClassAndConstFetch('Argtyper202511\Doctrine\DBAL\Types\Type', 'DATEINTERVAL', 'Argtyper202511\Doctrine\DBAL\Types\Types', 'DATEINTERVAL')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecations-in-the-wrapper-connection-class
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'executeUpdate', 'executeStatement'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'exec', 'executeStatement'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'query', 'executeQuery'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#driverexceptiongeterrorcode-is-deprecated
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Driver\DriverException', 'getErrorCode', 'getSQLState'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-expressionbuilder-methods
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Query\Expression\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Query\Expression\ExpressionBuilder', 'orX', 'or'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-compositeexpression-methods
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Query\Expression\CompositeExpression', 'add', 'with'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Query\Expression\CompositeExpression', 'addMultiple', 'with'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-fetchmode-and-the-corresponding-methods
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'fetchArray', 'fetchNumeric'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Connection', 'fetchAll', 'fetchAllAssociative'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Statement', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Statement', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Statement', 'fetchAll', 'fetchAllAssociative'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Result', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Result', 'fetchArray', 'fetchNumeric'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Result', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Argtyper202511\Doctrine\DBAL\Result', 'fetchAll', 'fetchAllAssociative'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#pdo-related-classes-outside-of-the-pdo-namespace-are-deprecated
        'Argtyper202511\Doctrine\DBAL\Driver\PDOMySql\Driver' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\MySQL\Driver',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOOracle\Driver' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\OCI\Driver',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOPgSql\Driver' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\PgSQL\Driver',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOSqlite\Driver' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\SQLite\Driver',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOSqlsrv\Driver' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\SQLSrv\Driver',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOSqlsrv\Connection' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\SQLSrv\Connection',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOSqlsrv\Statement' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\SQLSrv\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-dbalexception
        'Argtyper202511\Doctrine\DBAL\DBALException' => 'Argtyper202511\Doctrine\DBAL\Exception',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#inconsistently-and-ambiguously-named-driver-level-classes-are-deprecated
        'Argtyper202511\Doctrine\DBAL\Driver\DriverException' => 'Argtyper202511\Doctrine\DBAL\Driver\Exception',
        'Argtyper202511\Doctrine\DBAL\Driver\AbstractDriverException' => 'Argtyper202511\Doctrine\DBAL\Driver\AbstractException',
        'Argtyper202511\Doctrine\DBAL\Driver\IBMDB2\DB2Driver' => 'Argtyper202511\Doctrine\DBAL\Driver\IBMDB2\Driver',
        'Argtyper202511\Doctrine\DBAL\Driver\IBMDB2\DB2Connection' => 'Argtyper202511\Doctrine\DBAL\Driver\IBMDB2\Connection',
        'Argtyper202511\Doctrine\DBAL\Driver\IBMDB2\DB2Statement' => 'Argtyper202511\Doctrine\DBAL\Driver\IBMDB2\Statement',
        'Argtyper202511\Doctrine\DBAL\Driver\Mysqli\MysqliConnection' => 'Argtyper202511\Doctrine\DBAL\Driver\Mysqli\Connection',
        'Argtyper202511\Doctrine\DBAL\Driver\Mysqli\MysqliStatement' => 'Argtyper202511\Doctrine\DBAL\Driver\Mysqli\Statement',
        'Argtyper202511\Doctrine\DBAL\Driver\OCI8\OCI8Connection' => 'Argtyper202511\Doctrine\DBAL\Driver\OCI8\Connection',
        'Argtyper202511\Doctrine\DBAL\Driver\OCI8\OCI8Statement' => 'Argtyper202511\Doctrine\DBAL\Driver\OCI8\Statement',
        'Argtyper202511\Doctrine\DBAL\Driver\SQLSrv\SQLSrvConnection' => 'Argtyper202511\Doctrine\DBAL\Driver\SQLSrv\Connection',
        'Argtyper202511\Doctrine\DBAL\Driver\SQLSrv\SQLSrvStatement' => 'Argtyper202511\Doctrine\DBAL\Driver\SQLSrv\Statement',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOConnection' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\Connection',
        'Argtyper202511\Doctrine\DBAL\Driver\PDOStatement' => 'Argtyper202511\Doctrine\DBAL\Driver\PDO\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-masterslaveconnection-use-primaryreadreplicaconnection
        'Argtyper202511\Doctrine\DBAL\Connections\MasterSlaveConnection' => 'Argtyper202511\Doctrine\DBAL\Connections\PrimaryReadReplicaConnection',
    ]);
    $rectorConfig->rules([
        // https://github.com/doctrine/dbal/pull/3853
        // https://github.com/doctrine/dbal/issues/3837
        ExtractArrayArgOnQueryBuilderSelectRector::class,
        ReplaceFetchAllMethodCallRector::class,
    ]);
};
