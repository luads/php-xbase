<?php declare(strict_types=1);

namespace XBase\Memo;

use XBase\DataConverter\Encoder\EncoderInterface;
use XBase\Stream\Stream;
use XBase\Table\Table;

abstract class AbstractMemo implements MemoInterface
{
    /** @var Table */
    protected $table;

    /** @var Stream */
    protected $fp;

    /** @var string */
    protected $filepath;

    /** @var EncoderInterface */
    protected $encoder;

    /**
     * @param string $filepath Path to memo file
     */
    public function __construct(Table $table, string $filepath, EncoderInterface $encoder)
    {
        $this->table = $table;
        $this->encoder = $encoder;

        $this->filepath = $filepath;
        $this->open();
        $this->readHeader();
    }

    public function getFilepath(): string
    {
        return $this->filepath;
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

    protected function guessDataType(string $string): array
    {
        if (strlen($string) > 4) {
            $bytes = unpack('n*', substr($string, 0, 4));
            switch ($bytes[1]) {
                case 0x4D42: //BMP
                    return [
                        'type' => MemoObject::TYPE_IMAGE,
                        'ext'  => 'bmp',
                    ];
                case 0xFFD8: //JPEG
                    return [
                        'type' => MemoObject::TYPE_IMAGE,
                        'ext'  => 'jpeg',
                    ];
                case 0x8950: //PNG
                    if (0x4E47 === $bytes[2]) {
                        return [
                            'type' => MemoObject::TYPE_IMAGE,
                            'ext'  => 'png',
                        ];
                    }
                    break;
            }
        }

        return [
            'type' => MemoObject::TYPE_TEXT,
        ];
    }
}
