<?php declare(strict_types=1);

namespace XBase\DataConverter\Encoder;

class MbConvertEncodingEncoder implements EncoderInterface
{
    public function encode(string $string, string $fromEncoding, string $toEncoding): string
    {
        return mb_convert_encoding($string, $toEncoding, $fromEncoding);
    }
}
