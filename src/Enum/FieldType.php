<?php declare(strict_types=1);

namespace XBase\Enum;

final class FieldType
{
    /** @var string Memo type field */
    const MEMO = 'M';
    /** @var string Character field */
    const CHAR = 'C';
    /** @var string Double */
    const DOUBLE = 'B';
    /** @var string Numeric */
    const NUMERIC = 'N';
    /** @var string Floating point */
    const FLOAT = 'F';
    /** @var string Date */
    const DATE = 'D';
    /** @var string Logical - ? Y y N n T t F f (? when not initialized). */
    const LOGICAL = 'L';
    /** @var string DateTime */
    const DATETIME = 'T';
    const TIMESTAMP = '@';
    /** @var string Integer */
    const INTEGER = 'I';
    /** @var string Ignore this field */
    const IGNORE = '0';
    /** @var string (dBASE V: like Memo) OLE Objects in MS Windows versions */
    const GENERAL = 'G';

    const BLOB = 'W';
    const CURRENCY = 'Y';
    const VAR_FIELD = 'V';
    const VARBINARY = 'Q';
    /** @var string dBase7 */
    const AUTO_INCREMENT = '+';

    const DBASE4_BLOB = 'B';
    const DBASE7_DOUBLE = 'O';

    public static function has($type): bool
    {
        return in_array($type, self::all());
    }

    private static function all(): array
    {
        return [
            self::MEMO,
            self::CHAR,
            self::DOUBLE,
            self::NUMERIC,
            self::FLOAT,
            self::DATE,
            self::LOGICAL,
            self::DATETIME,
            self::INTEGER,
            self::IGNORE,
            self::GENERAL,
            self::BLOB,
            self::CURRENCY,
            self::VAR_FIELD,
            self::VARBINARY,
        ];
    }
}
