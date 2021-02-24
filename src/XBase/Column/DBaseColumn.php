<?php declare(strict_types=1);

namespace XBase\Column;

use XBase\Enum\FieldType;

class DBaseColumn extends AbstractColumn
{
    protected $reserved1;

    protected $reserved2;

    protected $reserved3;

    public function __construct(
        string $rawName,
        string $type,
        int $memAddress,
        int $length,
        int $decimalCount = 0,
        $reserved1 = '',
        int $workAreaID = 0,
        $reserved2 = '',
        bool $setFields = false,
        $reserved3 = '',
        bool $indexed = false,
        ?int $colIndex = null,
        ?int $bytePos = null
    ) {
        $this->rawName = $rawName;
        $name = (false !== strpos($rawName, chr(0x00))) ? substr($rawName, 0, strpos($rawName, chr(0x00))) : trim($rawName);

        // chop all garbage from 0x00
        $this->name = strtolower($name);
        $this->type = $type;
        $this->memAddress = $memAddress;

        if (in_array($this->type, [FieldType::CHAR, FieldType::MEMO])) {
            $this->length = $length + 256 * $decimalCount;
        } else {
            $this->length = $length;
            $this->decimalCount = $decimalCount;
        }

        $this->reserved1 = $reserved1;
        $this->workAreaID = $workAreaID;
        $this->reserved2 = $reserved2;
        $this->setFields = $setFields;
        $this->reserved3 = $reserved3;
        $this->indexed = $indexed;
        $this->colIndex = $colIndex;
        $this->bytePos = $bytePos;
    }

    public function getReserved1(): string
    {
        return $this->reserved1;
    }

    public function getReserved2(): string
    {
        return $this->reserved2;
    }

    public function getReserved3(): string
    {
        return $this->reserved3;
    }

    /**
     * @return bool|string
     */
    public function toString()
    {
        return $this->name;
    }
}
