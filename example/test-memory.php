<?php
// php test-memory.php ../tests/Resources/foxpro/1.dbf

use XBase\TableReader;

require_once '../vendor/autoload.php';

/**
 * CLI usage: php test-memory.php db=[path to a dbf file]
 **/

if (empty($argv[1])) {
    die('database file argument not defined?');
}
$filepath = realpath($argv[1]);
if (false === $filepath || !is_file($filepath)) {
    die('Bad path to file realpath '.$argv[1]);
}

$table = new TableReader($filepath);
echo 'Record count: '.$table->getRecordCount();

$columns = $table->getColumns();

$i = 0;
while ($record = $table->nextRecord()) {
    $s = [];
    foreach ($columns as $column) {
        $s[] = $record->get($column->getName());
    }
    $str = implode(',', $s);
    if (++$i % 1000 == 0) {
        echo "{$i} >> ".round(memory_get_usage() / (1024 * 1024))." MB\n";
    }
}
