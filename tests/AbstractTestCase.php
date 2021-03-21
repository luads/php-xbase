<?php declare(strict_types=1);

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\FieldType;
use XBase\Memo\MemoObject;
use XBase\TableReader;

abstract class AbstractTestCase extends TestCase
{
    protected function assertRecords(TableReader $table)
    {
        $columns = $table->getColumns();

        //<editor-fold desc="columns">
        $column = $columns['name'];
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(20, $column->getLength());
        $column = $columns['birthday'];
        self::assertSame(FieldType::DATE, $column->getType());
        self::assertSame(8, $column->getLength());
        $column = $columns['is_man'];
        self::assertSame(FieldType::LOGICAL, $column->getType());
        self::assertSame(1, $column->getLength());
        $column = $columns['bio'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(10, $column->getLength());
//        self::assertSame(30, $column->getMemAddress());
        $column = $columns['money'];
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(20, $column->getLength());
        self::assertSame(4, $column->getDecimalCount());
        $column = $columns['image'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(10, $column->getLength());
//        self::assertSame(60, $column->getMemAddress());
        unset($column, $columns);
        //</editor-fold>

        $record = $table->nextRecord();
        self::assertSame('Groot', $record->get('name'));
        self::assertSame('1960-11-01', $record->getDateTimeObject('birthday')->format('Y-m-d'));
        self::assertSame(false, $record->get('is_man'));
        $str = <<<TEXT
Groot (/?ru?t/) is a fictional character appearing in American comic books published by Marvel Comics. Created by Stan Lee, Larry Lieber and Jack Kirby, the character first appeared in Tales to Astonish #13 (November 1960). An extraterrestrial, sentient tree-like creature, the original Groot first appeared as an invader that intended to capture humans for experimentation.

The character was reintroduced as a heroic, noble being in 2006, and appeared in the crossover comic book storyline "Annihilation: Conquest". Groot went on to star in its spin-off series, Guardians of the Galaxy, joining the team of the same name. Groot has been featured in a variety of associated Marvel merchandise, including animated television series, toys and trading cards. Vin Diesel voices Groot in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019), while Krystian Godlewski played the character via performance capture in the first film. Fred Tatasciore voices Groot on the Disney California Adventure ride Guardians of the Galaxy: Mission Breakout. Diesel will return to voice the character in Guardians of the Galaxy Vol. 3. Diesel also voiced Groot as a cameo in the 2018 Disney animated film Ralph Breaks the Internet. Since his film premiere and animated series debut, Groot has become a pop culture icon, with his repeated line "I am Groot" becoming an Internet meme. 
TEXT;
        self::assertSame(trim($str), str_replace("\r\n", "\n", trim($record->get('bio'))));
        self::assertSame(12.1235, $record->get('money'));
        /** @var MemoObject $memoImg */
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType());
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData()));

        $record = $table->nextRecord();
        self::assertSame('Rocket Raccoon', $record->get('name'));
        self::assertSame('1976-06-01', $record->getDateTimeObject('birthday')->format('Y-m-d'));
        self::assertSame(false, $record->get('is_man'));
        $str = <<<TEXT
Rocket Raccoon is a fictional character appearing in American comic books published by Marvel Comics. Created by writer Bill Mantlo and artist Keith Giffen, the character first appeared in Marvel Preview #7 (Summer 1976). He is an intelligent, anthropomorphic raccoon, who is an expert marksman, weapon specialist and master tactician. His name and aspects of his character are a nod to The Beatles' 1968 song "Rocky Raccoon". Rocket Raccoon appeared as a prominent member in the 2008 relaunch of the superhero team Guardians of the Galaxy.

The character has appeared in several media adaptations as a member of that team, including animated television series, toys and video games. He appears in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019). In these appearances, Rocket Raccoon is voiced by Bradley Cooper, with motion capture provided by Sean Gunn. 
TEXT;
        self::assertSame(trim($str), str_replace("\r\n", "\n", trim($record->get('bio'))));
        self::assertSame(325.32, $record->get('money'));
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType());
//        self::assertSame(95714, strlen($memoImg->getData()));

        $record = $table->nextRecord();
        self::assertSame('Star-Lord', $record->get('name'));
        self::assertSame('1976-01-01', $record->getDateTimeObject('birthday')->format('Y-m-d'));
        self::assertSame(true, $record->get('is_man'));
        $str = <<<TEXT
Star-Lord (Peter Jason Quill) is a fictional superhero appearing in American comic books published by Marvel Comics. The character, created by Steve Englehart and Steve Gan, first appeared in Marvel Preview #4 (January 1976). The son of human Meredith Quill and Spartoi J'son, Peter Quill assumes the mantle of Star-Lord, an interplanetary policeman.

The character played prominent roles in the comic book storylines "Annihilation" (2006) and "Annihilation: Conquest" (2007), "War of Kings" (2008), and The Thanos Imperative (2009). He became the leader of the space-based superhero team Guardians of the Galaxy in the 2008 relaunch of the comic of the same name. He has been featured in a variety of associated Marvel merchandise, including animated television series, toys and trading cards.

Chris Pratt portrays the character in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019). Wyatt Oleff portrays a young Peter Quill in the first two Guardians of the Galaxy films. Pratt will return to play the character in Guardians of the Galaxy Vol. 3.
TEXT;
        self::assertSame(trim($str), str_replace("\r\n", "\n", trim($record->get('bio'))));
        self::assertSame(0.0, $record->get('money'));
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType());
//        self::assertSame(187811, strlen($memoImg->getData()));
//        file_put_contents('./image.png', $memoImg->getData());
    }

    protected function assertMemoImg(TableReader $table)
    {
        $record = $table->moveTo(1);
        /** @var MemoObject $memoImg */
        $memoImg = $record->getMemoObject('image');
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData())); //png
        $record = $table->nextRecord();
        $memoImg = $record->getMemoObject('image');
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData()));
    }
}
