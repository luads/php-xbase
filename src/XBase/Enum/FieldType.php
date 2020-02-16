<?php

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
    /** @var string Integer */
    const INTEGER = 'I';
    /** @var string Ignore this field */
    const IGNORE = '0';
    /** @var string (dBASE V: like Memo) OLE Objects in MS Windows versions */
    const GENERAL = 'G';

    const BLOB      = 'W';
    const CURRENCY  = 'Y';
    const VAR_FIELD = 'V';
    const VARBINARY = 'Q';
}
