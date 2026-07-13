<?php

namespace App\Support;

use ZipArchive;

/**
 * Générateur .xlsx minimal, sans dépendance externe (utilise l'extension zip).
 *
 * Produit un classeur Office Open XML multi-feuilles avec un jeu de styles
 * réduit : titre, en-tête de section, en-tête de colonne, cellule normale.
 * Suffisant pour un rapport de statistiques lisible dans Excel / LibreOffice.
 *
 * Usage :
 *   $x = new SimpleXlsxWriter();
 *   $x->addSheet('Vue d\'ensemble', function ($s) {
 *       $s->title('Rapport');
 *       $s->headerRow(['Indicateur', 'Valeur']);
 *       $s->row(['Inscrits', 1947], [null, SimpleXlsxWriter::S_NORMAL]);
 *   });
 *   return $x->download('rapport.xlsx');
 */
class SimpleXlsxWriter
{
    // Index de style (doivent correspondre à l'ordre dans styles.xml).
    public const S_NORMAL  = 0;
    public const S_TITLE   = 1;
    public const S_SECTION = 2;
    public const S_HEADER  = 3;
    public const S_MUTED   = 4;

    /** @var array<int,array{name:string,rows:array}> */
    private array $sheets = [];

    /**
     * Ajoute une feuille. Le callback reçoit un SheetBuilder.
     */
    public function addSheet(string $name, callable $build): self
    {
        $builder = new XlsxSheetBuilder();
        $build($builder);

        $this->sheets[] = [
            'name' => $this->sanitizeSheetName($name),
            'rows' => $builder->rows(),
        ];

        return $this;
    }

    /**
     * Écrit le fichier sur disque et renvoie le chemin.
     */
    public function save(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Impossible de créer le fichier xlsx : {$path}");
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->sheetXml($sheet['rows']));
        }

        $zip->close();

        return $path;
    }

    /**
     * Génère le fichier et renvoie une réponse de téléchargement Laravel.
     */
    public function download(string $filename)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $this->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function sanitizeSheetName(string $name): string
    {
        // Excel : max 31 caractères, sans : \ / ? * [ ]
        $name = preg_replace('/[:\\\\\/?*\[\]]/', ' ', $name);
        return mb_substr(trim($name), 0, 31);
    }

    // ── Génération XML ────────────────────────────────────────────

    private function contentTypesXml(): string
    {
        $overrides = '';
        foreach ($this->sheets as $i => $sheet) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1)
                . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides
            . '</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        $sheetsXml = '';
        foreach ($this->sheets as $i => $sheet) {
            $sheetsXml .= '<sheet name="' . $this->esc($sheet['name']) . '" sheetId="' . ($i + 1)
                . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    private function workbookRelsXml(): string
    {
        $rels = '';
        foreach ($this->sheets as $i => $sheet) {
            $rels .= '<Relationship Id="rId' . ($i + 1)
                . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
                . 'Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        // La feuille de styles prend le prochain rId libre.
        $stylesId = count($this->sheets) + 1;
        $rels .= '<Relationship Id="rId' . $stylesId
            . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    /**
     * Styles : ordre = index public S_*.
     * 0 normal, 1 titre, 2 section, 3 en-tête colonne, 4 discret.
     */
    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="5">'
            .   '<font><sz val="11"/><color rgb="FF1F2937"/><name val="Calibri"/></font>' // 0 normal
            .   '<font><b/><sz val="16"/><color rgb="FF0B6E4F"/><name val="Calibri"/></font>' // 1 titre
            .   '<font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' // 2 section
            .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' // 3 header
            .   '<font><i/><sz val="10"/><color rgb="FF6B7280"/><name val="Calibri"/></font>' // 4 muted
            . '</fonts>'
            . '<fills count="5">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF0B6E4F"/></patternFill></fill>' // 2 vert foncé
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF15803D"/></patternFill></fill>' // 3 vert header
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/></patternFill></fill>' // 4 vert clair
            . '</fills>'
            . '<borders count="2">'
            .   '<border><left/><right/><top/><bottom/><diagonal/></border>'
            .   '<border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right>'
            .     '<top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="5">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>' // 0 normal
            .   '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' // 1 titre
            .   '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"><alignment vertical="center"/></xf>' // 2 section
            .   '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment vertical="center" wrapText="1"/></xf>' // 3 header
            .   '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>' // 4 muted
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    /**
     * @param array<int,array{cells:array}> $rows
     */
    private function sheetXml(array $rows): string
    {
        $rowsXml   = '';
        $maxCols   = 0;

        foreach ($rows as $rIndex => $row) {
            $rowNum = $rIndex + 1;
            $cellsXml = '';
            $colCount = count($row['cells']);
            $maxCols  = max($maxCols, $colCount);

            foreach ($row['cells'] as $cIndex => $cell) {
                $ref   = $this->colLetter($cIndex) . $rowNum;
                $style = $cell['style'];
                $value = $cell['value'];

                if ($value === null || $value === '') {
                    $cellsXml .= '<c r="' . $ref . '" s="' . $style . '"/>';
                } elseif (is_int($value) || is_float($value)) {
                    $cellsXml .= '<c r="' . $ref . '" s="' . $style . '"><v>' . $value . '</v></c>';
                } else {
                    $cellsXml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
                        . $this->esc((string) $value) . '</t></is></c>';
                }
            }

            $rowsXml .= '<row r="' . $rowNum . '">' . $cellsXml . '</row>';
        }

        $lastCol = $this->colLetter(max(0, $maxCols - 1));
        $dimension = 'A1:' . $lastCol . max(1, count($rows));

        // Largeurs de colonnes lisibles par défaut.
        $cols = '<cols><col min="1" max="' . max(1, $maxCols) . '" width="26" customWidth="1"/></cols>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="' . $dimension . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" state="frozen"/></sheetView></sheetViews>'
            . $cols
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . '</worksheet>';
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $mod    = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index  = intdiv($index - 1, 26);
        }
        return $letter;
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
