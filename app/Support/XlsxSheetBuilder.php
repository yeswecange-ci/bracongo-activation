<?php

namespace App\Support;

/**
 * Constructeur de lignes pour une feuille de SimpleXlsxWriter.
 * Accumule des lignes typées (titre, section, en-tête, données).
 */
class XlsxSheetBuilder
{
    /** @var array<int,array{cells:array<int,array{value:mixed,style:int}>}> */
    private array $rows = [];

    /** Ligne de titre (une cellule). */
    public function title(string $text): self
    {
        return $this->pushSingle($text, SimpleXlsxWriter::S_TITLE);
    }

    /** Ligne discrète (sous-titre, note). */
    public function note(string $text): self
    {
        return $this->pushSingle($text, SimpleXlsxWriter::S_MUTED);
    }

    /** Bandeau de section coloré (une cellule mise en avant). */
    public function section(string $text): self
    {
        return $this->pushSingle($text, SimpleXlsxWriter::S_SECTION);
    }

    /** Ligne d'en-tête de colonnes. */
    public function headerRow(array $labels): self
    {
        $cells = [];
        foreach ($labels as $label) {
            $cells[] = ['value' => $label, 'style' => SimpleXlsxWriter::S_HEADER];
        }
        $this->rows[] = ['cells' => $cells];
        return $this;
    }

    /**
     * Ligne de données. $styles permet de surcharger le style par colonne
     * (null = S_NORMAL).
     *
     * @param array<int,mixed>    $values
     * @param array<int,int|null> $styles
     */
    public function row(array $values, array $styles = []): self
    {
        $cells = [];
        foreach (array_values($values) as $i => $value) {
            $cells[] = [
                'value' => $value,
                'style' => $styles[$i] ?? SimpleXlsxWriter::S_NORMAL,
            ];
        }
        $this->rows[] = ['cells' => $cells];
        return $this;
    }

    /** Ligne vide (espacement). */
    public function blank(): self
    {
        $this->rows[] = ['cells' => []];
        return $this;
    }

    /** @return array<int,array{cells:array}> */
    public function rows(): array
    {
        return $this->rows;
    }

    private function pushSingle(string $text, int $style): self
    {
        $this->rows[] = ['cells' => [['value' => $text, 'style' => $style]]];
        return $this;
    }
}
