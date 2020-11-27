PHP XBase
-----

[![Build Status](https://travis-ci.org/luads/php-xbase.svg?branch=master&t=202003171723)](https://travis-ci.org/luads/php-xbase)
[![Test Coverage](https://api.codeclimate.com/v1/badges/a3df6ca519b4cee98c6d/test_coverage)](https://codeclimate.com/github/hisamu/php-xbase/test_coverage)
[![Latest Stable Version](https://poser.pugx.org/hisamu/php-xbase/v/stable)](https://packagist.org/packages/hisamu/php-xbase)
[![Total Downloads](https://poser.pugx.org/hisamu/php-xbase/downloads)](https://packagist.org/packages/hisamu/php-xbase)
[![License](https://poser.pugx.org/hisamu/php-xbase/license)](https://packagist.org/packages/hisamu/php-xbase)

A simple library for dealing with **dbf** databases like dBase and FoxPro. It's a port of PHPXbase class written by [Erwin Kooi](http://www.phpclasses.org/package/2673-PHP-Access-dbf-foxpro-files-without-PHP-ext-.html), updated to a PSR-2 compliant code and tweaked for performance and to solve some issues the original code had.

Installation
-----
You can install it through [Composer](https://getcomposer.org):
```
$ composer require hisamu/php-xbase
```

Sample usage
-----
More samples in `tests` folder.

``` php
use XBase\Table;

$table = new Table('test.dbf');

while ($record = $table->nextRecord()) {
    echo $record->get('my_column');
    //or
    echo $record->my_column;
}
```

If the data in DB is not in UTF-8 you can specify a charset to convert the data from:

``` php
$table = new Table('test.dbf', null, 'CP1251');
```

It is also possible to read Memos from dedicated files. Just make sure that *.fpt* file with the same name as main database exists.

Performance
-----

You can pass an array of the columns that you need to the constructor, then if your table has columns that you don't use they will not be loaded. This way the parser can run a lot faster.

``` php
use XBase\Table;

$table = new Table('test.dbf', ['my_column', 'another_column']);

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
use XBase\WritableTable;

$table = new WritableTable('test.dbf');

for ($i = 0; $i < 10; $i++) {
    $record = $table->nextRecord();
    
    $record->set('field', 'string');
    //or
    $record->field = 'string';

    $table->writeRecord();
}

$table
    ->save()
    ->close();
```

Add new record
-----

``` php
use XBase\WritableTable;

$table = new WritableTable('file.dbf');
$record = $table->appendRecord();
$record->set('name', 'test name');
$record->set('age', 20);

$table
    ->writeRecord()
    ->save()
    ->close();
```

Delete record
-----

``` php
use XBase\WritableTable;

$table = new WritableTable('file.dbf');

while ($record = $table->nextRecord()) {
    if ($record->get('delete_this_row')) {
        $table->deleteRecord(); //mark record deleted
    }    
}

$table
    ->pack() //remove deleted rows
    ->save() // save changes
    ->close();
```

Troubleshooting
-----

I'm not an expert on dBase and I don't know all the specifics of the field types and versions, so the lib may not be able to handle some situations. If you find an error, please open an issue and send me a sample table that I can reproduce your problem and I'll try to help.

Useful links
-----

[Xbase File Format Description](http://www.manmrk.net/tutorials/database/xbase/)

[File Structure for dBASE 7](http://www.dbase.com/KnowledgeBase/int/db7_file_fmt.htm)

[DBF AND DBT/FPT FILE STRUCTURE](http://www.independent-software.com/dbase-dbf-dbt-file-format.html)
