<?php
namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ReceiptExport implements WithEvents, WithCustomStartCell, WithTitle
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
        return 'RPT';
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
            1  => 6,
            2  => 12,
            3  => 13.5,
            4  => 13.5,
            8  => 13,
            10 => 13.5,
            16 => 13.5,
            17 => 13.5,
            19 => 13,
            21 => 13,
            23 => 13,
            25 => 13,
        ];

        foreach ($heights as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        // Default row height
        $sheet->getDefaultRowDimension()->setRowHeight(11.5);
    }

    protected function applyColumnWidths(Worksheet $sheet)
    {
        $scale  = 1.08;
        $widths = [
            'A'  => 1.98,
            'B'  => 1.71,
            'C'  => 1.71,
            'D'  => 2.71,
            'E'  => 2.71,
            'F'  => 2.71,
            'G'  => 1.71,
            'H'  => 2.44,
            'I'  => 2.71,
            'J'  => 1.51,
            'K'  => 1.51,
            'L'  => 2.71,
            'M'  => 2.71,
            'N'  => 2.71,
            'O'  => 1.8,
            'P'  => 2.71,
            'Q'  => 2.71,
            'R'  => 2.71,
            'S'  => 1.71,
            'T'  => 1.4,
            'U'  => 1.4,
            'V'  => 1.71,
            'W'  => 2.71,
            'X'  => 2.71,
            'Y'  => 2.71,
            'Z'  => 2.71,
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
            'AK' => 3.17,
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
            'E5:K5',
            'E6:K6',
            'H8:AL8',
            'H10:AL11',
            'H13:AL14',
            'B16:E17', 'F16:AL17',
            'B19:C19', 'B21:C21', 'B23:C23', 'B25:C25', 
        ];

        foreach ($merges as $range) {
            $sheet->mergeCells($range);
        }
    }

    protected function addContent(Worksheet $sheet)
    {
        // Static and dynamic content
        $invoice = $this->invoice;

        // Title
        $sheet->setCellValue('A3', 'RECEIPT');

        $sheet->setCellValue('B5', 'No.');
        $sheet->setCellValue('B6', 'Date');
        $sheet->setCellValue('B8', 'Receipt From');
        $sheet->setCellValue('B10', 'Amount');
        $sheet->setCellValue('B13', 'For Payment');
        $sheet->setCellValue('B16', 'Rp.');
        $sheet->setCellValue('B25', 'X');

        $sheet->setCellValue('D5', ':');
        $sheet->setCellValue('D6', ':');

        $sheet->setCellValue('E5', $invoice['invoice']['inv_number'] ?? '-');
        $sheet->setCellValue('E6', $invoice['dates']['invDate'] ?? '');

        $sheet->setCellValue('E19', 'CASH');
        $sheet->setCellValue('E21', 'CHEQUE');
        $sheet->setCellValue('E23', 'BILYET GIRO');
        $sheet->setCellValue('E25', 'BANK TRANSFER');

        $sheet->setCellValue('E26', 'PT. Master Cipta Nusantara');
        $sheet->setCellValue('E27', 'Bank Mandiri Cab. Jakarta Pancoran');
        $sheet->setCellValue('E28', 'Acc. No. 070 - 0002118144');

        $amount = $invoice['invoice']['amount'] ?? 0;
        $vat = floor($amount * 0.11);
        $formattedVat = number_format($vat, 0, ',', '.') . ',-';
        $sheet->setCellValue('F16', $formattedVat);

        $sheet->setCellValue('G8', ':');
        $sheet->setCellValue('G10', ':');
        $sheet->setCellValue('G13', ':');

        $sheet->setCellValue('H8', 'PT. ' . strtoupper(($invoice['client']['client_name'] ?? '{client_name}')));
        $sheet->setCellValue('H10', $this->amountInWords);
        $sheet->setCellValue('H13', 'Tax Invoice No. ' . ($invoice['invoice']['fp_number'] ?? '-'));
        
        $sheet->setCellValue('Y19', 'PT. MASTER CIPTA NUSANTARA');
        $sheet->setCellValue('Y26', 'Andrianto Muljarsono');
        $sheet->setCellValue('Y27', 'Director');
    }

    protected function applyBorders(Worksheet $sheet)
    {
        $black = ['rgb' => '000000'];

        $sheet->getStyle('A1:AA1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:A28')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A28:AM28')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('AM7:AM28')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B19:C19')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B21:C21')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B23:C23')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B25:C25')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B16:AL17')->getBorders()->applyFromArray([
            'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => $black],
        ]);
        $sheet->getStyle('Y25:AI25')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    protected function applyStyles(Worksheet $sheet)
    {
        $black = ['rgb' => '000000'];

        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['name' => 'Arial Black', 'size' => 20, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'indent'     => 1,
            ],
        ]);

        $sheet->getStyle('B5')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
            ],
        ]);
        $sheet->getStyle('B6')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
            ],
        ]);
        $sheet->getStyle('B8')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('B10')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('B13')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('B16')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getStyle('B25')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('D5')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
            ],
        ]);
        $sheet->getStyle('D6')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
            ],
        ]);

        $sheet->getStyle('E5')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('E6')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('E19')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('E21')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('E23')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('E25')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);

        $sheet->getStyle('E26')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('E27')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('E28')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);

        $sheet->getStyle('F16')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
            'quotePrefix' => true,
        ]);

        $sheet->getStyle('G8')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('G10')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('G13')->applyFromArray([
            'font'      => ['name' => 'Arial Narrow', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);

        $sheet->getStyle('H8')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => $black],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFFF']],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('H10')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => $black],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFFF']],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle('H13')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => $black],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFFF']],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        
        $sheet->getStyle('Y19')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('Y26')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        $sheet->getStyle('Y27')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'color' => $black],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_BOTTOM,
            ],
        ]);
    }
}
