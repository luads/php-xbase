<?php declare(strict_types=1);

namespace XBase\Enum;

final class Codepage
{
    const UNDEFINED = 0;
    /** @var int U.S. MS-DOS */
    const CP437 = 0x01;
    /** @var int Mazovia (Polish) MS-DOS */
    const CP620 = 0x69;
    /** @var int Greek MS-DOS (437G) */
    const CP737 = 0x6A;
    /** @var int International MS-DOS */
    const CP850 = 0x02;
    /** @var int Eastern European MS-DOS */
    const CP852 = 0x64;
    /** @var int Turkish MS-DOS */
    const CP857 = 0x6B;
    /** @var int Icelandic MS-DOS */
    const CP861 = 0x67;
    /** @var int Nordic MS-DOS */
    const CP865 = 0x66;
    /** @var int Russian MS-DOS */
    const CP866 = 0x65;
    /** @var int Thai Windows */
    const CP874 = 0x7C;
    /** @var int Kamenicky (Czech) MS-DOS */
    const CP895 = 0x68;
    /** @var int Japanese Windows */
    const CP932 = 0x7B;
    /** @var int Chinese (PRC, Singapore) Windows */
    const CP936 = 0x7A;
    /** @var int Korean Windows */
    const CP949 = 0x79;
    /** @var int Chinese (Hong Kong SAR, Taiwan) Windows */
    const CP950 = 0x78;
    /** @var int Eastern European Windows */
    const CP1250 = 0xC8;
    /** @var int Russian Windows */
    const CP1251 = 0xC9;
    /** @var int Windows ANSI */
    const CP1252 = 0x03;
    /** @var int Greek Windows */
    const CP1253 = 0xCB;
    /** @var int Turkish Windows */
    const CP1254 = 0xCA;
    /** @var int Hebrew Windows */
    const CP1255 = 0x7D;
    /** @var int Arabic Windows */
    const CP1256 = 0x7E;
    /** @var int Standard Macintosh */
    const CP10000 = 0x04;
    /** @var int Greek Macintosh */
    const CP10006 = 0x98;
    /** @var int Russian Macintosh */
    const CP10007 = 0x96;
    /** @var int Macintosh EE */
    const CP10029 = 0x97;
}
