<?php

namespace PhpExcel\Xlsx;

use InvalidArgumentException;
use PhpExcel\Abstract\ArchiveFile;
use PhpExcel\Abstract\CellUtils;
use PhpExcel\Abstract\XMLHolder;
use PhpExcel\CellResolver;
use PhpExcel\Processing\Convertion\FormulaParser;
use PhpExcel\Xlsx\DocProps\App;
use PhpExcel\Xlsx\DocProps\Core;
use PhpExcel\Xlsx\Rels\Rels;
use PhpExcel\Xlsx\Xl\SharedStrings;
use PhpExcel\Xlsx\Xl\Styles;
use PhpExcel\Xlsx\Xl\Theme\Theme;
use PhpExcel\Xlsx\Xl\Workbook;
use PhpExcel\Xlsx\Xl\Worksheets\Cell;
use PhpExcel\Xlsx\Xl\Worksheets\CellCollection;
use PhpExcel\Xlsx\Xl\Worksheets\Worksheet;
use RuntimeException;
use ZipArchive;

class SpreadSheet extends XMLHolder
{
    use CellUtils;

    /** @var array<string,class-string<ArchiveFile>> */
    const BASE_FILE_INDEX = [
        'xl/workbook.xml' => Workbook::class,
        'xl/styles.xml' => Styles::class,
        'xl/sharedStrings.xml' => SharedStrings::class,
        '_rels/.rels' => Rels::class,
        'docProps/core.xml' => Core::class,
        'docProps/app.xml' => App::class,
        '[Content_Types].xml' => ContentTypes::class,
    ];

    /** @var array<string,class-string<ArchiveFile>> */
    const DIRECTOR_MAPPING =[
        'xl/worksheets' => Worksheet::class,
        'xl/theme' => Theme::class
    ];

    /** @var array<string,ArchiveFile> */
    protected array $elements = [];

    /** @var array<string,Theme> */
    protected array $themes = [];

    /** @var array<string,Worksheet> */
    public array $worksheets = [];
    public ?Worksheet $activeWorksheet = null;

    protected ?Workbook $workbook;

    protected CellResolver $cellResolver;

    public function __construct()
    {
        $this->cellResolver = new CellResolver($this);
    }

    public static function fromFile(string $file): static
    {
        $instance = new static();
        $zip = new ZipArchive();
        if (!$zip->open($file))
            throw new RuntimeException("Could not open $file");

        $files = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);

            $files[$stat['name']] = [
                'name' => $stat['name'],
                'size' => $stat['size'],
                'compressed_size' => $stat['comp_size'],
                'is_directory' => str_ends_with($stat['name'], '/'),
            ];
        }

        $filenames = array_keys($files);

        foreach (self::DIRECTOR_MAPPING as $directory => $classmap) {
            collect($filenames)
                ->filter(fn($f) => str_starts_with($f, $directory))
                ->each(fn($f) =>
                    $instance->elements[$f] = $classmap::fromFile($f, $zip->getFromName($f))
                );
        }

        foreach (self::BASE_FILE_INDEX as $filename => $classmap) {
            if ($file = $files[$filename] ?? false) {
                $instance->elements[$filename] = $classmap::fromFile($filename, $zip->getFromName($filename));
            }
        }

        foreach ($files as $filename => $_) {
            if (!array_key_exists($filename, $instance->elements))
                $instance->elements[$filename] = ArchiveFile::fromFile($filename, $zip->getFromName($filename));
        }

        $instance->updateIndexes();
        $zip->close();
        return $instance;
    }

    public function writeFile(string $outputFile) {

        $zip = new ZipArchive;
        $zip->open($outputFile, ZipArchive::CREATE);

        foreach ($this->elements as $key => $archiveFile) {
            $zip->addFromString(
                $archiveFile->getArchiveRelativePath(),  
                $archiveFile->toString()
            );
        }

        $zip->close();
    }


    public function updateIndexes() {

        $this->themes = [];

        foreach ($this->elements as &$element) {
            if ($element instanceof Theme)
                $this->themes[] = $element;

            if ($element instanceof Workbook)
                $this->workbook = $element;
        }

        if ($this->workbook) {
            foreach ($this->workbook->sheetMap as $sheetName => $sheetFile) {
                if (!array_key_exists($sheetFile, $this->elements))
                    throw new RuntimeException("Could not resolve sheet $sheetName");

                $this->worksheets[$sheetName] = &$this->elements[$sheetFile];
            }
        }

        $this->selectDefaultSheet();
    }

    public function getSheet(string $sheetName): ?Worksheet {
        $this->selectDefaultSheet();
        return $this->worksheets[$sheetName] ?? null;
    }

    protected function selectDefaultSheet() {
        $this->activeWorksheet ??= array_values($this->worksheets)[0] ?? null;
    }

    public function getActiveSheet(): Worksheet {
        $this->createDefaultSheetOnEmpty();

        return $this->activeWorksheet;
    }

    public function selectSheet(string $sheetName): self {
        if (!array_key_exists($sheetName, $this->worksheets))
            throw new InvalidArgumentException("Cannot use sheet $sheetName, inexistent worksheet");

        $this->activeWorksheet = &$this->worksheets[$sheetName];
        return $this;
    }

    public function createSheet(string $sheetName): Worksheet {
        $this->worksheets[$sheetName] ??= new Worksheet();
        return $this->getSheet($sheetName);
    }

    protected function createDefaultSheetOnEmpty()
    {
        if (!count($this->worksheets))
            $this->createSheet('Sheet1');

        $this->selectDefaultSheet();
    }

    public function write(string $address, string $valueOrFormula): Cell {
        $this->createDefaultSheetOnEmpty();

        $sheetName = $this->parseAddress($address)->sheet;

        $sheet = $sheetName
            ? $this->getSheet($sheetName)
            : $this->getActiveSheet();

        return $sheet->write($address, $valueOrFormula);
    }

    /**
     * @param array $assocArray as $address => $valueOrFormula
     */
    public function writeAssoc(array $assocArray): void {
        if (array_is_list($assocArray))
            throw new InvalidArgumentException('Given array must be an associative array');

        foreach ($assocArray as $address => $valueOrFormula)
            $this->write($address, $valueOrFormula);
    }

    public function resolve(string $expression): Cell|CellCollection {
        return $this->cellResolver->resolve($expression);
    }

    public function resolveValue(string $expression, ?Cell $preprocessedCell = null): mixed {
        $cell = $preprocessedCell ?? $this->resolve($expression);
        if (!$cell->formula) {
            return $this->autoTypeValue($cell->value);
        }

        if ($cached = $cell->cachedExpression)
            return $cached->getValue($this);

        $parser = new FormulaParser($this);
        $newExpression = $parser($cell);
        $cell->cachedExpression = $newExpression;

        return $newExpression->getValue($this);
    }

    /**
     * @alias Alias of resolveValue()
     */
    public function query(string $expression, ?Cell $preprocessedCell = null): mixed {
        return $this->resolveValue($expression, $preprocessedCell);
    }
}