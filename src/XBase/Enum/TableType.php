<?php declare(strict_types=1);

namespace XBase\Enum;

/**
 * @see https://www.dbf2002.com/dbf-file-format.html
 */
final class TableType
{
    /** @var int dBase II or FoxBASE */
    const DBASE_II = 0x02;
    /** @var int FoxBASE+/dBase III plus, no memo */
    const DBASE_III_PLUS_NOMEMO = 0x03;
    /** @var int dBase 7 */
    const DBASE_7_NOMEMO = 0x04;
    /** @var int Visual FoxPro */
    const VISUAL_FOXPRO = 0x30;
    /** @var int Visual FoxPro, autoincrement enabled */
    const VISUAL_FOXPRO_AI = 0x31;
    /** @var int Visual FoxPro, Varchar, Varbinary, or Blob-enabled */
    const VISUAL_FOXPRO_VAR = 0x32;
    /** @var int dBase IV/dBase 5 - SQL table files, no memo */
    const DBASE_IV_SQL_TABLE_NOMEMO = 0x43;
    /** @var int dBase IV/dBase 5 SQL system files, no memo */
    const DBASE_IV_SQL_SYSTEM_NOMEMO = 0x63;
    /** @var int FoxBASE+/dBase III PLUS/FoxPro, with memo (*.DBT) */
    const DBASE_III_PLUS_MEMO = 0x83;
    /** @var int dBase IV/dBase 5, with memo (*.DBT) */
    const DBASE_IV_MEMO = 0x8B;
    /** @var int dBase 7, with memo (*.DBT) */
    const DBASE_7_MEMO = 0x8C;
    /** @var int dBase IV/dBase 5, SQL table files, with memo (*.DBT) */
    const DBASE_IV_SQL_TABLE_MEMO = 0xCB;
    /** @var int (*.SMT) */
    const SMT = 0xE5;
    /** @var int dBase IV/dBase 5, SQL system files, with memo */
    const DBASE_IV_SQL_SYSTEM_MEMO = 0xEB;
    /** @var int FoxPro 2.x ( or earlier) with memo (*.FPT) */
    const FOXPRO_MEMO = 0xF5;
    /** @var int FoxBASE */
    const FOXBASE = 0xFB;

    public static function isFoxpro(int $version): bool
    {
        return in_array($version, [
            self::VISUAL_FOXPRO,
            self::VISUAL_FOXPRO_AI,
            self::VISUAL_FOXPRO_VAR,
//            self::DBASE_III_PLUS_MEMO,
            self::DBASE_IV_SQL_TABLE_MEMO,
            self::FOXPRO_MEMO,
            self::FOXBASE,
        ]);
    }

    public static function isVisualFoxpro(int $version): bool
    {
        return in_array($version, [
            self::VISUAL_FOXPRO,
            self::VISUAL_FOXPRO_AI,
            self::VISUAL_FOXPRO_VAR,
        ]);
    }

    public static function hasMemo(int $version): bool
    {
        return in_array($version, [
            self::DBASE_III_PLUS_MEMO,
            self::DBASE_IV_MEMO,
            self::DBASE_IV_SQL_TABLE_MEMO,
            self::DBASE_IV_SQL_SYSTEM_MEMO,
            self::DBASE_7_MEMO,
            self::VISUAL_FOXPRO,
            self::VISUAL_FOXPRO_AI,
            self::VISUAL_FOXPRO_VAR,
            self::FOXPRO_MEMO,
        ]);
    }

    public static function getMemoTypes(int $tableType): array
    {
        if (!self::hasMemo($tableType)) {
            return [];
        }

        switch ($tableType) {
            default:
                return [FieldType::BLOB, FieldType::MEMO];
        }
    }

    public static function all(): array
    {
        return [
            self::DBASE_II,
            self::DBASE_III_PLUS_NOMEMO,
            self::DBASE_7_NOMEMO,
            self::VISUAL_FOXPRO,
            self::VISUAL_FOXPRO_AI,
            self::VISUAL_FOXPRO_VAR,
            self::DBASE_IV_SQL_TABLE_NOMEMO,
            self::DBASE_IV_SQL_SYSTEM_NOMEMO,
            self::DBASE_III_PLUS_MEMO,
            self::DBASE_IV_MEMO,
            self::DBASE_7_MEMO,
            self::DBASE_IV_SQL_TABLE_MEMO,
            self::SMT,
            self::DBASE_IV_SQL_SYSTEM_MEMO,
            self::FOXPRO_MEMO,
            self::FOXBASE,
        ];
    }

    public static function has(int $type): bool
    {
        return in_array($type, self::all());
    }
}
