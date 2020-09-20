<?php declare(strict_types=1);

namespace XBase\Record\DataConverter;

use XBase\Record\RecordInterface;

interface DataConverterInterface
{
    public function fromBinaryString(string $rawData): array;

    public function toBinaryString(RecordInterface $record): string;
}
