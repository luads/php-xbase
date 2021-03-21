<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Enum\TableType;
use XBase\Stream\Stream;

/**
 * Creates instance of HeaderBuilderInterface with which the Header object will be created.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class HeaderReaderFactory
{
    public static function create(string $filepath, array $options = []): HeaderReaderInterface
    {
        $stream = Stream::createFromFile($filepath);
        $version = $stream->readUChar();
        $stream->close();

        if (TableType::isVisualFoxpro($version)) {
            return new VisualFoxproHeaderReader($filepath, $options);
        }

        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return new DBase7HeaderReader($filepath, $options);
            default:
                return new DBaseHeaderReader($filepath, $options);
        }
    }
}
