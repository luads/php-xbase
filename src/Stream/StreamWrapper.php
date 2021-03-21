<?php declare(strict_types=1);

namespace XBase\Stream;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @method int readUChar(): int
 * @method int readUShort(): int
 * @method int readUInt(): int
 * @method int readChar(): int
 * @method int readShort(): int
 * @method int readInt(): int
 * @method int writeUChar(int $value)
 * @method int writeUShort(int $value)
 * @method int writeUInt(int $value)
 * @method int writeChar(int $value)
 * @method int writeShort(int $value)
 * @method int writeInt(int $value)
 */
class StreamWrapper
{
    /** @var resource Stream */
    protected $fp;

    /**
     * Stream constructor.
     *
     * @param resource $fp
     */
    public function __construct($fp)
    {
        if (!is_resource($fp)) {
            throw new \LogicException('Argument is not resource');
        }

        $this->fp = $fp;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function truncate(int $size)
    {
        return ftruncate($this->fp, $size);
    }

    public function rewind()
    {
        return rewind($this->fp);
    }

    public function seek(int $offset, int $whence = SEEK_SET)
    {
        return fseek($this->fp, $offset, $whence);
    }

    public function tell()
    {
        return ftell($this->fp);
    }

    public function flush()
    {
        return fflush($this->fp);
    }

    public function isOpen(): bool
    {
        return null !== $this->fp;
    }

    public function close()
    {
        $result = true;
        if ($this->fp) {
            $result = fclose($this->fp);
            $this->fp = null;
        }

        return $result;
    }

    /**
     * @return bool|string
     */
    public function read(int $length = 1)
    {
        return fread($this->fp, $length);
    }

    public function write(string $string)
    {
        return fwrite($this->fp, $string); //todo length arg
    }

    public function eof(): bool
    {
        return feof($this->fp);
    }

    public function stat(): array
    {
        return fstat($this->fp);
    }

    public function __call(string $method, $args)
    {
        $mapping = [
            'char'  => [1, 'c'],
            'short' => [2, 's'],
            'int'   => [4, 'i'],
        ];

        $unsigned = false;
        $arr = explode('_', strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $method)));
        if (3 === count($arr) && 'u' === $arr[1]) {
            $unsigned = true;
            array_splice($arr, 1, 1);
        }

        [$length, $format] = $mapping[$arr[1]];
        if ($unsigned) {
            $format = strtoupper($format);
        }
        switch ($arr[0]) {
            case 'read':
                $str = $this->read($length);
                $buf = unpack($format, $str);

                return $buf[1];

            case 'write':
                return $this->write(pack($format, $args[0]));
        }

        throw new \LogicException('Unsupported function '.$method);
    }
}
