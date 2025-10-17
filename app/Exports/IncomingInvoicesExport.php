<?php

namespace App\Exports;

use App\Models\IncomingInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class IncomingInvoicesExport implements FromCollection, WithHeadings, WithStartRow, WithEvents
{
    // Define constants for the final required row positions
    protected const HEADER_ROW = 3; 
    protected const DATA_START_ROW = 4;
    
    // The custom number format for currency
    protected const CURRENCY_FORMAT = '_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)';

    /**
     * Set the starting row for data to 2. 
     */
    public function startRow(): int
    {
        return 2; 
    }

    /**
     * Define the data collection.
     */
    public function collection()
    {
        return IncomingInvoice::with(['vendor', 'department', 'order'])
            ->get()
            ->map(function ($invoice) {
                return [
                    'NO' => $invoice->id,
                    'SUPPLIER / SUBCON' => $invoice->vendor->vendor_name ?? 'N/A',
                    'D-CODE' => $invoice->department->department_code ?? 'N/A',
                    'INVOICE NO' => $invoice->inv_number,
                    'INV RCVD DATE' => $invoice->inv_received_date ? Carbon::parse($invoice->inv_received_date)->format('d-M-y') : '',
                    'INV / FP DATE' => $invoice->fp_date ? Carbon::parse($invoice->fp_date)->format('d-M-y') : '',
                    'FP S/N' => $invoice->fp_number,
                    'MCN ORDER NO' => $invoice->order->ord_number ?? 'N/A',
                    'CUR' => $invoice->cur ?? 'IDR',
                    'AMOUNT (IDR)' => $invoice->amount,
                    'PMT DATE' => $invoice->payment_date ? Carbon::parse($invoice->payment_date)->format('d-M-y') : '',
                    'REMARKS' => $invoice->remark ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'NO', 'SUPPLIER / SUBCON', 'D-CODE', 'INVOICE NO', 'INV RCVD DATE', 
            'INV / FP DATE', 'FP S/N', 'MCN ORDER NO', 'CUR', 'AMOUNT (IDR)', 
            'PMT DATE', 'REMARKS'
        ];
    }

    /**
     * Register events to apply styling, sizing, and place non-data cells/formulas.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // --- Insert 2 rows to push R1 (Headings) to R3 and data (R2) to R4 ---
                $sheet->insertNewRowBefore(1, 2);

                // Get the final row number after data is written and rows are inserted
                $lastDataRow = $sheet->getHighestRow(); 

                // Calculate the dynamic row numbers:
                $dataRangeEnd = max(self::DATA_START_ROW, $lastDataRow); 
                
                // Blank Rows (5) start immediately after data ends: R[DataEnd] + 1 to R[DataEnd] + 5
                $firstBlankRow = $dataRangeEnd + 1;
                
                // Instruction Row (R[DataEnd] + 6)
                // (R[DataEnd] + 1) + 5 blank rows = R[DataEnd] + 6
                $instructionRow = $dataRangeEnd + 6;
                
                // Total Row (R[DataEnd] + 7) - This is the very last row with content
                // (Instruction Row + 1) = R[DataEnd] + 7
                $totalRow = $dataRangeEnd + 7; 
                
                // ✨ FIX: The last exported row is the total row. 
                // This prevents "Invalid cell coordinate" error when data is empty.
                $lastExportRow = $totalRow; 

                // --- 1. SIZING (Widths and Heights) ---
                $this->applySizing($sheet, $dataRangeEnd, $lastExportRow);

                // --- 2. STATIC CONTENT (R1, R2) & SUMMARY FORMULAS (R1, R2, L[TotalRow]) ---
                $this->applyTitleAndSummaryCells($sheet, $dataRangeEnd, $totalRow);

                // --- 3. HEADER (R3) STYLES ---
                $this->applyHeaderStyles($sheet);
                
                // --- 4. DATA ROW STYLES (R4 to R[DataEnd]) ---
                $this->applyDataRowStyles($sheet, $dataRangeEnd);

                // --- 5. DYNAMIC FOOTER CONTENT & BORDERS (R[DataEnd]+1 to R[TotalRow]) ---
                $this->applyFooterStylesAndFormulas($sheet, $dataRangeEnd, $instructionRow, $totalRow);

                // --- 6. CONDITIONAL FORMATTING ---
                $this->applyConditionalFormatting($sheet, $totalRow);
            },
        ];
    }
    
    // --------------------------------------------------------------------------
    // --- Helper methods to organize logic ---
    // --------------------------------------------------------------------------

    protected function applySizing(Worksheet $sheet, int $dataRangeEnd, int $lastExportRow)
    {
        // Column Widths
        $sheet->getColumnDimension('A')->setWidth(6.71); 
        $sheet->getColumnDimension('B')->setWidth(25.71);
        $sheet->getColumnDimension('C')->setWidth(7.71);
        $sheet->getColumnDimension('D')->setWidth(19.71);
        $sheet->getColumnDimension('E')->setWidth(10.71);
        $sheet->getColumnDimension('F')->setWidth(10.71);
        $sheet->getColumnDimension('G')->setWidth(20.71);
        $sheet->getColumnDimension('H')->setWidth(18.71);
        $sheet->getColumnDimension('I')->setWidth(5.71);
        $sheet->getColumnDimension('J')->setWidth(20.71);
        $sheet->getColumnDimension('K')->setWidth(10.71);
        $sheet->getColumnDimension('L')->setWidth(12.71);
        
        // Row Heights
        $sheet->getRowDimension(1)->setRowHeight(20.2); 
        $sheet->getRowDimension(2)->setRowHeight(20.2); 
        $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(30); 
        
        // Apply row height 20.2 to all data rows, footer rows, and blank rows up to $lastExportRow
        for ($i = self::DATA_START_ROW; $i <= $lastExportRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20.2);
        }

        // Freeze Panes
        $sheet->freezePane('G4');
    }

    protected function applyTitleAndSummaryCells(Worksheet $sheet, int $dataRangeEnd, int $totalRow)
    {
        $dataRangeAmount = 'J' . self::DATA_START_ROW . ':J' . $dataRangeEnd;
        $dataRangeRemarks = 'L' . self::DATA_START_ROW . ':L' . $dataRangeEnd;

        // A1 Content (Title)
        $sheet->setCellValue('A1', 'LIST TAGIHAN MASUK 2025');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Arial Black', 'size' => 20, 'color' => ['rgb' => 'C0514D']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER], 
        ]);

        // J[TotalRow] Formula: =SUBTOTAL(9;L4:L[DataEnd])
        $totalAmountFormula = '=SUBTOTAL(9,' . $dataRangeAmount . ')';
        $sheet->setCellValue('J' . $totalRow, $totalAmountFormula);
        
        // J1 content is =L[TotalRow]
        $sheet->setCellValue('J1', '=J' . $totalRow); 
        
        // I1 content is =SUMIF(L4:L[DataEnd],"*PPH*",J4:J[DataEnd])/J1
        $sheet->setCellValue('I1', '=SUMIF(' . $dataRangeRemarks . ',"*PPH*",' . $dataRangeAmount . ')/J1');

        // Number Formatting for Summary cells
        $sheet->getStyle('I1')->getNumberFormat()->setFormatCode('0%');
        $sheet->getStyle('J1')->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT); 

        // Apply Font Arial, Size 10 to I1:J1 range (covers both sets)
        $sheet->getStyle('I1:J1')->getFont()->setName('Arial')->setSize(10);
        $sheet->getStyle('I1')->getFont()->setBold(true);

        // Apply Middle and Center Alignment to I1
        $sheet->getStyle('I1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        
        // Apply Middle Alignment (Vertical Center) to J1
        $sheet->getStyle('J1')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Apply Middle Alignment 
        $sheet->getStyle('A1:L2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    protected function applyHeaderStyles(Worksheet $sheet)
    {
        $headerRange = 'A' . self::HEADER_ROW . ':L' . self::HEADER_ROW;
        
        // R3 General Style
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C0514D']],
            // Top and Center Alignment
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP], 
            'borders' => [
                // Outline border Orange, Accent 6, Darker 50% (RGB 974706) color, Medium line
                'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '974706']], 
                // Inside border White, Background 1 (RGB FFFFFF) color, Medium line
                'inside' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ]);

        //Apply Wrap Text specifically to Columns E and F
        $wrapTextRange = 'E' . self::HEADER_ROW . ':F' . self::HEADER_ROW;
        $sheet->getStyle($wrapTextRange)->getAlignment()->setWrapText(true);
        
        // Enable Filter
        $sheet->setAutoFilter($headerRange);
    }

    protected function applyDataRowStyles(Worksheet $sheet, int $dataRangeEnd)
    {
        $fullDataRange = 'A' . self::DATA_START_ROW . ':L' . $dataRangeEnd;
        $sheet->getStyle($fullDataRange)->getFont()->setName('Arial')->setSize(10);
        
        // Define column-specific alignment rules
        $alignmentRules = [
            'A' => Alignment::HORIZONTAL_CENTER, 
            'B' => Alignment::HORIZONTAL_LEFT,   
            'C' => Alignment::HORIZONTAL_CENTER, 
            'D' => Alignment::HORIZONTAL_LEFT,   
            'E' => Alignment::HORIZONTAL_CENTER, 
            'F' => Alignment::HORIZONTAL_CENTER, 
            'G' => Alignment::HORIZONTAL_LEFT,   
            'H' => Alignment::HORIZONTAL_LEFT,   
            'I' => Alignment::HORIZONTAL_CENTER, 
            'J' => Alignment::HORIZONTAL_RIGHT,   
            'K' => Alignment::HORIZONTAL_CENTER, 
            'L' => Alignment::HORIZONTAL_LEFT,
        ];
        
        // Apply vertical center alignment to all data cells
        $sheet->getStyle($fullDataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Apply horizontal alignment for each column
        foreach ($alignmentRules as $col => $horizontalAlignment) {
            $sheet->getStyle($col . self::DATA_START_ROW . ':' . $col . $dataRangeEnd)
                ->getAlignment()->setHorizontal($horizontalAlignment);
        }
        
        // Apply custom currency format to all data cells in column L
        $sheet->getStyle('J' . self::DATA_START_ROW . ':J' . $dataRangeEnd)
            ->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
    }

    protected function applyFooterStylesAndFormulas(Worksheet $sheet, int $dataRangeEnd, int $instructionRow, int $totalRow)
    {
        $dataRangeAmount = 'J' . self::DATA_START_ROW . ':J' . $dataRangeEnd;
        $dataRangeA = 'A' . self::DATA_START_ROW . ':A' . $dataRangeEnd;

        // --- Instruction Row Content and Style ---
        $sheet->setCellValue('B' . $instructionRow, 'Add new data above this row');
        $sheet->getStyle('B' . $instructionRow)->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'italic' => true, 'color' => ['rgb' => 'FF0000']], 
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER] 
        ]);

        $sheet->getStyle('J' . $totalRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle('J' . $totalRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('J' . $totalRow)->getFont()->setName('Arial')->setSize(12)->setBold(true);
        $sheet->getStyle('J' . $totalRow)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('J' . $totalRow)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1F497D']]]);

        // ======================================================================
        // --- SIMPLIFIED BORDERS MODIFICATION (R4 to R[InstructionRow]) ---
        $dataAreaRange = 'A' . self::DATA_START_ROW . ':L' . $instructionRow;

        // 1. Apply Dotted Horizontal and Thin Solid Vertical to the entire data area
        $sheet->getStyle($dataAreaRange)->applyFromArray([
            'borders' => [
                'horizontal' => ['borderStyle' => Border::BORDER_DOTTED, 'color' => ['rgb' => '000000']], 
                'vertical' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ]
        ]);

        // 2. Apply the MEDIUM OUTLINE OVERRIDES

        // Outer Left Border (Column A) is MEDIUM
        $leftColumnRange = 'A' . self::DATA_START_ROW . ':A' . $instructionRow;
        $sheet->getStyle($leftColumnRange)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        
        // Outer Right Border (Column N) is MEDIUM
        $rightColumnRange = 'L' . self::DATA_START_ROW . ':L' . $instructionRow;
        $sheet->getStyle($rightColumnRange)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
        
        // Bottom Border of the Instruction Row is MEDIUM
        $bottomRowRange = 'A' . $instructionRow . ':L' . $instructionRow;
        $sheet->getStyle($bottomRowRange)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);


        // 3. Apply the MEDIUM ORANGE Top Border (Header separation)
        $topRowRange = 'A' . self::DATA_START_ROW . ':L' . self::DATA_START_ROW;
        $sheet->getStyle($topRowRange)->getBorders()->getTop()->applyFromArray([
            'borderStyle' => Border::BORDER_MEDIUM, 
            'color' => ['rgb' => '974706']
        ]);
        
        // 4. Total Row Border (A[TotalRow]:L[TotalRow])
        $totalRange = 'A' . $totalRow . ':L' . $totalRow;
        $sheet->getStyle($totalRange)->applyFromArray([
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                'inside' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ]
        ]);
    }

    protected function applyConditionalFormatting(Worksheet $sheet, int $totalRow)
    {       
        // CF 1: Cell Value greater than 0.3 -> Fill RGB FF0000, Font White
        $cf1 = new Conditional();
        $cf1->setConditionType(Conditional::CONDITION_CELLIS);
        $cf1->setOperatorType(Conditional::OPERATOR_GREATERTHAN);
        // Condition should be a numeric value
        $cf1->setConditions([0.3]);
        $cf1->getStyle()->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getEndColor()->setRGB('FF0000');
        $cf1->getStyle()->getFont()
            ->getColor()->setRGB('FFFFFF');
        
        // Apply CF 1 & 2 to K[TotalRow]
        $sheet->getStyle('I1')->setConditionalStyles([$cf1]);
    }
}