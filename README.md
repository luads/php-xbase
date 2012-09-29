PHP XBase
-----

A simple parser for *.dbf (dBase, foxpro). It's a port of PHPXbase class written by [Erwin Kooi](http://www.phpclasses.org/package/2673-PHP-Access-dbf-foxpro-files-without-PHP-ext-.html), updated to a 5.3 / PSR compliant code.

Sample code:

    <?php
    
    use XBase\Table;
    
    $table = new Table(dirname(__FILE__).'/pcores.dbf');
    
    while ($record = $table->nextRecord()) {
        echo $record->my_column;
    }