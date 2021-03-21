<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator;

use XBase\Exception\ColumnException;
use XBase\Header\Column;

class ChainValidator
{
    /**
     * @var ColumnValidatorInterface[]
     */
    private $validators;

    /**
     * @param ColumnValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    public function validate(Column $column): void
    {
        $validators = $this->findValidators($column->type);
        if (!$validators) {
            throw new ColumnException("Table not supports `{$column->type}` column type");
        }

        foreach ($validators as $validator) {
            $validator->validate($column);
        }
    }

    private function findValidators(string $type): array
    {
        return array_filter(
            $this->validators,
            static function (ColumnValidatorInterface $validator) use ($type): bool {
                $supportedTypes = $validator->getType();
                if (!is_array($supportedTypes)) {
                    $supportedTypes = [$supportedTypes];
                }

                return in_array($type, $supportedTypes);
            }
        );
    }
}
