<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator;

use XBase\Enum\TableType;
use XBase\Header\Column\Validator\DBase\CharValidator;
use XBase\Header\Column\Validator\DBase\DateValidator;
use XBase\Header\Column\Validator\DBase\LogicalValidator;
use XBase\Header\Column\Validator\DBase\MemoValidator;
use XBase\Header\Column\Validator\DBase\NumberValidator;
use XBase\Header\Column\Validator\DBase4\FloatValidator;
use XBase\Header\Column\Validator\DBase7\BlobValidator;
use XBase\Header\Column\Validator\DBase7\IntegerValidator;
use XBase\Header\Column\Validator\DBase7\TimestampValidator;

class ColumnValidatorFactory
{
    public static function create(int $version): ChainValidator
    {
        $validators = [
            new CharValidator(),
            new DateValidator(),
            new LogicalValidator(),
            new MemoValidator(),
            new NumberValidator(),
        ];

        switch ($version) {
            case TableType::DBASE_IV_SQL_SYSTEM_MEMO:
            case TableType::DBASE_IV_SQL_SYSTEM_NOMEMO:
            case TableType::DBASE_IV_SQL_TABLE_MEMO:
            case TableType::DBASE_IV_SQL_TABLE_NOMEMO:
            case TableType::DBASE_IV_MEMO:
                $validators[] = new FloatValidator();
                break;
            case TableType::DBASE_7_MEMO:
                $validators = array_merge($validators, [
                    new BlobValidator(),
                    new IntegerValidator(),
                    new TimestampValidator(),
                ]);
                break;
        }

        return new ChainValidator($validators);
    }
}
