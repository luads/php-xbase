<?php
// php test-memo.php ../tests/Resources/foxpro/1.dbf memo_field_name

use XBase\TableReader;

require_once '../vendor/autoload.php';

/**
 * CLI usage: php test-memo.php [path to a dbf file] [memo_field_name]
 **/

if (empty($argv[1])) {
    die('database file argument not defined?');
}
if (empty($argv[2])) {
    die('memo field name not defined?');
}
$filepath = realpath($argv[1]);
if (false === $filepath || !is_file($filepath)) {
    die('Bad path to file realpath '.$argv[1]);
}

$table = new TableReader($filepath, ['encoding' => 'cp1252']);
echo 'Record count: '.$table->getRecordCount() . PHP_EOL;

$columns = $table->getColumns();

//print_r($columns);exit;

$i = 0;
while ($record = $table->nextRecord()) {
    $memo = $record->{$argv[2]};
    echo $memo . PHP_EOL;
}
