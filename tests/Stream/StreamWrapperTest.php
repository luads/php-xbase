<?php declare(strict_types=1);

namespace XBase\Tests\Stream;

use PHPUnit\Framework\TestCase;
use XBase\Stream\StreamWrapper;

class StreamWrapperTest extends TestCase
{
    public function testMagickCall(): void
    {
        $stream = new StreamWrapper(fopen('php://temp', 'r+'));
        $stream->write('abcdefghiklmnopq');
        // unsigned
        self::assertTrue($stream->rewind());
        self::assertEquals(0x61, $stream->readUChar());
        self::assertTrue($stream->rewind());
        self::assertEquals(0x6261, $stream->readUShort());
        self::assertTrue($stream->rewind());
        self::assertEquals(0x64636261, $stream->readUInt());
        // signed
        self::assertTrue($stream->rewind());
        self::assertEquals(0x61, $stream->readChar());
        self::assertTrue($stream->rewind());
        self::assertEquals(0x6261, $stream->readShort());
        self::assertTrue($stream->rewind());
        self::assertEquals(0x64636261, $stream->readInt());
    }
}
