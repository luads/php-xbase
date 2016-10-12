<?php

/**
* CLI usage: php test-memory.php db=[path to a dbf file]
**/

parse_str(implode('&', array_slice($argv, 1)), $_GET);
if (empty($_GET['db'])) die('db?');

chdir(dirname(__FILE__).'/../src');
require 'XBase/Table.php';
require 'XBase/Column.php';
require 'XBase/Record.php';

$table = new \Xbase\Table($_GET['db']);
$columns = $table->getColumns();

$i=0;
while ($record = $table->nextRecord()) {
    $s = [];
    foreach ($columns as $column) {
        $s[] = $record->forceGetString($column->name);
    }
    $str = implode(',',$s);
    if (++$i % 1000 == 0) {
        echo "{$i} >> ".round(memory_get_usage()/(1024*1024))." MB\n";
    }
}
