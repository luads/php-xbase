# CHANGELOG

## 2.0

### New features

- add TableCreator;

### Breaking changes

- rename Table to TableReader;
- rename WritableTable to TableEditor;
- removed all Record methods like get*() except `get` and getDateTimeObject.
- removed all Record methods like set*() except `set`.


## 1.3.5

### deprecations
- AbstractRecord::getColumns() use should Table::getColumn() instead.
- AbstractRecord::getColumn(name $name) use should Table::getColumn() instead.


## 1.3.2

- Table::__constructor accepts options array. Available options: `encoding`, `columns`.

```php
use XBase\Table;

// before 1.3.2
$table = new Table(
    __DIR__.'/Resources/foxpro/1.dbf', 
    ['column1', 'column2'], 
    'cp852'
);

// since 1.3.2
$table = new Table(
    __DIR__.'/Resources/foxpro/1.dbf', 
    [
        'columns' => ['column1', 'column2'], 
        'encoding' => 'cp852'
    ]
);
```

- WritableTable `editMode` options.
    - `clone` Default. Creates a clone of original file and applies all changes to it. To save changes you need to call `save` method. 
    - `realtime` Immediately apply changes for original table file. Changes cannot be undone.

```php
use XBase\WritableTable;

// clone edit mode
$tableWrite = new WritableTable(
    'file.dbf', 
    [
        'encoding' => 'cp866',
        'editMode' => WritableTable::EDIT_MODE_CLONE,
    ]
);
// do edits
$tableWrite
    ->save()
    ->close();

// realtime edit mode
$tableWrite = new WritableTable(
    'file.dbf', 
    [
        'encoding' => 'cp866',
        'editMode' => WritableTable::EDIT_MODE_REALTIME,
    ]
);
// do edits
$tableWrite->close();
```


## 1.3

- all setters return $this.
- getters for type `D` (Date) now returns date string in 'Ymd' format instead of timestamp.
- `VisualFoxproRecord::getDateTime` returns object of `\DateTimeInterface` instead of timestamp.

### deprecated

- setters like getType are deprecated. Use set('name', $value) method instead.
- getters like getType are deprecated. Use get('name') method instead.