<?php declare(strict_types=1);

namespace XBase\Tests\DataConverter\Record;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Record\DBaseDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Memo\MemoObject;
use XBase\Record\DBaseRecord;
use XBase\Table\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\DBaseDataConverter
 */
class DBaseDataConverterTest extends TestCase
{
    public function testFromBinaryString(): void
    {
        $base64RowData = 'IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGICAgICAgICAgMSAgICAgICAgICAgICAxMi4xMjM1ICAgICAgICAgNA==';

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

        $table = new Table();
        $table->options = ['encoding' => 'cp866'];
        $table->header = new Header();
        $table->header->version = TableType::DBASE_III_PLUS_MEMO;
        $table->header->columns = $columns;
        $table->header->recordByteLength = 70;

        $converter = new DBaseDataConverter($table, new IconvEncoder());
        $rawData = base64_decode($base64RowData);
        $array = $converter->fromBinaryString($rawData);
        self::assertNotEmpty($array);
        self::assertSame(false, $array['deleted']);
        self::assertSame('Groot', $array['data']['name']);
        self::assertSame('19601101', $array['data']['birthday']);
        self::assertSame(false, $array['data']['is_man']);
        self::assertSame(12.1235, $array['data']['money']);
        /* @var MemoObject $memoBio */
        self::assertSame(1, $array['data']['bio']);
        /* @var MemoObject $memoImage */
        self::assertSame(4, $array['data']['image']);
        // opposite force
        $binaryString = $converter->toBinaryString(new DBaseRecord($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }
}
