<?php

namespace XBase\Memo;

abstract class AbstractMemo implements MemoInterface
{
    /** @var resource */
    protected $fp;
    /** @var string */
    protected $filepath;
    /** @var string */
    protected $convertFrom;

    /**
     * Memo constructor.
     *
     * @param string $filepath
     * @param string $convertFrom
     */
    public function __construct($filepath, $convertFrom = null)
    {
        $this->filepath = $filepath;
        $this->convertFrom = $convertFrom; //todo autodetect from languageCode
        $this->open();
        $this->readHeader();
    }

    public function __destruct()
    {
        $this->close();
    }

    protected function readHeader()
    {

    }

    public static function getExtension()
    {
        return 'dbt';
    }

    /**
     * @inheritDoc
     */
    public function isOpen()
    {
        return null !== $this->fp;
    }

    public function open()
    {
        $this->close();
        $this->fp = fopen($this->filepath, 'rb');
    }

    public function close()
    {
        if (null !== $this->fp) {
            fclose($this->fp);
        }
        $this->fp = null;
    }

    protected function guessDataType(string $result)
    {
        if (strlen($result) > 4) {
            $bytes = unpack('n*', substr($result, 0, 4));
            switch ($bytes[1]) {
                case 0x4D42: //BMP
                case 0xFFD8: //JPEG
                    return MemoObject::TYPE_IMAGE;
                case 0x8950: //PNG
                    if (0x4E47 === $bytes[2]) {
                        return MemoObject::TYPE_IMAGE;
                    }
                    break;
            }
        }

        return MemoObject::TYPE_TEXT;
    }
}
