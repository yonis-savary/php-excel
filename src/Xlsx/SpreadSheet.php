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
    public array $elements = [];

    /** @var array<string,Theme> */
    protected array $themes = [];

    /** @var array<string,Worksheet> */
    public array $worksheets = [];
    public ?Worksheet $activeWorksheet = null;

    protected ?Workbook $workbook;
    protected Rels $globalRels;

    protected CellResolver $cellResolver;

    public function __construct()
    {
        $this->cellResolver = new CellResolver($this);
        $this->createDefaultWorkbook();
        $this->createDefaultGlobalRels();
    }

    public function addElement(ArchiveFile $element, ?string $filename = null) {
        $element->setSpreadsheet($this);
        $filename ??= $element->getArchiveRelativePath();
        $this->elements[$filename] = $element;
    }

    public function openZip(string $file) {

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
                ->each(fn($f) => $this->addElement($classmap::fromFile($f, $zip->getFromName($f)), $f));
        }

        foreach (self::BASE_FILE_INDEX as $filename => $classmap) {
            if ($file = $files[$filename] ?? false) {
                $this->addElement($classmap::fromFile($filename, $zip->getFromName($filename)), $filename);
            }
        }

        foreach ($files as $filename => $_) {
            if (!array_key_exists($filename, $this->elements))
                $this->addElement(ArchiveFile::fromFile($filename, $zip->getFromName($filename)), $filename);
        }

        $this->updateIndexes();
        $zip->close();
    }

    public static function fromFile(string $file): static
    {
        $instance = new static();
        $instance->openZip($file);
        return $instance;
    }

    public function createDefaultWorkbook() {
        $workbook = new Workbook;
        $workbookPath = $workbook->getArchiveRelativePath();
        $this->workbook ??= $workbook;
        $this->elements[$workbookPath] ??= $workbook;
    }

    public function createDefaultGlobalRels() {
        $rels = new Rels;
        $relsPath = '_rels/.rels';
        if (!array_key_exists($relsPath, $this->elements)) {
            $rels->setFilename($relsPath);
            $this->elements[$relsPath] ??= $rels;
            $this->globalRels = $rels;
            $this->globalRels->addRef($this->workbook);
        }
    }

    public function writeFile(string $outputFile) {

        $elements = &$this->elements;

        if (!array_key_exists('[Content_Types].xml', $elements))
            $this->addElement(new ContentTypes());

        $keys = array_keys($elements);
        foreach ($keys as $key)
            $elements[$key]->addChildToSpreadsheet($this);

        $zip = new ZipArchive;
        $zip->open($outputFile, ZipArchive::CREATE);

        foreach ($elements as $key => $archiveFile) {
            $archiveFile->setSpreadsheet($this);
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

                /** @var Worksheet $worksheet */
                $worksheet = &$this->elements[$sheetFile];
                $worksheet->name = $sheetName;
                $this->worksheets[$sheetName] = $worksheet;
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
        if (!array_key_exists($sheetName, $this->worksheets)) {
            $newSheet = new Worksheet();
            $newSheet->name = $sheetName;
            $safeNewSheetName = preg_replace('~[^A-Z\-_0-9\.]~i', '_', $sheetName);
            $newSheet->setFilename("xl/worksheets/$safeNewSheetName.xml");
            $this->addElement($newSheet);
            $this->workbook->addSheet($newSheet);
            $this->worksheets[$sheetName] = $newSheet;
        }

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