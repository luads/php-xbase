<?php declare(strict_types=1);

namespace XBase\Enum;

/**
 * MDX flags.
 */
final class TableFlag
{
    const NONE = 0x00;
    /** @var int File has a structural .cdx */
    const CDX = 0x01;
    /** @var int File has a Memo field */
    const MEMO = 0x02;
    /** @var int File is a database (.dbc) */
    const DBC = 0x04;
}
