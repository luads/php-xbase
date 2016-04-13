PHP XBase
-----

A simple library for dealing with **dbf** databases like dBase and FoxPro. It's a port of PHPXbase class written by [Erwin Kooi](http://www.phpclasses.org/package/2673-PHP-Access-dbf-foxpro-files-without-PHP-ext-.html), updated to a PSR-2 compliant code and tweaked for performance and to solve some issues the original code had.

Installation
-----
You can install it through [Composer](https://getcomposer.org):
```
$ composer require hisamu/php-xbase
```

Sample usage
-----
``` php
<?php

use XBase\Table;

$table = new Table(dirname(__FILE__).'/test.dbf');

while ($record = $table->nextRecord()) {
    echo $record->my_column;
}
```

If the data in DB is not in UTF-8 you can specify a charset to convert the data from:

``` php
$table = new Table(dirname(__FILE__).'/test.dbf', null, 'CP1251');
```

Performance
-----

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

Writing Data
-----

To open a table for writing, you have to use a `WritableTable` object, as on this example:

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

Troubleshooting
-----

I'm not an expert on dBase and I don't know all the specifics of the field types and versions, so the lib may not be able to handle some situations. If you find an error, please open an issue and send me a sample table that I can reproduce your problem and I'll try to help.
