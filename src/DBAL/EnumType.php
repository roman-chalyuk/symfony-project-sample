<?php

declare(strict_types=1);

namespace App\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class EnumType extends Type
{
    protected $name;
    protected $values = [];

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        $values = array_map(fn($val) => "'" . $val . "'", $this->values);

        return "ENUM(" . implode(", ", $values) . ")";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_null($value) && !in_array($value, $this->values, true)) {
            throw new \InvalidArgumentException("Invalid '" . $this->name . "' value.");
        }
        return $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
