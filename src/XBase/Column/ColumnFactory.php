<?php declare(strict_types=1);

namespace XBase\Column;

use XBase\Enum\TableType;

class ColumnFactory
{
    public static function getClass(int $version): string
    {
        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return DBase7Column::class;

            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return VisualFoxproColumn::class;

            default:
                return DBaseColumn::class;
        }
    }
}
