# php-excel (WIP)

Excel Reading/Writing and Formula Engine for PHP !

Features :
 - Reading / Writing Excel files
 - Making additions / modifications to existing files and formulas
 - Supporting formulas and cell logic

## Usage

```php
// From an existing file
$s = Spreadsheet::fromFile('your-excel-file.xlsx');
$s->query('C14');

// New Spreadsheet
$s = new Spreadsheet();
$s->write('A1', '8');
$s->write('B1', '5');
$s->write('C1', '=IF(A1>B1; A1+B1; 0)');
$s->query('C1') // Returns 13

$s->save('output-file.xlsx');

```

## Roadmap

| Status | Task |
|--------|------|
| ✅ | Opening/parsing an existing file |
| ✅ | Formula tokenization/parsing system |
| ✅ | Basic Operator supports (+,-,*,/,>,<,<=,<=,=) |
| ✅ | Sheet name support for cell name |
| ✅ | Implements Cell Range system |
| 🌀 | Possibility to save a new or edited spreadsheet (Developped, need tests) |
| ⏳ | Implements basics Excel functions such as SUM, POW, SQRT... |
| ⏳ | Recursion detection system |
| ⏳ | Write more tests for existing already features |

### TODOS

- Support &lt;dimension&gt; in Worksheet XML data

## Tests

```sh
composer install
make test
```
