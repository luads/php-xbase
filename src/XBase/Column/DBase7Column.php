<?php declare(strict_types=1);

namespace XBase\Column;

class DBase7Column extends AbstractColumn
{
    /** @var int */
    protected $mdxFlag;

    /** @var int */
    protected $nextAI;

    /**
     * @var string field name in ASCII (zero-filled)
     * @var string field type in ASCII (B, C, D, N, L, M, @, I, +, F, 0 or G)
     * @var int    field length in binary
     * @var int    field decimal count in binary
     * @var mixed  reserved
     * @var int    Production .MDX field flag; 0x01 if field has an index tag in the production .MDX file; 0x00 if the field is not indexed.
     * @var mixed  reserved
     * @var int    next Autoincrement value, if the Field type is Autoincrement, 0x00 otherwise
     * @var mixed  reserved
     */
    public function __construct(string $name, string $type, int $length, int $decimalCount, $reserved1, int $mdxFlag, $reserved2, int $nextAI, $reserved3, int $colIndex, ?int $bytePos = null)
    {
        $this->rawName = $name;
        $this->name = strtolower(rtrim($name, chr(0x00)));
        $this->type = $type;
        $this->length = $length;
        $this->decimalCount = $decimalCount;
        $this->mdxFlag = $mdxFlag;
        $this->nextAI = $nextAI;
        // not protocol
        $this->colIndex = $colIndex;
        $this->bytePos = $bytePos;
    }

    public function getMdxFlag(): int
    {
        return $this->mdxFlag;
    }

    public function getNextAI(): int
    {
        return $this->nextAI;
    }
}
