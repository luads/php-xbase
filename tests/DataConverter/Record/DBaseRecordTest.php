<?php declare(strict_types=1);

namespace XBase\Tests\Record;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Record\DBaseDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Memo\MemoObject;
use XBase\Memo\WritableMemoInterface;
use XBase\Record\DBaseRecord;
use XBase\Table\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\Record\DBaseRecord
 */
class DBaseRecordTest extends TestCase
{
    /**
     * @covers ::copyFrom
     */
    public function testCopyFrom(): void
    {
        $columns = [];

        $columns['name'] = $c = new Column();
        $c->name = 'name';
        $c->type = FieldType::CHAR;
        $c->bytePosition = 1;
        $c->length = 20;

        $columns['birthday'] = $c = new Column();
        $c->name = 'birthday';
        $c->type = FieldType::DATE;
        $c->bytePosition = 21;
        $c->length = 8;

        $columns['is_man'] = $c = new Column();
        $c->name = 'is_man';
        $c->type = FieldType::LOGICAL;
        $c->bytePosition = 29;
        $c->length = 1;

        $columns['bio'] = $c = new Column();
        $c->name = 'bio';
        $c->type = FieldType::MEMO;
        $c->bytePosition = 30;
        $c->length = 10;

        $columns['money'] = $c = new Column();
        $c->name = 'money';
        $c->type = FieldType::NUMERIC;
        $c->bytePosition = 40;
        $c->length = 20;
        $c->decimalCount = 4;

        $columns['image'] = $c = new Column();
        $c->name = 'image';
        $c->type = FieldType::MEMO;
        $c->bytePosition = 60;
        $c->length = 10;

        $memo = $this->createMock(WritableMemoInterface::class);
        $memo
            ->expects(self::atLeastOnce())
            ->method('create')
            ->willReturnMap([
                ['memo text', 1],
                ['memo_image_data', 2],
            ]);
        $memo
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [1, new MemoObject('memo text')],
                [2, new MemoObject('memo_image_data')],
            ]);

        $table = new Table();
        $table->options = ['encoding' => 'cp866'];
        $table->header = new Header();
        $table->header->version = TableType::DBASE_III_PLUS_MEMO;
        $table->header->columns = $columns;
        $table->header->recordByteLength = 70;
        $table->memo = $memo;

        $converter = new DBaseDataConverter($table, new IconvEncoder());

        $record1 = new DBaseRecord($table, 1, $converter->fromBinaryString(''));
        $record1
            ->set('name', 'Groot')
            ->set('birthday', \DateTime::createFromFormat('Ymd', '19601101'))
            ->set('is_man', false)
            ->set('money', 12.1235)
            ->set('bio', 'memo text')
            ->set('image', 'memo_image_data');

        $record2 = new DBaseRecord($table, 2);
        $record2->copyFrom($record1);

        self::assertSame($converter->toBinaryString($record1), $converter->toBinaryString($record2));
    }
}
