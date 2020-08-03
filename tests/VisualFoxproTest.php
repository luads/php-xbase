<?php

namespace XBase\Tests;

use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\Table;

class VisualFoxproTest extends AbstractTestCase
{
    public function testRead(): void
    {
        $table = new Table(__DIR__.'/Resources/foxpro/visual_fox_pro6.dbf');

        self::assertSame(TableType::VISUAL_FOXPRO, $table->version);
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(true, TableType::isVisualFoxpro($table->version));
        self::assertSame(776, $table->headerLength);
        self::assertSame(90, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::CDX | TableFlag::MEMO, ord($table->mdxFlag));
        self::assertSame(0x03, $table->getLanguageCode());
        self::assertSame(15, $table->getColumnCount());
        self::assertSame(0, $table->getRecordCount());

        $i = 0;
        $columns = array_values($table->getColumns());
        self::assertSame(FieldType::CHAR, $columns[$i++]->getType());
        self::assertSame(FieldType::CHAR, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::IGNORE, $columns[$i++]->getType());

        $table->close();
    }

    public function testVfp(): void
    {
        $table = new Table(__DIR__.'/Resources/foxpro/vfp.dbf');

        self::assertSame(20, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::VISUAL_FOXPRO_VAR, $table->version);
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(936, $table->headerLength);
        self::assertSame(164, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::MEMO, ord($table->mdxFlag));
        self::assertSame(0x03, $table->getLanguageCode());

        $columns = $table->getColumns();

        //<editor-fold desc="columns">
        $memAddress = 1;
        $column = $columns['name'];
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(20, $column->getLength());
        self::assertSame(1, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['birthday'];
        self::assertSame(FieldType::DATE, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(21, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['is_man'];
        self::assertSame(FieldType::LOGICAL, $column->getType());
        self::assertSame(1, $column->getLength());
        self::assertSame(29, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['bio'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(30, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['money'];
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(20, $column->getLength());
        self::assertSame(4, $column->getDecimalCount());
        self::assertSame(34, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['image'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(54, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['rate'];
        self::assertSame(FieldType::FLOAT, $column->getType());
        self::assertSame(10, $column->getLength());
        self::assertSame(58, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['general'];
        self::assertSame(FieldType::GENERAL, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(68, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['blob'];
        self::assertSame(FieldType::BLOB, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(72, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['currency'];
        self::assertSame(FieldType::CURRENCY, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(76, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['datetime'];
        self::assertSame(FieldType::DATETIME, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(84, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['double'];
        self::assertSame(FieldType::DOUBLE, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(92, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['integer'];
        self::assertSame(FieldType::INTEGER, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(100, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['ai'];
        self::assertSame(FieldType::INTEGER, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(104, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['varchar'];
        self::assertSame(FieldType::VAR_FIELD, $column->getType());
        self::assertSame(10, $column->getLength());
        self::assertSame(108, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['name_bin'];
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(20, $column->getLength());
        self::assertSame(118, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['bio_bin'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(138, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['varbinary'];
        self::assertSame(FieldType::VARBINARY, $column->getType());
        self::assertSame(10, $column->getLength());
        self::assertSame(142, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['varchar_bi'];
        self::assertSame(FieldType::VAR_FIELD, $column->getType());
        self::assertSame(10, $column->getLength());
        self::assertSame(152, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        $column = $columns['_nullflags'];
        self::assertSame(FieldType::IGNORE, $column->getType());
        self::assertSame(2, $column->getLength());
        self::assertSame(162, $column->getMemAddress());
        self::assertSame($memAddress, $column->getMemAddress());
        $memAddress += $column->getLength();

        self::assertSame(164, $memAddress);
        unset($column, $columns, $memAddress);
        //</editor-fold>

        $record = $table->nextRecord();
        self::assertSame('Groot', $record->getString('name'));
        self::assertSame('1960-11-01', $record->getDateTimeObject('birthday')->format('Y-m-d'));
        self::assertSame(false, $record->getBoolean('is_man'));
        $bio = <<<TEXT
Groot (/?ru?t/) is a fictional character appearing in American comic books published by Marvel Comics. Created by Stan Lee, Larry Lieber and Jack Kirby, the character first appeared in Tales to Astonish #13 (November 1960). An extraterrestrial, sentient tree-like creature, the original Groot first appeared as an invader that intended to capture humans for experimentation.

The character was reintroduced as a heroic, noble being in 2006, and appeared in the crossover comic book storyline "Annihilation: Conquest". Groot went on to star in its spin-off series, Guardians of the Galaxy, joining the team of the same name. Groot has been featured in a variety of associated Marvel merchandise, including animated television series, toys and trading cards. Vin Diesel voices Groot in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019), while Krystian Godlewski played the character via performance capture in the first film. Fred Tatasciore voices Groot on the Disney California Adventure ride Guardians of the Galaxy: Mission Breakout. Diesel will return to voice the character in Guardians of the Galaxy Vol. 3. Diesel also voiced Groot as a cameo in the 2018 Disney animated film Ralph Breaks the Internet. Since his film premiere and animated series debut, Groot has become a pop culture icon, with his repeated line "I am Groot" becoming an Internet meme.
TEXT;
        self::assertSame($bio, str_replace("\r\n", "\n", trim($record->getMemo('bio'))));
        self::assertSame(12.1235, $record->getNum('money'));
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType());
        self::assertSame(27297, strlen($memoImg->getData()));
        self::assertSame(1.2, $record->getFloat('rate'));
        self::assertSame(1, $record->getString('general'));
        self::assertSame([70, 19], array_values(unpack('C*', $record->getString('blob'))));
        self::assertSame(1.2, $record->getString('currency'));
        self::assertSame(-5364658739, $record->getDateTime('datetime'));
        self::assertSame('1800-01-01 01:01:01', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        self::assertSame(2.3, $record->getString('double'));
        self::assertSame(0, $record->getString('integer'));
        self::assertSame(1, $record->getString('ai'));
        self::assertSame('qwe', $record->getString('varchar'));
        self::assertSame('Groot', $record->getString('name_bin'));
        self::assertSame($bio, str_replace("\r\n", "\n", trim($record->getString('bio_bin'))));
        self::assertSame([0xAB, 0xCD, 0xEF], array_values(unpack('C*', $record->getString('varbinary'))));
        self::assertSame('qwe', $record->getString('varchar_bi'));

        $record = $table->nextRecord();
        self::assertSame('Rocket Raccoon', $record->getString('name'));
        self::assertSame('1976-06-01', $record->getDateTimeObject('birthday')->format('Y-m-d'));
        self::assertSame(false, $record->getBoolean('is_man'));
        $bio = <<<TEXT
Rocket Raccoon is a fictional character appearing in American comic books published by Marvel Comics. Created by writer Bill Mantlo and artist Keith Giffen, the character first appeared in Marvel Preview #7 (Summer 1976). He is an intelligent, anthropomorphic raccoon, who is an expert marksman, weapon specialist and master tactician. His name and aspects of his character are a nod to The Beatles' 1968 song "Rocky Raccoon". Rocket Raccoon appeared as a prominent member in the 2008 relaunch of the superhero team Guardians of the Galaxy.

The character has appeared in several media adaptations as a member of that team, including animated television series, toys and video games. He appears in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019). In these appearances, Rocket Raccoon is voiced by Bradley Cooper, with motion capture provided by Sean Gunn.
TEXT;
        self::assertSame(trim($bio), str_replace("\r\n", "\n", trim($record->getMemo('bio'))));
        self::assertSame(325.32, $record->getNum('money'));
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType());
        self::assertSame(95714, strlen($memoImg->getData()));
        self::assertSame(1.23, $record->getFloat('rate'));
        self::assertSame(2, $record->getString('general'));
        self::assertSame(null, $record->getString('blob'));
        self::assertSame(1.23, $record->getString('currency'));
        self::assertSame(0, $record->getDateTime('datetime'));
        self::assertSame('1970-01-01 00:00:00', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        self::assertSame(4.56, $record->getString('double'));
        self::assertSame(1, $record->getString('integer'));
        self::assertSame(2, $record->getString('ai'));
        self::assertSame('asd', $record->getString('varchar')); //todo varchar
        self::assertSame('Rocket Raccoon', $record->getString('name_bin'));
        self::assertSame($bio, str_replace("\r\n", "\n", trim($record->getString('bio_bin'))));
        self::assertSame([0x12, 0x34], array_values(unpack('C*', $record->getString('varbinary'))));
        self::assertSame('asd', $record->getString('varchar_bi'));

        $record = $table->nextRecord();
        self::assertSame('Star-Lord', $record->getString('name'));
        self::assertSame('1976-01-01', $record->getDateTimeObject('birthday')->format('Y-m-d'));
        self::assertSame(true, $record->getBoolean('is_man'));
        $bio = <<<TEXT
Star-Lord (Peter Jason Quill) is a fictional superhero appearing in American comic books published by Marvel Comics. The character, created by Steve Englehart and Steve Gan, first appeared in Marvel Preview #4 (January 1976). The son of human Meredith Quill and Spartoi J'son, Peter Quill assumes the mantle of Star-Lord, an interplanetary policeman.

The character played prominent roles in the comic book storylines "Annihilation" (2006) and "Annihilation: Conquest" (2007), "War of Kings" (2008), and The Thanos Imperative (2009). He became the leader of the space-based superhero team Guardians of the Galaxy in the 2008 relaunch of the comic of the same name. He has been featured in a variety of associated Marvel merchandise, including animated television series, toys and trading cards.

Chris Pratt portrays the character in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019). Wyatt Oleff portrays a young Peter Quill in the first two Guardians of the Galaxy films. Pratt will return to play the character in Guardians of the Galaxy Vol. 3.
TEXT;
        self::assertSame(trim($bio), str_replace("\r\n", "\n", trim($record->getMemo('bio'))));
        self::assertSame(0.0, $record->getNum('money'));
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType());
        self::assertSame(187811, strlen($memoImg->getData()));
        self::assertSame(15.16, $record->getFloat('rate'));
        self::assertSame(3, $record->getString('general'));
        self::assertSame(null, $record->getString('blob'));
        self::assertSame(15.16, $record->getString('currency'));
        self::assertSame(1582230020, $record->getDateTime('datetime'));
        self::assertSame('2020-02-20 20:20:20', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        self::assertSame(987.654, $record->getString('double'));
        self::assertSame(2, $record->getString('integer'));
        self::assertSame(3, $record->getString('ai'));
        self::assertSame('zxc', $record->getString('varchar')); //todo varchar
        self::assertSame('Star-Lord', $record->getString('name_bin'));
        self::assertSame($bio, str_replace("\r\n", "\n", trim($record->getString('bio_bin'))));
        self::assertSame([0xFA, 0xCE, 0x8D], array_values(unpack('C*', $record->getString('varbinary'))));
        self::assertSame(null, $record->getString('varchar_bi'));
    }

    public function testCurrency(): void
    {
        $table = new Table(__DIR__.'/Resources/foxpro/currency.dbf');

        self::assertSame(1, $table->getColumnCount());
        self::assertSame(1, $table->getRecordCount());
        self::assertSame(TableType::VISUAL_FOXPRO, $table->version);
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(true, $table->isFoxpro());

        $column = $table->getColumn('amount');
        self::assertSame(8, $column->getLength());
        self::assertSame(0, $column->getDecimalCount());

        $record = $table->nextRecord();
        self::assertSame(10412.1241, $record->getCurrency('amount'));
    }
}
