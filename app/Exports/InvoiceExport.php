<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class InvoiceExport implements WithEvents, WithCustomStartCell, WithTitle
{
    protected $invoice;
    protected $amountInWords;

    public function __construct(array $invoiceData)
    {
        $this->invoice = $invoiceData;
        $this->amountInWords = $invoiceData['financials']['amountInWords'] ?? '';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return 'INV';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- Disable gridlines ---
                $sheet->setShowGridlines(false);

                // --- Apply row heights ---
                $this->applyRowHeights($sheet);

                // --- Apply column widths ---
                $this->applyColumnWidths($sheet);

                // --- Merge cells ---
                $this->mergeCells($sheet);

                // --- Add content ---
                $this->addContent($sheet);

                // --- Apply borders ---
                $this->applyBorders($sheet);

                // --- Apply styles to content ---
                $this->applyStyles($sheet);
            }
        ];
    }

    protected function applyRowHeights(Worksheet $sheet)
    {
        $heights = [
            1 => 6,
            2 => 12,
            3 => 13.5,
            4 => 13.5,
            8 => 6.8,
            9 => 6.8,
            10 => 6.8,
            11 => 13,
            12 => 13,
            13 => 13,
            14 => 13,
            15 => 13,
            16 => 5.2,
            17 => 12,
            18 => 18,
            19 => 6,
            50 => 13.5,
            51 => 20.2,
            52 => 12,
            62 => 16.5,
            63 => 12.8,
            64 => 12.8,
        ];

        // Rows 20-46: 12.8
        for ($i = 20; $i <= 46; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(12.8);
        }

        // Rows 47-48: 6
        for ($i = 47; $i <= 48; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(6);
        }

        // Rows 55-61: 10.5
        for ($i = 55; $i <= 61; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(10.5);
        }

        foreach ($heights as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        // Default row height
        $sheet->getDefaultRowDimension()->setRowHeight(11.5);
    }

    protected function applyColumnWidths(Worksheet $sheet)
    {
        $scale = 1.08;
        $widths = [
            'A' => 2.71,
            'B' => 1.71,
            'C' => 1.71,
            'D' => 2.71,
            'E' => 2.71, 
            'F' => 2.71,
            'G' => 2.71,
            'H' => 2.71,
            'I' => 2.71,
            'J' => 1.51,
            'K' => 1.51,
            'L' => 2.71,
            'M' => 2.71,
            'N' => 2.71,
            'O' => 2.71,
            'P' => 2.71,
            'Q' => 2.71,
            'R' => 2.71,
            'S' => 1.71,
            'T' => 1.4,
            'U' => 1.4,
            'V' => 1.71,
            'W' => 2.71,
            'X' => 2.71,
            'Y' => 2.71,
            'Z' => 2.71,
            'AA' => 4.53,
            'AB' => 2.71,
            'AC' => 2.71,
            'AD' => 1.4,
            'AE' => 1.4,
            'AF' => 2.71,
            'AG' => 2.71,
            'AH' => 3.71,
            'AI' => 5.71,
            'AJ' => 2.71,
            'AK' => 3.17,
            'AL' => 2.71,
            'AM' => 2.71,
        ];

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    protected function mergeCells(Worksheet $sheet)
    {
        $merges = [
            'A3:U4',
            'B8:G9',
            'T9:X10',
            'Y9:Y10',
            'Z9:AM10',
            'T11:X11', 'Z11:AM11',
            'T12:X12', 'Z12:AM12',
            'T13:X13', 'Z13:AM13',
            'T14:X14', 'Z14:AM14',
            'T15:X15', 'Z15:AM15',
            'B18:C18', 'D18:U18', 'V18:Y18', 'AB18:AF18', 'AI18:AL18',
            'B47:C47', 'D47:U47', 'V47:Y47', 'AB47:AF47', 'AI47:AL47',
            'B48:C48', 'D48:U48', 'V48:W48',
            'Y49:AF49', 'AI49:AL49',
            'Y50:AF50', 'AI50:AL50',
            'Y51:AF51', 'AI51:AL51',
            'B50:W51',
            'B64:R65',
        ];

        // Dynamic merges for item rows (20-46)
        for ($row = 20; $row <= 46; $row++) {
            $merges[] = "A{$row}:B{$row}";
            $merges[] = "D{$row}:T{$row}";
            $merges[] = "U{$row}:W{$row}";
            $merges[] = "AB{$row}:AG{$row}";
            $merges[] = "AI{$row}:AL{$row}";
        }

        // Vertical ranges
        $verticalMerges = [
            ['B', 'C', 18, 19],
            ['D', 'U', 18, 19],
            ['V', 'Y', 18, 19],
            ['AB', 'AF', 18, 19],
            ['AI', 'AL', 18, 19],
            ['A', 'B', 20, 46],
            ['D', 'T', 20, 46],
            ['U', 'W', 20, 46],
            ['AB', 'AG', 20, 46],
            ['AI', 'AL', 20, 46],
            ['Y', 'AF', 49, 51],
            ['AI', 'AL', 49, 51],
        ];

        foreach ($verticalMerges as [$startCol, $endCol, $startRow, $endRow]) {
            for ($r = $startRow; $r <= $endRow; $r++) {
                $merges[] = "{$startCol}{$r}:{$endCol}{$r}";
            }
        }

        foreach ($merges as $range) {
            $sheet->mergeCells($range);
        }
    }

    protected function addContent(Worksheet $sheet)
    {
        // Static and dynamic content
        $invoice = $this->invoice;

        // Title
        $sheet->setCellValue('A3', 'INVOICE');

        // Charged To
        $sheet->setCellValue('B8', 'CHARGED TO');

        // Client Info
        $sheet->setCellValue('B11', 'PT. ' . strtoupper(($invoice['client']['client_name'] ?? '{client_name}')));
        $sheet->setCellValue('B12', $invoice['client']['address'] ?? '{address}');
        $sheet->setCellValue('B13', ($invoice['client']['subdistrict'] ?? '{subdistrict}') . ' - ' .
            ($invoice['client']['city'] ?? '{city}') . ' ' .
            ($invoice['client']['zipcode'] ?? '{zipcode}'));
        $sheet->setCellValue('B14', 'Telp. : ' . ($invoice['client']['phone_number'] ?? '{phone_number}') .
            '; Fax : ' . ($invoice['client']['fax_number'] ?? '{fax_number}'));
        
        $richText2 = new RichText();
        $richText2->createText('UP : ');
        $underlined = $richText2->createTextRun(
            'Bp. ' . ($invoice['client']['contact_person_name'] ?? '{contact_person_name}')
        );
        $underlined->getFont()
            ->setName('Arial Narrow')
            ->setSize(10)
            ->setBold(true)
            ->setUnderline(Font::UNDERLINE_SINGLE)
            ->setColor(new Color('000000'));
        $sheet->setCellValue('B15', $richText2);

        // Invoice metadata labels
        $sheet->setCellValue('T9', 'Inv. No.');
        $sheet->setCellValue('T11', 'Inv. Date');
        $sheet->setCellValue('T12', 'Due Date');
        $sheet->setCellValue('T13', 'FP S/N');
        $sheet->setCellValue('T14', 'PO No.');
        $sheet->setCellValue('T15', 'PO Date');

        // Colons
        foreach ([9,10,11,12,13,14,15] as $row) {
            $sheet->setCellValue("Y{$row}", ":");
        }

        // Invoice metadata values
        $sheet->setCellValue('Z9', $invoice['invoice']['inv_number'] ?? '-');
        $sheet->setCellValue('Z11', $invoice['dates']['invDate'] ?? '');
        $sheet->setCellValue('Z12', $invoice['dates']['dueDate'] ?? '');
        $sheet->setCellValue('Z13', $invoice['invoice']['fp_number'] ?? '-');
        $sheet->setCellValue('Z14', data_get($invoice['order'], 'purchaseOrder.po_number') ?? '-');
        $sheet->setCellValue('Z15', $invoice['dates']['poDate'] ?? '');

        // Table headers
        $sheet->setCellValue('B18', 'No.');
        $sheet->setCellValue('D18', 'Details');
        $sheet->setCellValue('V18', 'Quantity');
        $sheet->setCellValue('AB18', 'Unit Price');
        $sheet->setCellValue('AI18', 'Sub Total');

        // Items
        $items = $this->invoice['items'] ?? [];
        $row = 21;

        foreach ($items as $index => $item) {
            if ($row > 45) break;

            // Line number
            $sheet->setCellValue("A{$row}", $index + 1);

            // Split details by newline
            $detailLines = explode("\n", $item['details'] ?? '');

            // First line: item name
            $sheet->setCellValue("D{$row}", $detailLines[0] ?? '');
            $sheet->getStyle("D{$row}")->applyFromArray([
                'font' => [
                    'name' => 'Arial',
                    'size' => 9,
                    'bold' => true,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);

            // Quantity
            $sheet->setCellValue("U{$row}", $item['quantity'] ?? '');
            // Unit
            $sheet->setCellValue("X{$row}", $item['unit'] ?? '');
            // "Rp"
            $sheet->setCellValue("AA{$row}", 'Rp');
            // Unit Price
            $sheet->setCellValue("AB{$row}", $item['unit_price'] ?? 0);
            $sheet->setCellValue("AH{$row}", 'Rp');
            // Subtotal
            $sheet->setCellValue("AI{$row}", $item['sub_total'] ?? 0);

            // Render remaining lines (specs) below
            $currentRow = $row + 1;
            for ($i = 1; $i < count($detailLines); $i++) {
                if ($currentRow > 45) break;
                $sheet->setCellValue("D{$currentRow}", $detailLines[$i]);
                $currentRow++;
            }

            // Next item starts after specs + 1 buffer row
            $row = $currentRow + 1;
        }

        // Footer content
        $sheet->setCellValue('B49', 'Says :');
        $sheet->setCellValue('B50', $this->amountInWords);
        $sheet->setCellValue('Y49', 'Taxable Amount');
        $sheet->setCellValue('Y50', 'VAT');
        $sheet->setCellValue('Y51', 'Invoiced Amount');

        $amount = $invoice['invoice']['amount'] ?? 0;
        $vat = floor($amount * 0.11);
        $total = $amount + $vat;

        $sheet->setCellValue('AH49', 'Rp');
        $sheet->setCellValue('AH50', 'Rp');
        $sheet->setCellValue('AH51', 'Rp');

        $sheet->setCellValue('AI49', $amount);
        $sheet->setCellValue('AI50', $vat);
        $sheet->setCellValue('AI51', $total);

        // Company & signature
        $sheet->setCellValue('Y54', 'PT. MASTER CIPTA NUSANTARA');
        $sheet->setCellValue('Y63', 'Andrianto Muljarsono');
        $sheet->setCellValue('Y64', 'Director');
        $sheet->setCellValue('AM65', data_get($invoice['order'], 'ord_number') ?? '');

        // Bank info
        $sheet->setCellValue('B60', 'Please place your transfer to :');
        $sheet->setCellValue('B62', 'PT. Master Cipta Nusantara');
        $sheet->setCellValue('B63', 'Bank Mandiri Cabang Jakarta Pancoran');

        $richText = new RichText();

        // Part 1 → small
        $part1 = $richText->createTextRun('Acc. No. ');
        $part1->getFont()->setName('Arial');
        $part1->getFont()->setSize(9);
        $part1->getFont()->setBold(true);
        $part1->getFont()->getColor()->setARGB($black['argb'] ?? 'FF000000');

        // Part 2 → large
        $part2 = $richText->createTextRun('070 - 0002118144 ( IDR )');
        $part2->getFont()->setName('Arial');
        $part2->getFont()->setSize(14);
        $part2->getFont()->setBold(true);
        $part2->getFont()->getColor()->setARGB($black['argb'] ?? 'FF000000');

        $sheet->setCellValue('B64', $richText);

        // Keep alignment only (remove font styling here)
        $sheet->getStyle('B64')->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);
    }

    protected function applyBorders(Worksheet $sheet)
    {
        $black = ['rgb' => '000000'];

        // A9:S16 — Outside border
        $sheet->getStyle('A9:S16')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);

        // Outer border for T9:AM16
        $sheet->getStyle('T9:AM16')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
        ]);

        // Apply bottom border ONLY to rows 9–14 (NOT 15!)
        $rowsWithBottom = [9,10,11,12,13,14];
        foreach ($rowsWithBottom as $row) {
            $sheet->getStyle("T{$row}:AM{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }

        // A18:AM51 — Left/Right borders
        $sheet->getStyle('A18:AM51')->applyFromArray([
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black],
            ]
        ]);

        // Top/Bottom: A18:AM18 — Medium
        $sheet->getStyle('A18:AM18')->applyFromArray([
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => $black],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => $black],
            ]
        ]);

        // Bottom: A47:AM47 — Medium
        $sheet->getStyle('A47:AM47')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // Right: X48:X51
        $sheet->getStyle('X48:X51')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

        // Bottom: Y50:AM50
        $sheet->getStyle('Y50:AM50')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Bottom: A51:AM51 — Double
        $sheet->getStyle('A51:AM51')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // Bottom: Y62:AI62
        $sheet->getStyle('Y62:AI62')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    protected function applyStyles(Worksheet $sheet)
    {
        $black = ['rgb' => '000000'];

        // INVOICE title
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['name' => 'Arial Black', 'size' => 20, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'indent' => 1,
            ],
        ]);

        // CHARGED TO
        $sheet->getStyle('B8')->applyFromArray([
            'font' => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Client info
        $clientRows = [11,12,13,14,15];
        foreach ($clientRows as $row) {
            $style = [
                'font' => ['name' => 'Arial Narrow', 'size' => 10, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
            ];

            if ($row === 11 || $row === 15) {
                $style['font']['bold'] = true;
            }
            if ($row === 13) {
                $style['font']['underline'] = Font::UNDERLINE_SINGLE;
                $cell = $sheet->getCell("B{$row}");
                $cell->setValue(Str::upper($cell->getValue()));
            }

            $sheet->getStyle("B{$row}")->applyFromArray($style);
        }

        // Invoice metadata labels & colons
        $metaRows = [9,10,11,12,13,14,15];
        foreach ($metaRows as $row) {
            if (in_array($row, [9,10])) {
                $range = "T{$row}";
            } else {
                $range = "T{$row}";
            }

            $sheet->getStyle($range)->applyFromArray([
                'font' => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1,],
            ]);

            $sheet->getStyle("Y{$row}")->applyFromArray([
                'font' => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // Invoice metadata values
        foreach ([9,10,11,12,13,14,15] as $row) {
            $sheet->getStyle("Z{$row}:AM{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_BOTTOM, 
                    'horizontal' => Alignment::HORIZONTAL_LEFT, 
                ],
            ]);
        }

        // Table headers
        $headerCells = [
            'B18' => Alignment::HORIZONTAL_LEFT,
            'D18' => Alignment::HORIZONTAL_LEFT,
            'V18' => Alignment::HORIZONTAL_CENTER,
            'AB18' => Alignment::HORIZONTAL_RIGHT,
            'AI18' => Alignment::HORIZONTAL_RIGHT,
        ];

        foreach ($headerCells as $cell => $align) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => true, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => $align],
            ]);
        }

        // Item rows (21–45): generic style
        for ($row = 21; $row <= 45; $row++) {
            // Item number
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);

            // Item name
            // $sheet->getStyle("D21")->applyFromArray([
            //     'font' => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            //     'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_LEFT],
            // ]);

            // Quantity
            $sheet->getStyle("U{$row}:V{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);

            // Unit
            $sheet->getStyle("X{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 10, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
            ]);

            // "Rp"
            foreach (['AA', 'AH'] as $col) {
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10, 'color' => $black],
                    'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
            }

            // Unit Price
            $sheet->getStyle("AB{$row}:AG{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Subtotal
            $sheet->getStyle("AI{$row}:AL{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Specs
            $specStyle = [
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_LEFT],
            ];
            $sheet->getStyle("D{$row}")->applyFromArray($specStyle);
        }

        // Apply number formatting to unit price and subtotal
        for ($r = 21; $r <= 45; $r++) {
            // Unit Price (AB:AG) → should be number
            $sheet->getStyle("AB{$r}:AG{$r}")
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');

            // Subtotal (AI:AL)
            $sheet->getStyle("AI{$r}:AL{$r}")
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
        }

        // Footer amounts
        $sheet->getStyle('AI49:AL49')->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $sheet->getStyle('AI50:AL50')->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $sheet->getStyle('AI51:AL51')->getNumberFormat()->setFormatCode('"Rp" #,##0');

        // Footer: "Says :"
        $sheet->getStyle('B49')->applyFromArray([
            'font' => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);

        // Amount in words
        $sheet->getStyle('B50')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
        ]);

        // Tax labels
        foreach ([49,50,51] as $row) {
            $horizontal = ($row === 51) ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_RIGHT;
            $bold = ($row === 51);
            $sheet->getStyle("Y{$row}")->applyFromArray([
                'font' => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => $bold, 'color' => $black],
                'alignment' => ['vertical' => ($row === 51 ? Alignment::VERTICAL_CENTER : Alignment::VERTICAL_BOTTOM), 'horizontal' => $horizontal],
            ]);
        }

        // "Rp" in footer
        foreach ([49,50,51] as $row) {
            $bold = ($row === 51);
            $sheet->getStyle("AH{$row}")->applyFromArray([
                'font' => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => $bold, 'color' => $black],
                'alignment' => ['vertical' => ($row === 51 ? Alignment::VERTICAL_CENTER : Alignment::VERTICAL_BOTTOM), 'horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // Amount values
        $sheet->getStyle('AI49:AL49')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
        $sheet->getStyle('AI50:AL50')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
        $sheet->getStyle('AI51:AL51')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Company name & signature
        $sheet->getStyle('Y54')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
        $sheet->getStyle('Y63:Y64')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
        $sheet->getStyle('Y63')->getFont()->setBold(true);

        $sheet->getStyle('AM65')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 6, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        // Bank info
        $sheet->getStyle('B60')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
        $sheet->getStyle('B62:B63')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
        ]);
    }
}