<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Exception\TableException;
use XBase\Header\Header;
use XBase\Header\Reader\Column\ColumnReaderFactory;
use XBase\Header\Specification\HeaderSpecificationFactory;
use XBase\Stream\Stream;

abstract class AbstractHeaderReader implements HeaderReaderInterface
{
    /** @var static */
    protected $filepath;

    /** @var Stream */
    protected $fp;

    /** @var Header|null */
    protected $header;

    /** @var array */
    protected $options;

    /**
     * @param array $options Array of options:<br>
     *                       columns - available columns<br>
     */
    public function __construct(string $filepath, array $options = [])
    {
        $this->filepath = $filepath;
        $this->fp = Stream::createFromFile($filepath);
        $this->options = $options;
    }

    public function read(): Header
    {
        $this->fp->seek(0);

        $this->readFirstBlock();
        $this->readColumns();
        $this->readRest();

        $this->fp->close();

        return $this->header;
    }

    protected function readFirstBlock(): void
    {
        $this->header = new Header($this->extractArgs());
    }

    protected function readColumns(): void
    {
        [$columnsCount, $terminatorLength] = $this->pickColumnsCount();

        /* some checking */
        clearstatcache();
        if ($this->header->length > filesize($this->filepath)) {
            throw new TableException(sprintf('File %s is not DBF', $this->filepath));
        }

        if ($this->header->length + ($this->header->recordCount * $this->header->recordByteLength) - 500 > filesize($this->filepath)) {
            throw new TableException(sprintf('File %s is not DBF', $this->filepath));
        }

        $targetColumns = $this->options['columns'] ?? [];

        $bytePos = 1;
        $columnReader = ColumnReaderFactory::create($this->header->version);
        for ($i = 0; $i < $columnsCount; $i++) {
            $column = $columnReader->read($this->fp);
            if (empty($targetColumns) || in_array($column->name, $targetColumns)) {
                $column->columnIndex = $i;
                $column->bytePosition = $bytePos;

                $this->header->columns[] = $column;
            }

            $bytePos += $column->length;
        }

        $this->checkHeaderTerminator($terminatorLength);
    }

    protected function readRest(): void
    {
    }

    /**
     * @return array named argument for certain implementation of Header
     */
    protected function extractArgs(): array
    {
        $args = [
            'version'          => $this->fp->readUChar(),
            'modifyDate'       => $this->fp->read3ByteDate(),
            'recordCount'      => $this->fp->readUInt(),
            'length'           => $this->fp->readUShort(),
            'recordByteLength' => $this->fp->readUShort(),
        ];
        $this->fp->read(2); //reserved
        $args['inTransaction'] = 0 !== $this->fp->readUChar();
        $args['encrypted'] = 0 !== $this->fp->readUChar();
        $this->fp->read(4); //Free record thread
        $this->fp->read(8); //Reserved for multi-user dBASE
        $args['mdxFlag'] = $this->fp->readUChar();
        $args['languageCode'] = $this->fp->readUChar();
        $this->fp->read(2); //reserved

        return $args;
    }

    /**
     * @return array [$fieldCount, $terminatorLength]
     */
    protected function pickColumnsCount(): array
    {
        // some files has headers with 2byte-terminator 0xOD00
        foreach ([1, 2] as $terminatorLength) {
            $fieldCount = $this->getLogicalFieldCount($terminatorLength);
            if (is_int($fieldCount)) {
                return [$fieldCount, $terminatorLength];
            }
        }

        throw new \LogicException('Wrong fieldCount calculation');
    }

    /**
     * @return float|int
     */
    protected function getLogicalFieldCount(int $terminatorLength = 1)
    {
        $spec = HeaderSpecificationFactory::create($this->header->version);

        $headerLength = $spec->headerTopLength + $terminatorLength; // [Terminator](1)
        $extraSize = $this->header->length - $headerLength;

        return $extraSize / $spec->fieldLength;
    }

    /**
     * @throws TableException
     */
    private function checkHeaderTerminator(int $terminatorLength): void
    {
        $terminator = $this->fp->read($terminatorLength);
        switch ($terminatorLength) {
            case 1:
                if (chr(0x0D) !== $terminator) {
                    throw new TableException('Expected header terminator not present at position '.$this->fp->tell());
                }
                break;

            case 2:
                $unpack = unpack('n', $terminator);
                if (0x0D00 !== $unpack[1]) {
                    throw new TableException('Expected header terminator not present at position '.$this->fp->tell());
                }
                break;
        }
    }
}
