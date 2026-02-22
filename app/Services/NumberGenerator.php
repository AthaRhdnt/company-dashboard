<?php
namespace App\Services;

use App\Models\IncomingInvoice;
use App\Models\Order;
use App\Models\OutgoingInvoice;
use Carbon\Carbon;

class NumberGenerator
{
    // Convert 1-12 → I–XII
    public static function toRoman(int $month): string
    {
        $map = [
            1 => 'I', 2   => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6   => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? 'I';
    }

    // Generate order number
    public static function generateOrderNumber(Carbon $date): string
    {
        // Get all orders in the same month/year
        $ordersInMonth = Order::whereYear('ord_date', $date->year)
            ->orderBy('ord_number', 'desc')
            ->get();

        $counter = 1;

        foreach ($ordersInMonth as $order) {
            if (preg_match('/^(\d{3})\s*\/\s*A2-AM/i', $order->ord_number, $matches)) {
                $lastNumber = (int) $matches[1];
                $counter    = $lastNumber + 1;
                break; // only need the highest
            }
        }

        return sprintf(
            '%03d / A2-AM / %s / %s',
            $counter,
            self::toRoman($date->month),
            $date->format('y')
        );
    }

    // Generate outgoing invoice number
    public static function generateOutgoingInvoiceNumber(Carbon $date): string
    {
        // Get all outgoing invoices in the same month/year
        $invoicesInMonth = OutgoingInvoice::whereYear('inv_date', $date->year)
            ->orderBy('inv_number', 'desc')
            ->get();

        $counter = 1;

        foreach ($invoicesInMonth as $invoice) {
            // Match: "001 / INV-F / XII / 25" (allow spaces around /)
            if (preg_match('/^(\d{3})\s*\/\s*INV-F/i', $invoice->inv_number, $matches)) {
                $lastNumber = (int) $matches[1];
                $counter    = $lastNumber + 1;
                break; // highest valid number found
            }
        }

        return sprintf(
            '%03d / INV-F / %s / %s',
            $counter,
            self::toRoman($date->month),
            $date->format('y')
        );
    }

    // Generate receipt number
    public static function generateReceiptNumber(Carbon $date): string
    {
        // Get all outgoing invoices in the same month/year
        $invoicesInMonth = OutgoingInvoice::whereYear('inv_date', $date->year)
            ->orderBy('inv_number', 'desc')
            ->get();

        $counter = 1;

        foreach ($invoicesInMonth as $invoice) {
            // Match: "001 / INV-F / XII / 25" (allow spaces around /)
            if (preg_match('/^(\d{3})\s*\/\s*INV-F/i', $invoice->inv_number, $matches)) {
                $lastNumber = (int) $matches[1];
                $counter    = $lastNumber + 1;
                break; // highest valid number found
            }
        }

        return sprintf(
            '%03d / RPT / %s / %s',
            $counter,
            self::toRoman($date->month),
            $date->format('y')
        );
    }

    // Generate delivery order number
    public static function generateDeliverOrderNumber(Carbon $date): string
    {
        // Get all outgoing invoices in the same month/year
        $invoicesInMonth = OutgoingInvoice::whereYear('inv_date', $date->year)
            ->orderBy('inv_number', 'desc')
            ->get();

        $counter = 1;

        foreach ($invoicesInMonth as $invoice) {
            // Match: "001 / INV-F / XII / 25" (allow spaces around /)
            if (preg_match('/^(\d{3})\s*\/\s*INV-F/i', $invoice->inv_number, $matches)) {
                $lastNumber = (int) $matches[1];
                $counter    = $lastNumber + 1;
                break; // highest valid number found
            }
        }

        return sprintf(
            '%03d / DO / %s / %s',
            $counter,
            self::toRoman($date->month),
            $date->format('y')
        );
    }

    // Generate incoming invoice number
    public static function generateIncomingInvoiceNumber(Carbon $date, string $vendorCode = 'HWN'): string
    {
        // Only look at invoices matching our expected format for this vendor
        $pattern     = "% / {$vendorCode} / " . $date->format('m-Y');
        $lastSameDay = IncomingInvoice::whereDate('inv_received_date', $date)
            ->where('inv_number', 'LIKE', "__{$pattern}")
            ->orderBy('inv_number', 'desc')
            ->first();

        if ($lastSameDay && preg_match('/^(\d+)/', $lastSameDay->inv_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $day        = (float) $date->day;
            $month      = (int) $date->month;
            $n          = ($day / 30.0) * 150.0 + 150.0 * ($month - 1);
            $nextNumber = (int) floor($n);
        }

        $result = sprintf(
            '%04d / %s / %s-%s',
            $nextNumber,
            $vendorCode,
            $date->format('m'),
            $date->format('Y')
        );

        return $result;
    }
}
