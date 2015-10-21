PHP XBase
-----

A simple parser for *.dbf (dBase, foxpro). It's a port of PHPXbase class written by [Erwin Kooi](http://www.phpclasses.org/package/2673-PHP-Access-dbf-foxpro-files-without-PHP-ext-.html), updated to a 5.3 / PSR compliant code.

Sample code:
``` php
<?php

use XBase\Table;

$table = new Table(dirname(__FILE__).'/test.dbf');

while ($record = $table->nextRecord()) {
    echo $record->my_column;
}
```

Performance
=====

You can pass an array of the columns that you need to the constructor, then if your table has columns that you don't use they will not be loaded. This way the parser can run a lot faster.

``` php
<?php

use XBase\Table;

$table = new Table(dirname(__FILE__).'/test.dbf', array('my_column', 'another_column'));

while ($record = $table->nextRecord()) {
    echo $record->my_column;
    echo $record->another_column;
}
```

If you know the column type already, you can also call the type-specific function for that field, which increases the speed too.

``` php
while ($record = $table->nextRecord()) {
    echo $record->getChar('my_column');
    echo $record->getDate('another_column');
}
```

Write Data
=====
``` php
<?php

use XBase\WritableTable;

$table = new WritableTable(dirname(__FILE__).'/test.dbf'));
$table->openWrite();

for ($i = 0; $i < 10; $i++) {
    $record = $table->nextRecord();
    $record->field = 'string';
    $table->writeRecord();
}

# optional
$table->close();
```
