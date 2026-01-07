<?php

namespace Argtyper202601;

if (\PHP_VERSION_ID < 80100 && !\interface_exists('UnitEnum', \false)) {
    /**
     * @since 8.1
     */
    interface UnitEnum
    {
        /**
         * @return static[]
         */
        public static function cases(): array;
    }
    /**
     * @since 8.1
     */
    \class_alias('Argtyper202601\UnitEnum', 'UnitEnum', \false);
}
if (\PHP_VERSION_ID < 80100 && !\interface_exists('BackedEnum', \false)) {
    /**
     * @since 8.1
     */
    interface BackedEnum extends \UnitEnum
    {
        /**
         * @param int|string $value
         * @return $this
         */
        public static function from($value);
        /**
         * @param int|string $value
         * @return $this|null
         */
        public static function tryFrom($value);
    }
    /**
     * @since 8.1
     */
    \class_alias('Argtyper202601\BackedEnum', 'BackedEnum', \false);
}
