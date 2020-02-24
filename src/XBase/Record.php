<?php

namespace XBase;

use XBase\Record\VisualFoxproRecord;

/**
 * @deprecated since 1.2. For constants use FieldType class. You should create new instance via RecordFactory.
 */
class Record extends VisualFoxproRecord
{
    /** @var string Memo type field */
    const DBFFIELD_TYPE_MEMO = 'M';
    /** @var string Character field */
    const DBFFIELD_TYPE_CHAR = 'C';
    /** @var string Double */
    const DBFFIELD_TYPE_DOUBLE = 'B';
    /** @var string Numeric */
    const DBFFIELD_TYPE_NUMERIC = 'N';
    /** @var string Floating point */
    const DBFFIELD_TYPE_FLOATING = 'F';
    /** @var string Date */
    const DBFFIELD_TYPE_DATE = 'D';
    /** @var string Logical - ? Y y N n T t F f (? when not initialized). */
    const DBFFIELD_TYPE_LOGICAL = 'L';
    /** @var string DateTime */
    const DBFFIELD_TYPE_DATETIME = 'T';
    /** @var string Index */
    const DBFFIELD_TYPE_INDEX = 'I';
    /** @var string Ignore this field */
    const DBFFIELD_IGNORE_0 = '0';
}
