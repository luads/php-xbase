# CHANGELOG

## 1.3

- all setters return $this.
- getters for type `D` (Date) now returns date string in 'Ymd' format instead of timestamp.
- `VisualFoxproRecord::getDateTime` returns object of `\DateTimeInterface` instead of timestamp.

### deprecated

- setters like getType are deprecated. Use set('name', $value) method instead.
- getters like getType are deprecated. Use get('name') method instead.