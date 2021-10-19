<?php declare(strict_types=1);

namespace XBase\DataConverter\Encoder;

interface EncoderInterface
{
    public function encode(string $string, string $fromEncoding, string $toEncoding): string;
}
