<?php

namespace XBase\Stream;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @method int readUChar()
 * @method int readUShort()
 * @method int readUInt()
 * @method int readChar()
 * @method int readShort()
 * @method int readInt()
 *
 * @method int writeUChar()
 * @method int writeUShort()
 * @method int writeUInt()
 * @method int writeChar()
 * @method int writeShort()
 * @method int writeInt()
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

    public function seek(int $offset)
    {
        return fseek($this->fp, $offset);
    }

    public function tell()
    {
        return ftell($this->fp);
    }

    public function flush()
    {
        return fflush($this->fp);
    }

    public function isOpen()
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

    public function __call($method, $args)
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
