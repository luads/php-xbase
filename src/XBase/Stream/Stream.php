<?php declare(strict_types=1);

namespace XBase\Stream;

class Stream extends StreamWrapper
{
    public static function createFromFile(string $filepath, string $mode = 'rb'): Stream
    {
        return new self(fopen($filepath, $mode));
    }

    public static function createFromString(string $string = '', string $mode = 'rb+'): Stream
    {
        $stream = fopen('php://temp', $mode);
        fwrite($stream, $string);
        rewind($stream);

        return new self($stream);
    }

    /**
     * @return int unixtime
     */
    public function read3ByteDate()
    {
        $y = unpack('c', $this->read());
        $m = unpack('c', $this->read());
        $d = unpack('c', $this->read());

        return mktime(0, 0, 0, $m[1], $d[1], $y[1] > 70 ? 1900 + $y[1] : 2000 + $y[1]);
    }

    /**
     * @param $d
     *
     * @return bool|int
     */
    public function write3ByteDate($d)
    {
        $t = getdate($d);

        return $this->writeUChar($t['year'] % 1000) + $this->writeUChar($t['mon']) + $this->writeUChar($t['mday']);
    }

    /**
     * @return false|int
     */
    public function read4ByteDate()
    {
        $y = $this->readUShort();
        $m = unpack('c', $this->read());
        $d = unpack('c', $this->read());

        return mktime(0, 0, 0, $m[1], $d[1], $y);
    }

    /**
     * @param $d
     *
     * @return bool|int
     */
    public function write4ByteDate($d)
    {
        $t = getdate($d);

        return $this->writeUShort($t['year']) + $this->writeUChar($t['mon']) + $this->writeUChar($t['mday']);
    }
}
