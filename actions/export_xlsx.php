<?php
// Export filtered fines to XLSX (OpenXML, no external library)
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$from = getq('from', date('Y-m-01'));
$to   = getq('to',   date('Y-m-d'));

$rows = db_all("
  SELECT f.id, s.student_no, s.full_name, COALESCE(c.name,'Custom') AS category,
         f.reason, f.amount, f.status, f.issued_at, f.paid_at
  FROM fines f
  JOIN students s ON s.id = f.student_id
  LEFT JOIN fine_categories c ON c.id = f.category_id
  WHERE DATE(f.issued_at) BETWEEN ? AND ?
  ORDER BY f.issued_at DESC", [$from, $to]);

// ── helpers ──────────────────────────────────────────────────────────────────

function xlsx_esc(string $v): string {
    return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function col_letter(int $n): string {   // 1-based → A, B, …, Z, AA, …
    $s = '';
    while ($n > 0) {
        $n--;
        $s = chr(65 + ($n % 26)) . $s;
        $n = intdiv($n, 26);
    }
    return $s;
}

function build_row(int $rowNum, array $cells, bool $bold = false): string {
    $xml = "<row r=\"{$rowNum}\">";
    $col = 1;
    foreach ($cells as $val) {
        $ref = col_letter($col) . $rowNum;
        $col++;
        if ($val === null || $val === '') {
            $xml .= "<c r=\"{$ref}\"/>";
            continue;
        }
        if (is_numeric($val)) {
            $s = $bold ? ' s="1"' : '';
            $xml .= "<c r=\"{$ref}\"{$s}><v>" . xlsx_esc((string)$val) . "</v></c>";
        } else {
            $s = $bold ? ' s="1"' : '';
            $xml .= "<c r=\"{$ref}\" t=\"inlineStr\"{$s}><is><t>" . xlsx_esc((string)$val) . "</t></is></c>";
        }
    }
    $xml .= '</row>';
    return $xml;
}

// ── build sheet XML ───────────────────────────────────────────────────────────

$headers = ['Fine ID','Student No','Student Name','Category','Reason','Amount','Status','Issued','Paid'];

$sheetRows = '';
$sheetRows .= build_row(1, $headers, true);
$r = 2;
foreach ($rows as $row) {
    $sheetRows .= build_row($r++, array_values($row));
}

$colCount = count($headers);
$lastCol  = col_letter($colCount);
$lastRow  = $r - 1;

$sheetXml = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
{$sheetRows}
  </sheetData>
</worksheet>
XML;

// ── static XLSX parts ─────────────────────────────────────────────────────────

$contentTypes = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;

$relsRoot = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
    Target="xl/workbook.xml"/>
</Relationships>
XML;

$workbook = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Fines" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;

$wbRels = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
    Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"
    Target="styles.xml"/>
</Relationships>
XML;

// Bold font style (xf index 1 = bold)
$styles = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>
  </cellXfs>
</styleSheet>
XML;

// ── pack into ZIP (XLSX = ZIP) ────────────────────────────────────────────────

$tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
$zip = new ZipArchive();
$zip->open($tmp, ZipArchive::OVERWRITE);

$zip->addFromString('[Content_Types].xml',            $contentTypes);
$zip->addFromString('_rels/.rels',                    $relsRoot);
$zip->addFromString('xl/workbook.xml',                $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels',     $wbRels);
$zip->addFromString('xl/styles.xml',                  $styles);
$zip->addFromString('xl/worksheets/sheet1.xml',       $sheetXml);

$zip->close();

$filename = "eso_fines_{$from}_{$to}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
unlink($tmp);
exit;
