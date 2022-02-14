<?php declare(strict_types=1);

namespace XBase\Tests\DataConverter\Record;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Record\FoxproDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Record\FoxproRecord;
use XBase\Table\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\FoxproDataConverter
 */
class FoxproDataConverterTest extends TestCase
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
        $c->name = 'rate';
        $c->type = FieldType::FLOAT;
        $c->bytePosition = 70;
        $c->length = 10;
        $c->decimalCount = 2;

        $columns[] = $c = new Column();
        $c->name = 'general';
        $c->type = FieldType::GENERAL;
        $c->bytePosition = 80;
        $c->length = 10;
        //</editor-fold>

        $table = new Table();
        $table->header = new Header();
        $table->header->version = TableType::FOXPRO_MEMO;
        $table->header->columns = $columns;
        $table->header->recordByteLength = 90;

        $converter = new FoxproDataConverter($table, new IconvEncoder());
        $array = $converter->fromBinaryString($rawData);
        $binaryString = $converter->toBinaryString(new FoxproRecord($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGICAgICAgICAgOCAgICAgICAgICAgICAxMi4xMjM1ICAgICAgICAzMiAgICAgIDEuMjAgICAgICAgNDU5'),
        ];
    }
}
