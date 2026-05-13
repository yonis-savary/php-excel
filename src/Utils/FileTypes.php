<?php

namespace PhpExcel\Utils;

enum FileTypes: string
{
    case RELS           = "application/vnd.openxmlformats-package.relationships+xml";
    case WORKBOOK       = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml";
    case THEME          = "application/vnd.openxmlformats-officedocument.theme+xml";
    case STYLES         = "application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml";
    case WORKSHEET      = "application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml";
    case SHARED_STRINGS = "application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml";
    case CORE_PROPS     = "application/vnd.openxmlformats-package.core-properties+xml";
    case EXTENDED_PROPS = "application/vnd.openxmlformats-officedocument.extended-properties+xml";
}
