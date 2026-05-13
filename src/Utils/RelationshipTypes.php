<?php

namespace PhpExcel\Utils;

enum RelationshipTypes: string 
{
    case WORKBOOK       = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';
    case CORE_PROPS     = 'http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties';
    case EXTENDED_PROPS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties';
    case THEME          = "http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme";
    case STYLES         = "http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles";
    case WORKSHEET      = "http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet";
    case SHARED_STRING  = "http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings";
}