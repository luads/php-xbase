<?php declare(strict_types=1);

namespace XBase\Tests\DataConverter\Record;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Record\VisualFoxproDataConverter;
use XBase\Enum\FieldType;
use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Record\VisualFoxproRecord;
use XBase\Table\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\VisualFoxproDataConverter
 */
class VisualFoxproDataConverterTest extends TestCase
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
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'money';
        $c->type = FieldType::NUMERIC;
        $c->bytePosition = 34;
        $c->length = 20;
        $c->decimalCount = 4;

        $columns[] = $c = new Column();
        $c->name = 'image';
        $c->type = FieldType::MEMO;
        $c->bytePosition = 54;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'rate';
        $c->type = FieldType::FLOAT;
        $c->bytePosition = 58;
        $c->length = 10;
        $c->decimalCount = 2;

        $columns[] = $c = new Column();
        $c->name = 'general';
        $c->type = FieldType::GENERAL;
        $c->bytePosition = 68;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'blob';
        $c->type = FieldType::BLOB;
        $c->bytePosition = 72;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'currency';
        $c->type = FieldType::CURRENCY;
        $c->bytePosition = 76;
        $c->length = 8;

        $columns[] = $c = new Column();
        $c->name = 'datetime';
        $c->type = FieldType::DATETIME;
        $c->bytePosition = 84;
        $c->length = 8;

        $columns[] = $c = new Column();
        $c->name = 'double';
        $c->type = FieldType::DOUBLE;
        $c->bytePosition = 92;
        $c->length = 8;

        $columns[] = $c = new Column();
        $c->name = 'integer';
        $c->type = FieldType::INTEGER;
        $c->bytePosition = 100;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'ai';
        $c->type = FieldType::INTEGER;
        $c->bytePosition = 104;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'varchar';
        $c->type = FieldType::VAR_FIELD;
        $c->bytePosition = 108;
        $c->length = 10;

        $columns[] = $c = new Column();
        $c->name = 'name_bin';
        $c->type = FieldType::CHAR;
        $c->bytePosition = 118;
        $c->length = 20;

        $columns[] = $c = new Column();
        $c->name = 'bio_bin';
        $c->type = FieldType::MEMO;
        $c->bytePosition = 138;
        $c->length = 4;

        $columns[] = $c = new Column();
        $c->name = 'varbinary';
        $c->type = FieldType::VARBINARY;
        $c->bytePosition = 142;
        $c->length = 10;

        $columns[] = $c = new Column();
        $c->name = 'varchar_bi';
        $c->type = FieldType::VAR_FIELD;
        $c->bytePosition = 152;
        $c->length = 10;

        $columns[] = $c = new Column();
        $c->name = '_nullflags';
        $c->type = FieldType::IGNORE;
        $c->bytePosition = 162;
        $c->length = 2;
        //</editor-fold>

        $table = new Table();
        $table->options = ['encoding' => 'cp866'];
        $table->header = new Header();
        $table->header->columns = $columns;
        $table->header->recordByteLength = 164;

        $converter = new VisualFoxproDataConverter($table, new IconvEncoder());
        $array = $converter->fromBinaryString($rawData);
        $binaryString = $converter->toBinaryString(new VisualFoxproRecord($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGthMAACAgICAgICAgICAgICAxMi4xMjM1IAAAACAgICAgIDEuMjABAAAARhMAAOAuAAAAAAAAAUskAMjcNwBmZmZmZmYCQAAAAAABAAAAcXdlAAAAAAAAA0dyb290ICAgICAgICAgICAgICAgzhMAAKvN7wAAAAAAAANxd2UAAAAAAAADIAo='),
        ];
    }
}
