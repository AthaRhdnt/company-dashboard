<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class DeliveryExport implements WithEvents, WithCustomStartCell, WithTitle
{
    protected $invoice;
    protected $amountInWords;

    public function __construct(array $invoiceData)
    {
        $this->invoice       = $invoiceData;
        $this->amountInWords = $invoiceData['financials']['amountInWords'] ?? '';

    }

    // Sheet will start at A1
    public function startCell(): string
    {
        return 'A1';
    }

    // ✅ Sheet Name
    public function title(): string
    {
        return 'DO';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Disable gridlines
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
            },
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
            10 => 5.2,
            16 => 5.2,
            18 => 20.2,
            19 => 5.2,
            20 => 30,
            22 => 20.2,
            58 => 14,
        ];

        // Rows 20-46: 12.8
        for ($i = 23; $i <= 51; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(13.5);
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
            'R' => 3.26,
            'S' => 1.71,
            'T' => 1.4,
            'U' => 1.4,
            'V' => 1.71,
            'W' => 3.17,
            'X' => 2.71,
            'Y' => 2.71,
            'Z' => 2.71,
            'AA' => 2.71,
            'AB' => 2.71,
            'AC' => 2.71,
            'AD' => 1.4,
            'AE' => 1.4,
            'AF' => 2.71,
            'AG' => 2.71,
            'AH' => 2.71,
            'AI' => 2.71,
            'AJ' => 2.71,
            'AK' => 2.17,
            'AL' => 2.71,
            'AM' => 2.44,
            'AN' => 0.95,
        ];

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    protected function mergeCells(Worksheet $sheet)
    {
        $merges = [
            'A3:U4',
            'C8:F9',
            'W8:AA9',
            'B18:E18', 'F18:K18', 'L18:P18', 'Q18:W18', 'X18:AB18', 'AC18:AL18',
            'B19:E19', 'F19:K19', 'L19:P19', 'Q19:W19', 'X19:AB19', 'AC19:AL19',
            'B20:E20', 'F20:K20', 'L20:P20', 'Q20:W20', 'X20:AB20', 'AC20:AL20',
            'A22:F22', 'G22:X22', 'Y22:AB22', 'AC22:AM22',
            'A23:F23', 'G23:X23', 'Y23:Z23', 'AA23:AB23', 'AC23:AM23',
            'A52:AM52',
            'A53:J53', 'K53:T53', 'U53:AD53', 'AE53:AM53',
            'AE58:AM58',

            'A24:F24', 'Y24:Z24', 'AA24:AB24', 'AC24:AM24',
        ];

        // Dynamic merges for item rows (20-46)
        for ($row = 24; $row <= 51; $row++) {
            $merges[] = "A{$row}:F{$row}";
            $merges[] = "Y{$row}:Z{$row}";
            $merges[] = "AA{$row}:AB{$row}";
            $merges[] = "AC{$row}:AM{$row}";
        }

        foreach ($merges as $range) {
            $sheet->mergeCells($range);
        }
    }

    protected function addContent(Worksheet $sheet)
    {
        $invoice = $this->invoice;

        $sheet->setCellValue('A3', 'DELIVERY ORDER');

        $sheet->setCellValue('C8', 'SOLD TO');

        $sheet->setCellValue('W8', 'SHIPPED TO');

        $sheet->setCellValue('C11', 'PT. ' . strtoupper(($invoice['client']['client_name'] ?? '{client_name}')));
        $sheet->setCellValue('C12', $invoice['client']['address'] ?? '{address}');
        $sheet->setCellValue('C13', ($invoice['client']['subdistrict'] ?? '{subdistrict}') . ' - ' .
            ($invoice['client']['city'] ?? '{city}') . ' ' .
            ($invoice['client']['zipcode'] ?? '{zipcode}'));
        $sheet->setCellValue('C14', 'Telp. : ' . ($invoice['client']['phone_number'] ?? '{phone_number}') .
            '; Fax : ' . ($invoice['client']['fax_number'] ?? '{fax_number}'));
        $sheet->setCellValue('C15', 'UP : ' . 'Mr. ' . ($invoice['client']['contact_person_name'] ?? '{contact_person_name}'));

        $sheet->setCellValue('W11', 'PT. ' . strtoupper(($invoice['client']['client_name'] ?? '{client_name}')));
        $sheet->setCellValue('W12', $invoice['client']['address'] ?? '{address}');
        $sheet->setCellValue('W13', ($invoice['client']['subdistrict'] ?? '{subdistrict}') . ' - ' .
            ($invoice['client']['city'] ?? '{city}') . ' ' .
            ($invoice['client']['zipcode'] ?? '{zipcode}'));
        $sheet->setCellValue('W14', 'Telp. : ' . ($invoice['client']['phone_number'] ?? '{phone_number}') .
            '; Fax : ' . ($invoice['client']['fax_number'] ?? '{fax_number}'));
        $sheet->setCellValue('W15', 'UP : ' . 'Mr. ' . ($invoice['client']['contact_person_name'] ?? '{contact_person_name}'));

        $sheet->setCellValue('B18', 'PAGE');
        $sheet->setCellValue('F18', 'DO NO.');
        $sheet->setCellValue('L18', 'DATE');
        $sheet->setCellValue('Q18', 'PO NO.');
        $sheet->setCellValue('X18', 'SALES');
        $sheet->setCellValue('AC18', 'WARRANTY');

        $sheet->setCellValue('B20', '1/1');
        $sheet->setCellValue('F20', $invoice['invoice']['do_number'] ?? '-');
        $sheet->setCellValue('L20', $invoice['dates']['invDate'] ?? '');
        $sheet->setCellValue('Q20', data_get($invoice['order'], 'purchaseOrder.po_number') ?? '-');
        $sheet->setCellValue('X20', 'SR');

        $sheet->setCellValue('A22', 'ITEM CODE');
        $sheet->setCellValue('G22', 'ITEM DETAILS');
        $sheet->setCellValue('Y22', 'QUANTITY');
        $sheet->setCellValue('AC22', 'REMARKS');

        $items = $this->invoice['items'] ?? [];
        $row = 24;

        foreach ($items as $index => $item) {
            if ($row > 49) break;

            // Line number
            $sheet->setCellValue("A{$row}", $index + 1);

            // Split details by newline
            $detailLines = explode("\n", $item['details'] ?? '');

            // First line: item name
            $sheet->setCellValue("G{$row}", $detailLines[0] ?? '');
            $sheet->getStyle("G{$row}")->applyFromArray([
                'font' => [
                    'name' => 'Arial',
                    'size' => 9,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_BOTTOM,
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(-1);

            // Quantity
            $sheet->setCellValue("Y{$row}", $item['quantity'] ?? '');
            // Unit
            $sheet->setCellValue("AA{$row}", $item['unit'] ?? '');

            // Render remaining lines (specs) below
            $currentRow = $row + 1;
            for ($i = 1; $i < count($detailLines); $i++) {
                if ($currentRow > 45) break;
                $sheet->setCellValue("G{$currentRow}", $detailLines[$i]);
                $sheet->getStyle("G{$currentRow}")->applyFromArray([
                    'font' => ['name'=>'Arial','size'=>9],
                    'alignment' => [
                        'vertical'    => Alignment::VERTICAL_BOTTOM,
                        'horizontal'  => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
                $currentRow++;
            }

            // Next item starts after specs + 1 buffer row
            $row = $currentRow + 1;
        }

        $sheet->setCellValue('A52', 'Please ensure goods are in good order and condition when received. Goods sold are not returnable.');

        $sheet->setCellValue('A53', 'Issued By :');
        $sheet->setCellValue('K53', 'Approved By :');
        $sheet->setCellValue('U53', 'Delivered By :');
        $sheet->setCellValue('AE53', 'Received By :');
        
        $rich = new RichText();
        $symbolRun = $rich->createTextRun('k');
        $symbolRun->getFont()
            ->setName('Wingdings 2')
            ->setSize(11)
            ->getColor()->setARGB(Color::COLOR_BLACK);

        $textRun = $rich->createTextRun(' Invoice Copy');
        $textRun->getFont()
            ->setName('Arial Narrow')
            ->setSize(9)
            ->getColor()->setARGB(Color::COLOR_BLACK);
        $sheet->setCellValue('AE58', $rich);
    }

    protected function applyBorders(Worksheet $sheet)
    {
        $black = ['rgb' => '000000'];
        $white = ['rgb' => 'FFFFFF'];

        $sheet->getStyle('A1:AA1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:A57')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('AM8:AM57')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A57:AM57')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('B9:S16')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('V9:AL16')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);

        $sheet->getStyle('B18:AL20')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black],
            'vertical' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('B18:AL18')->getBorders()->applyFromArray([
            'vertical' => ['borderStyle' => Border::BORDER_THIN, 'color' => $white]
        ]);
        $sheet->getStyle('B19:AL20')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);

        $sheet->getStyle('A22:AM22')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black],
            'vertical' => ['borderStyle' => Border::BORDER_THIN, 'color' => $white]
        ]);
        $sheet->getStyle('A23:F51')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('Y23:AB51')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('AC23:AM51')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('A23:AM51')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);

        $sheet->getStyle('A52:AM52')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);

        $sheet->getStyle('A53:J57')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('K53:T57')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('U53:AD57')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
        $sheet->getStyle('AE53:AM57')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black]
        ]);
    }

    protected function applyStyles(Worksheet $sheet)
    {
        $black = ['rgb' => '000000'];
        $white = ['rgb' => 'ffffff'];

        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['name' => 'Arial Black', 'size' => 20, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        foreach (['C', 'W'] as $col) {
            $sheet->getStyle("{$col}8")->applyFromArray([
                'font'      => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => true, 'color' => $black],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }

        $clientRows = [11,12,13,14,15];
        foreach ($clientRows as $row) {
            $style = [
                'font'      => ['name' => 'Arial Narrow', 'size' => 10, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
            ];

            if ($row === 11 || $row === 15) {
                $style['font']['bold'] = true;
            }
            if ($row === 13) {
                $style['font']['underline'] = Font::UNDERLINE_SINGLE;
                foreach (['C', 'W'] as $col) {
                    $cell = $sheet->getCell("{$col}{$row}");
                    $cell->setValue(Str::upper($cell->getValue()));
                }
            }

            foreach (['C', 'W'] as $col) {
                $sheet->getStyle("{$col}{$row}")->applyFromArray($style);
            }
        }

        $sheet->getStyle('B18:AC18')->applyFromArray([
            'font'      => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => true, 'color' => $white],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('B20:AC20')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9,'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('Q20')->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('A22:AC22')->applyFromArray([
            'font'      => ['name' => 'Lucida Sans Unicode', 'size' => 9, 'bold' => true, 'color' => $white],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        for ($row = 24; $row <= 51; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:AC{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'CCFFCC'],
                    ],
                ]);
            }

            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $sheet->getStyle("Y{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);

            $sheet->getStyle("AA{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 9, 'color' => $black],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM, 'horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
        }

        $sheet->getStyle('52:52')->applyFromArray([
            'font'      => ['name' => 'Tahoma', 'size' => 8, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A53:AE53')->applyFromArray([
            'font'      => ['name' => 'Lucida Sans Unicode', 'size' => 8, 'color' => $black],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('AE58')->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
    }
}
