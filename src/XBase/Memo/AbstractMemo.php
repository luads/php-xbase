<?php declare(strict_types=1);

namespace XBase\Memo;

use XBase\Stream\Stream;
use XBase\Table;

abstract class AbstractMemo implements MemoInterface
{
    /** @var Table */
    protected $table;

    /** @var Stream */
    protected $fp;

    /** @var string */
    protected $filepath;

    /** @var string */
    protected $convertFrom;

    /**
     * Memo constructor.
     *
     * @param string $convertFrom
     */
    public function __construct(Table $table, string $filepath, ?string $convertFrom = null)
    {
        $this->table = $table;
        $this->filepath = $filepath;
        $this->convertFrom = $convertFrom; //todo autodetect from languageCode
        $this->open();
        $this->readHeader();
    }

    public function __destruct()
    {
        $this->close();
    }

    protected function readHeader(): void
    {
    }

    public static function getExtension(): string
    {
        return 'dbt';
    }

    /**
     * {@inheritdoc}
     */
    public function isOpen(): bool
    {
        return null !== $this->fp;
    }

    public function open(): void
    {
        $this->fp = Stream::createFromFile($this->filepath); //todo configure write mode
    }

    public function close(): void
    {
        if (null !== $this->fp) {
            $this->fp->close();
        }
        $this->fp = null;
    }

    protected function guessDataType(string $result): int
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
