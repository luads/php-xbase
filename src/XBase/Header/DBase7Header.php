<?php declare(strict_types=1);

namespace XBase\Header;

class DBase7Header extends AbstractHeader
{
    /** @var string|null */
    private $languageName;

    public function __construct(
        int $version,
        int $modifyDate,
        int $recordCount,
        int $headerLength,
        int $recordByteLength,
        bool $inTransaction,
        bool $encrypted,
        int $mdxFlag,
        int $languageCode,
        string $languageName
    ) {
        parent::__construct($version, $modifyDate, $recordCount, $headerLength, $recordByteLength, $inTransaction, $encrypted, $mdxFlag, $languageCode);
        $this->languageName = $languageName;
    }

    public function getLanguageName(): ?string
    {
        return $this->languageName;
    }

    public function setLanguageName(?string $languageName): void
    {
        $this->languageName = $languageName;
    }
}
