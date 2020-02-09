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
    const FLOATING = 'F';
    /** @var string Date */
    const DATE = 'D';
    /** @var string Logical - ? Y y N n T t F f (? when not initialized). */
    const LOGICAL = 'L';
    /** @var string DateTime */
    const DATETIME = 'T';
    /** @var string Index */
    const INDEX = 'I';
    /** @var string Ignore this field */
    const DBFFIELD_IGNORE_0 = '0';
}
