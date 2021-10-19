<?php declare(strict_types=1);

namespace XBase\Tests\DataConverter\Record;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Record\DBase7DataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Record\DBase7Record;
use XBase\Table\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\DBase7DataConverter
 */
class DBase7DataConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $rawData): void
    {
        //<editor-fold desc="columns">
        $columns = [];

        $columns[] = $c = new Column();
        $c->name = 'name';
        $c->type = FieldType::CHAR;
        $c->bytePosition = 1;
        $c->length = 20;

        $columns[] = $c = new Column();
        $c->name = 'birthday';
        $c->type = FieldType::DATE;
        $c->bytePosition = 21;
        $c->length = 8;

        $columns[] = $c = new Column();
        $c->name = 'is_man';
        $c->type = FieldType::LOGICAL;
        $c->bytePosition = 29;
        $c->length = 1;

        $columns[] = $c = new Column();
        $c->name = 'bio';
        $c->type = FieldType::MEMO;
        $c->bytePosition = 30;
        $c->length = 10;

        $columns[] = $c = new Column();
        $c->name = 'money';
        $c->type = FieldType::NUMERIC;
        $c->bytePosition = 40;
        $c->length = 20;
        $c->decimalCount = 4;

        $columns[] = $c = new Column();
        $c->name = 'image';
        $c->type = FieldType::MEMO;
        $c->bytePosition = 60;
        $c->length = 10;

        $columns[] = $c = new Column();
        $c->name = 'auto_inc';
        $c->type = FieldType::AUTO_INCREMENT;
        $c->bytePosition = 70;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'integer';
        $c->type = FieldType::INTEGER;
        $c->bytePosition = 74;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'large_int';
        $c->type = FieldType::NUMERIC;
        $c->bytePosition = 78;
        $c->length = 20;
        $c->decimalCount = 4;

        $columns[] = $c = new Column();
        $c->name = 'datetime';
        $c->type = FieldType::TIMESTAMP;
        $c->bytePosition = 98;
        $c->length = 8;

        $columns[] = $c = new Column();
        $c->name = 'blob';
        $c->type = FieldType::DBASE4_BLOB;
        $c->bytePosition = 106;
        $c->length = 10;

        $columns[] = $c = new Column();
        $c->name = 'dbase_ole';
        $c->type = FieldType::GENERAL;
        $c->bytePosition = 116;
        $c->length = 10;
        //</editor-fold>

        $table = new Table();
        $table->header = new Header();
        $table->header->version = TableType::FOXPRO_MEMO;
        $table->header->columns = $columns;
        $table->header->recordByteLength = 126;

        $converter = new DBase7DataConverter($table, new IconvEncoder());
        $array = $converter->fromBinaryString($rawData);
        $binaryString = $converter->toBinaryString(new DBase7Record($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGMDAwMDAwMDAwMSAgICAgICAgICAgICAxMi4xMjM1MDAwMDAwMDAwOYAAAACAAAABICAgICAgICAgICAgICA0LjAwMDBCydEEX45kADAwMDAwMDA1ODcwMDAwMDAwMDAw'),
        ];
    }
}
