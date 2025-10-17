<?php

namespace Database\Seeders;

use App\Models\Tax;
use App\Models\Order;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\IncomingInvoice;
use App\Models\OutgoingInvoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// Add other models if needed, e.g., Tax, User, Level

class ProductionDataSeeder extends Seeder
{
    /**
     * Helper function to clean and convert IDR amount string to float/decimal.
     * e.g., "26.100.000,00" -> 26100000.00
     */
    private function cleanAmount(string $amountString): float
    {
        // Remove thousands separator (dot), replace decimal comma with dot
        $cleaned = str_replace('.', '', $amountString);
        $cleaned = str_replace(',', '.', $cleaned);
        return (float) $cleaned;
    }

    /**
     * Helper function to convert date string "DD-Mon-YY" to "YYYY-MM-DD".
     * Note: Assumes '25' means '2025' for the provided data.
     */
    private function cleanDate(?string $dateString): ?string
    {
        if (is_null($dateString)) {
            return null; // Safely return null if the input is null
        }

        // Add "20" prefix if year is two digits, then format
        if (substr($dateString, -2) === '25') {
            $dateString = substr($dateString, 0, -2) . '2025';
        }
        // Use PHP's DateTime to parse and format the date
        return \DateTime::createFromFormat('d-M-Y', $dateString)->format('Y-m-d');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 0. Disable foreign keys and clear tables to ensure a clean seed ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Order::truncate();
        PurchaseOrder::truncate();
        OutgoingInvoice::truncate();
        IncomingInvoice::truncate();
        Client::truncate();
        Department::truncate();
        Vendor::truncate();
        Tax::truncate();
        // Use DB::statement to truncate the pivot table since no model is available
        DB::statement('TRUNCATE TABLE incoming_invoice_taxes;');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ---------------------------------------------------------------------
        // 1. RAW DATA DEFINITION (Extracted from your tables)
        // ---------------------------------------------------------------------

        $orderListData = [
            // The main orders that correspond to MCN ORDER NO in the incoming/outgoing invoices
            ['ORDER NO' => '001 / A2-AM / I / 25', 'ORDER DATE' => '07-Jan-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 6 Pcs', 'CUSTOMER PO NO' => '4500321052', 'PO DATE' => '02-Jan-25', 'CUR' => 'IDR', 'AMOUNT' => '26.100.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '002 / A2-AM / I / 25', 'ORDER DATE' => '07-Jan-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge - 1 Lot', 'CUSTOMER PO NO' => '4500321056', 'PO DATE' => '02-Jan-25', 'CUR' => 'IDR', 'AMOUNT' => '14.260.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '003 / A2-AM / I / 25', 'ORDER DATE' => '08-Jan-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Desktop HP & Argox Barcode & Epson LQ', 'CUSTOMER PO NO' => '0002.MCN/TBINA-PED/PCD/I/2025', 'PO DATE' => '02-Jan-25', 'CUR' => 'IDR', 'AMOUNT' => '89.171.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '004 / A2-AM / I / 25', 'ORDER DATE' => '23-Jan-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Aruba 6100 24G 4SFP+ Switch JL677A - 3 Unit', 'CUSTOMER PO NO' => '0016.MCN/TBINA-PED/IT/I/2025', 'PO DATE' => '15-Jan-25', 'CUR' => 'IDR', 'AMOUNT' => '194.625.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '005 / C7-AM / II / 25', 'ORDER DATE' => '03-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Maintenance PABX Periode 01/03/2025 - 28/02/2026', 'CUSTOMER PO NO' => '4500330006', 'PO DATE' => '06-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '72.500.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '006 / A2-AM / II / 25', 'ORDER DATE' => '05-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Acrobat Pro ALC - 3 Lic', 'CUSTOMER PO NO' => '4500326381', 'PO DATE' => '23-Jan-25', 'CUR' => 'IDR', 'AMOUNT' => '24.690.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '007 / A2-AM / II / 25', 'ORDER DATE' => '06-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Palo Alto PA-450', 'CUSTOMER PO NO' => '0067.MCN/TBINA-PED/SEAT/II/2025', 'PO DATE' => '03-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '351.595.800,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '008 / A2-AM / II / 25', 'ORDER DATE' => '07-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook Pavillion Plus HP 14-EW0080TU - 2 Unit', 'CUSTOMER PO NO' => '0077.MCN/TBINA-PED/IT/II/2025', 'PO DATE' => '05-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '59.630.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '009 / A2-AM / II / 25', 'ORDER DATE' => '13-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Annual License Microsoft Windows 11 - 11 Lic', 'CUSTOMER PO NO' => '4500329284', 'PO DATE' => '06-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '40.590.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '010 / A2-AM / II / 25', 'ORDER DATE' => '19-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 6 Pcs', 'CUSTOMER PO NO' => '4500331512', 'PO DATE' => '12-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '26.100.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '011 / A2-AM / II / 25', 'ORDER DATE' => '19-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge - 1 Lot', 'CUSTOMER PO NO' => '4500331513', 'PO DATE' => '12-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '20.560.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '012 / A2-AM / II / 25', 'ORDER DATE' => '26-Feb-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook Pavillion Plus HP 14-EW0080TU - 4 Unit', 'CUSTOMER PO NO' => '0102.MCN/TBINA-PED/IT/II/2025', 'PO DATE' => '24-Feb-25', 'CUR' => 'IDR', 'AMOUNT' => '119.260.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '013 / A2-AM / III / 25', 'ORDER DATE' => '11-Mar-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 5 Pcs', 'CUSTOMER PO NO' => '4500336103', 'PO DATE' => '04-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '21.750.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '014 / A2-AM / III / 25', 'ORDER DATE' => '11-Mar-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge - 1 Lot', 'CUSTOMER PO NO' => '4500336104', 'PO DATE' => '04-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '12.270.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '015 / A2-AM / III / 25', 'ORDER DATE' => '17-Mar-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'TP-LINK AC600 USB Adapter Archer T2U Plus - 25 Pcs', 'CUSTOMER PO NO' => '4500338233', 'PO DATE' => '13-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '15.875.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '016 / A2-AM / III / 25', 'ORDER DATE' => '17-Mar-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'V-GEN SSD 1TB - 20 Pcs', 'CUSTOMER PO NO' => '4500338235', 'PO DATE' => '13-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '26.500.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '017 / A2-AM / III / 25', 'ORDER DATE' => '19-Mar-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Memory SODIMM 16GB DDR', 'CUSTOMER PO NO' => '4500339435', 'PO DATE' => '18-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '21.875.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '018 / A2-AM / IV / 25', 'ORDER DATE' => '07-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook Pavillion Plus HP 14-EW0080TU - 8 Unit', 'CUSTOMER PO NO' => '0120.MCN/TBINA-PED/IT/III/2025', 'PO DATE' => '06-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '238.520.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '019 / A2-AM / IV / 25', 'ORDER DATE' => '07-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Palo Alto PA-1410', 'CUSTOMER PO NO' => '0127.MCN/TBINA-PED/IT/III/2025', 'PO DATE' => '12-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '495.700.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '020 / A2-AM / IV / 25', 'ORDER DATE' => '07-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Desktop HP M01 F3016D - 4 Unit', 'CUSTOMER PO NO' => '4500338089', 'PO DATE' => '12-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '52.900.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '021 / A2-AM / IV / 25', 'ORDER DATE' => '07-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'License Win 11 Pro & Office 365 Business - 4 Lic', 'CUSTOMER PO NO' => '4500338090', 'PO DATE' => '12-Mar-25', 'CUR' => 'IDR', 'AMOUNT' => '35.340.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '022 / A2-AM / IV / 25', 'ORDER DATE' => '09-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Installation Palo Alto PA-1410', 'CUSTOMER PO NO' => '0143.MCN/TBINA-PED/IT/IV/2025', 'PO DATE' => '09-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '90.000.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '023 / A2-AM / IV / 25', 'ORDER DATE' => '23-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Datalogic Barcode Scanner Magelan 1500i - 1 Lot (12 Unit)', 'CUSTOMER PO NO' => '0152.MCN/TBINA-PED/IT/IV/2025', 'PO DATE' => '15-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '139.500.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '024 / A2-AM / IV / 25', 'ORDER DATE' => '23-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Printer HP Laserjet Pro M706N - 1 Lot (2 Unit)', 'CUSTOMER PO NO' => '0153.MCN/TBINA-PED/IT/IV/2025', 'PO DATE' => '15-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '54.950.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '025 / A2-AM / IV / 25', 'ORDER DATE' => '23-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Desktop HP M01 F3016D - 6 Unit', 'CUSTOMER PO NO' => '4500346218', 'PO DATE' => '15-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '79.350.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '026 / A2-AM / IV / 25', 'ORDER DATE' => '23-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'License Win 11 Pro & Office 365 Business - 6 Lic', 'CUSTOMER PO NO' => '4500346219', 'PO DATE' => '15-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '53.010.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '027 / A2-AM / IV / 25', 'ORDER DATE' => '28-Apr-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Palo Alto PA-1410', 'CUSTOMER PO NO' => '0164.MCN/TBINA-PED/IT/IV/2025', 'PO DATE' => '21-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '495.700.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '028 / A2-AM / V / 25', 'ORDER DATE' => '05-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 5 Pcs', 'CUSTOMER PO NO' => '4500347892', 'PO DATE' => '24-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '21.750.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '029 / A2-AM / V / 25', 'ORDER DATE' => '05-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge - 1 Lot', 'CUSTOMER PO NO' => '4500347894', 'PO DATE' => '24-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '22.550.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '030 / A2-AM / V / 25', 'ORDER DATE' => '05-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'HPE MSA 2060 10GBASE-T iSCSI SFF Storage', 'CUSTOMER PO NO' => '0177.MCN/TBINA-PED/IT/IV/2025', 'PO DATE' => '29-Apr-25', 'CUR' => 'IDR', 'AMOUNT' => '452.960.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '031 / A2-AM / V / 25', 'ORDER DATE' => '13-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Installation Palo Alto PA-1410', 'CUSTOMER PO NO' => '0190.MCN/TBINA-PED/IT/V/2025', 'PO DATE' => '06-May-25', 'CUR' => 'IDR', 'AMOUNT' => '90.000.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '032 / A2-AM / V / 25', 'ORDER DATE' => '13-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'FortiNAC Control and Application', 'CUSTOMER PO NO' => '0191.MCN/TBINA-PED/IT/V/2025', 'PO DATE' => '06-May-25', 'CUR' => 'IDR', 'AMOUNT' => '492.125.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '033 / A2-AM / V / 25', 'ORDER DATE' => '27-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Printer Epson LQ-310 - 2 Unit', 'CUSTOMER PO NO' => '4500354756', 'PO DATE' => '22-May-25', 'CUR' => 'IDR', 'AMOUNT' => '13.180.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '034 / A2-AM / V / 25', 'ORDER DATE' => '27-May-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Workstation HP Z6 Tower G5 - 2 Unit', 'CUSTOMER PO NO' => '0203.MCN/TBINA-PED/IT/V/2025', 'PO DATE' => '22-May-25', 'CUR' => 'IDR', 'AMOUNT' => '213.300.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '035 / A2-AM / VI / 25', 'ORDER DATE' => '02-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook Pavillion Plus HP 14-EW0080TU - 4 Unit', 'CUSTOMER PO NO' => '0214.MCN/TBINA-PED/IT/V/2025', 'PO DATE' => '28-May-25', 'CUR' => 'IDR', 'AMOUNT' => '119.260.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '036 / A2-AM / VI / 25', 'ORDER DATE' => '10-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook HP 14-EP0019TU - 5 Unit', 'CUSTOMER PO NO' => '4500357725', 'PO DATE' => '02-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '84.370.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '037 / A2-AM / VI / 25', 'ORDER DATE' => '10-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'License Win 11 Pro & Office 365 Business - 5 Lic', 'CUSTOMER PO NO' => '4500357726', 'PO DATE' => '02-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '44.175.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '038 / A2-AM / VI / 25', 'ORDER DATE' => '10-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 5 Pcs', 'CUSTOMER PO NO' => '4500357727', 'PO DATE' => '02-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '21.750.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '039 / A2-AM / VI / 25', 'ORDER DATE' => '10-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge - 1 Lot', 'CUSTOMER PO NO' => '4500357729', 'PO DATE' => '02-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '21.754.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '040 / A2-AM / VI / 25', 'ORDER DATE' => '12-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Annual License Microsoft Windows 11 - 13 Lic', 'CUSTOMER PO NO' => '4500358315', 'PO DATE' => '04-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '47.970.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '041 / A2-AM / VI / 25', 'ORDER DATE' => '16-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'FortiNAC Control and Application', 'CUSTOMER PO NO' => '0256.MCN/TBINA-PED/IT/VI/2025', 'PO DATE' => '12-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '244.700.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '042 / A2-AM / VI / 25', 'ORDER DATE' => '17-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Aruba Access Point IAP-305 - 1 Lot (11 Pcs)', 'CUSTOMER PO NO' => '0263.MCN/TBINA-PED/IT/VI/2025', 'PO DATE' => '13-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '217.800.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '043 / A2-AM / VI / 25', 'ORDER DATE' => '30-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Projector Panasonic PT-LMX460', 'CUSTOMER PO NO' => '0289.MCN/TBINA-PED/IT/VI/2025', 'PO DATE' => '25-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '29.850.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '044 / A2-AM / VI / 25', 'ORDER DATE' => '30-Jun-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Datalogic Barcode Scanner Magelan 1500i - 1 Lot (5 Unit)', 'CUSTOMER PO NO' => '0290.MCN/TBINA-PED/IT/VI/2025', 'PO DATE' => '25-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '58.125.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '045 / A2-AM / VII / 25', 'ORDER DATE' => '03-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook HP 14-EP0019TU - 5 Unit', 'CUSTOMER PO NO' => '4500364857', 'PO DATE' => '30-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '84.370.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '046 / A2-AM / VII / 25', 'ORDER DATE' => '03-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'License Win 11 Pro & Office 365 Business - 5 Lic', 'CUSTOMER PO NO' => '4500364858', 'PO DATE' => '30-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '44.175.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '047 / A2-AM / VII / 25', 'ORDER DATE' => '03-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 7 Pcs', 'CUSTOMER PO NO' => '4500364859', 'PO DATE' => '30-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '30.450.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '048 / A2-AM / VII / 25', 'ORDER DATE' => '03-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge - 1 Lot', 'CUSTOMER PO NO' => '4500364860', 'PO DATE' => '30-Jun-25', 'CUR' => 'IDR', 'AMOUNT' => '21.754.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '049 / A2-AM / VII / 25', 'ORDER DATE' => '10-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'IBM TS4300 3U Tape Library-Base Unit', 'CUSTOMER PO NO' => '0304.MCN/TBINA-PED/IT/VII/2025', 'PO DATE' => '08-Jul-25', 'CUR' => 'IDR', 'AMOUNT' => '106.000.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '050 / A2-AM / VII / 25', 'ORDER DATE' => '10-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Samsung Smart TV 65 Inch - 1 Lot (2 Set)', 'CUSTOMER PO NO' => '0305.MCN/TBINA-PED/IT/VII/2025', 'PO DATE' => '08-Jul-25', 'CUR' => 'IDR', 'AMOUNT' => '30.268.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '051 / A2-AM / VII / 25', 'ORDER DATE' => '21-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'IBM Ultrium LTO 8 Tape Cartridge - 1 Lot (24 Pcs)', 'CUSTOMER PO NO' => '0319.MCN/TBINA-PED/IT/VII/2025', 'PO DATE' => '17-Jul-25', 'CUR' => 'IDR', 'AMOUNT' => '87.168.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '052 / A2-AM / VII / 25', 'ORDER DATE' => '21-Jul-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Printer Epson LQ-2190 - 1 Lot (3 Unit)', 'CUSTOMER PO NO' => '0320.MCN/TBINA-PED/IT/VII/2025', 'PO DATE' => '17-Jul-25', 'CUR' => 'IDR', 'AMOUNT' => '45.630.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '053 / A2-AM / VIII / 25', 'ORDER DATE' => '07-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Annual License Microsoft Windows 11 - 8 Lic', 'CUSTOMER PO NO' => '4500373764', 'PO DATE' => '04-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '29.520.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '054 / A2-AM / VIII / 25', 'ORDER DATE' => '07-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Toner Cartridge HP Laserjet 93A - 6 Pcs', 'CUSTOMER PO NO' => '4500373778', 'PO DATE' => '04-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '26.100.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '055 / A2-AM / VIII / 25', 'ORDER DATE' => '07-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Ribbon Cartridge LQ-2190 & LQ-310 - 1 Lot', 'CUSTOMER PO NO' => '4500373799', 'PO DATE' => '04-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '6.265.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '056 / A2-AM / VIII / 25', 'ORDER DATE' => '12-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Lenovo Thinksystem SR650 V3 Rackmount 2U', 'CUSTOMER PO NO' => '0344.MCN/TBINA-PED/IT/VIII/2025', 'PO DATE' => '07-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '475.000.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '057 / A2-AM / VIII / 25', 'ORDER DATE' => '14-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Desktop HP M01 F3016D - 5 Unit', 'CUSTOMER PO NO' => '4500375375', 'PO DATE' => '07-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '66.125.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '058 / A2-AM / VIII / 25', 'ORDER DATE' => '14-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'License Win 11 Pro & Office 365 Business - 5 Lic', 'CUSTOMER PO NO' => '4500375376', 'PO DATE' => '07-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '44.175.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '059 / A2-AM / VIII / 25', 'ORDER DATE' => '14-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook HP 14-EP0019TU - 4 Unit', 'CUSTOMER PO NO' => '4500375602', 'PO DATE' => '12-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '67.496.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '060 / A2-AM / VIII / 25', 'ORDER DATE' => '14-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'License Win 11 Pro & Office 365 Business - 4 Lic', 'CUSTOMER PO NO' => '4500375603', 'PO DATE' => '12-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '35.340.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '061 / A2-AM / VIII / 25', 'ORDER DATE' => '26-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook Pavillion Plus HP 14-EW0080TU', 'CUSTOMER PO NO' => '0371.MCN/TBINA-PED/IT/VIII/2025', 'PO DATE' => '21-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '29.815.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '062 / A2-AM / VIII / 25', 'ORDER DATE' => '26-Aug-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Notebook HP OmniBook 14-FH0334TU', 'CUSTOMER PO NO' => '0372.MCN/TBINA-PED/IT/VIII/2025', 'PO DATE' => '21-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '39.680.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '063 / A2-AM / IX / 25', 'ORDER DATE' => '01-Sep-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Palo Alto PA-450', 'CUSTOMER PO NO' => '0382.MCN/TBINA-PED/IT/VIII/2025', 'PO DATE' => '27-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '351.595.800,00', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['ORDER NO' => '064 / A2-AM / IX / 25', 'ORDER DATE' => '01-Sep-25', 'CUSTOMER NAME' => 'Toyota Boshoku Indonesia', 'PROJECT NAME' => 'Aruba Access Point AP-505 - 1 Lot (12 Unit)', 'CUSTOMER PO NO' => '0383.MCN/TBINA-PED/IT/VIII/2025', 'PO DATE' => '27-Aug-25', 'CUR' => 'IDR', 'AMOUNT' => '259.800.000,00', 'REMARKS' => null, 'D-CODE' =>'260'],
        ];

        $outgoingInvoiceData = [
            // Outgoing invoice list structure (full data assumed from $orderListData)
            ['INVOICE NO' => '001 / INV-F / I / 25', 'INV DATE' => '07-Jan-25', 'DUE DATE' => '21-Jan-25', 'FP S/N' => '04002500000107120', 'MCN ORDER NO' => '001 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '26.100.000,00', 'INC DATE' => '30-Jan-25', 'REMARKS' => null],
            ['INVOICE NO' => '002 / INV-F / I / 25', 'INV DATE' => '07-Jan-25', 'DUE DATE' => '21-Jan-25', 'FP S/N' => '04002500000107121', 'MCN ORDER NO' => '002 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '14.260.000,00', 'INC DATE' => '30-Jan-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '003 / INV-F / I / 25', 'INV DATE' => '08-Jan-25', 'DUE DATE' => '22-Jan-25', 'FP S/N' => '04002500000207193', 'MCN ORDER NO' => '003 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '89.171.000,00', 'INC DATE' => '30-Jan-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '004 / INV-F / I / 25', 'INV DATE' => '23-Jan-25', 'DUE DATE' => '06-Feb-25', 'FP S/N' => '04002500006390191', 'MCN ORDER NO' => '004 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '194.625.000,00', 'INC DATE' => '17-Feb-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '005 / INV-F / II / 25', 'INV DATE' => '05-Feb-25', 'DUE DATE' => '19-Feb-25', 'FP S/N' => '04002500018467524', 'MCN ORDER NO' => '006 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '24.690.000,00', 'INC DATE' => '27-Feb-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '006 / INV-F / II / 25', 'INV DATE' => '06-Feb-25', 'DUE DATE' => '20-Feb-25', 'FP S/N' => '04002500020150824', 'MCN ORDER NO' => '007 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '351.595.800,00', 'INC DATE' => '27-Feb-25', 'REMARKS' => '4%'],
            ['INVOICE NO' => '007 / INV-F / II / 25', 'INV DATE' => '07-Feb-25', 'DUE DATE' => '21-Feb-25', 'FP S/N' => '04002500021418461', 'MCN ORDER NO' => '008 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '59.630.000,00', 'INC DATE' => '27-Feb-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '008 / INV-F / II / 25', 'INV DATE' => '13-Feb-25', 'DUE DATE' => '27-Feb-25', 'FP S/N' => '04002500030373012', 'MCN ORDER NO' => '009 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '40.590.000,00', 'INC DATE' => '27-Feb-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '009 / INV-F / II / 25', 'INV DATE' => '13-Feb-25', 'DUE DATE' => '27-Feb-25', 'FP S/N' => '04002500030379812', 'MCN ORDER NO' => '005 / C7-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '72.500.000,00', 'INC DATE' => '27-Feb-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '010 / INV-F / II / 25', 'INV DATE' => '19-Feb-25', 'DUE DATE' => '05-Mar-25', 'FP S/N' => '04002500041771340', 'MCN ORDER NO' => '010 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '26.100.000,00', 'INC DATE' => '17-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '011 / INV-F / II / 25', 'INV DATE' => '19-Feb-25', 'DUE DATE' => '05-Mar-25', 'FP S/N' => '04002500042068660', 'MCN ORDER NO' => '011 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.560.000,00', 'INC DATE' => '17-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '012 / INV-F / II / 25', 'INV DATE' => '26-Feb-25', 'DUE DATE' => '12-Mar-25', 'FP S/N' => '04002500047824258', 'MCN ORDER NO' => '012 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '119.260.000,00', 'INC DATE' => '17-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '013 / INV-F / III / 25', 'INV DATE' => '11-Mar-25', 'DUE DATE' => '25-Mar-25', 'FP S/N' => '04002500063473964', 'MCN ORDER NO' => '013 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.750.000,00', 'INC DATE' => '26-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '014 / INV-F / III / 25', 'INV DATE' => '11-Mar-25', 'DUE DATE' => '25-Mar-25', 'FP S/N' => '04002500063473965', 'MCN ORDER NO' => '014 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '12.270.000,00', 'INC DATE' => '26-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '015 / INV-F / III / 25', 'INV DATE' => '17-Mar-25', 'DUE DATE' => '31-Mar-25', 'FP S/N' => '04002500075581322', 'MCN ORDER NO' => '015 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '15.875.000,00', 'INC DATE' => '26-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '016 / INV-F / III / 25', 'INV DATE' => '17-Mar-25', 'DUE DATE' => '31-Mar-25', 'FP S/N' => '04002500075581291', 'MCN ORDER NO' => '016 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '26.500.000,00', 'INC DATE' => '26-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '017 / INV-F / III / 25', 'INV DATE' => '19-Mar-25', 'DUE DATE' => '02-Apr-25', 'FP S/N' => '04002500077808265', 'MCN ORDER NO' => '017 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.875.000,00', 'INC DATE' => '26-Mar-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '018 / INV-F / IV / 25', 'INV DATE' => '07-Apr-25', 'DUE DATE' => '21-Apr-25', 'FP S/N' => '04002500089284114', 'MCN ORDER NO' => '018 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '238.520.000,00', 'INC DATE' => '29-Apr-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '019 / INV-F / IV / 25', 'INV DATE' => '07-Apr-25', 'DUE DATE' => '21-Apr-25', 'FP S/N' => '04002500089284113', 'MCN ORDER NO' => '019 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '495.700.000,00', 'INC DATE' => '29-Apr-25', 'REMARKS' => '4%'],
            ['INVOICE NO' => '020 / INV-F / IV / 25', 'INV DATE' => '07-Apr-25', 'DUE DATE' => '21-Apr-25', 'FP S/N' => '04002500089284110', 'MCN ORDER NO' => '020 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '52.900.000,00', 'INC DATE' => '29-Apr-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '021 / INV-F / IV / 25', 'INV DATE' => '07-Apr-25', 'DUE DATE' => '21-Apr-25', 'FP S/N' => '04002500089284107', 'MCN ORDER NO' => '021 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '35.340.000,00', 'INC DATE' => '29-Apr-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '022 / INV-F / IV / 25', 'INV DATE' => '10-Apr-25', 'DUE DATE' => '24-Apr-25', 'FP S/N' => '04002500092920296', 'MCN ORDER NO' => '022 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '90.000.000,00', 'INC DATE' => '29-Apr-25', 'REMARKS' => '4%, PPH'],
            ['INVOICE NO' => '023 / INV-F / V / 25', 'INV DATE' => '02-May-25', 'DUE DATE' => '16-May-25', 'FP S/N' => '04002500117643917', 'MCN ORDER NO' => '023 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '139.500.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '024 / INV-F / V / 25', 'INV DATE' => '02-May-25', 'DUE DATE' => '16-May-25', 'FP S/N' => '04002500117643854', 'MCN ORDER NO' => '024 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '54.950.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '025 / INV-F / V / 25', 'INV DATE' => '02-May-25', 'DUE DATE' => '16-May-25', 'FP S/N' => '04002500117643889', 'MCN ORDER NO' => '025 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '79.350.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '026 / INV-F / V / 25', 'INV DATE' => '02-May-25', 'DUE DATE' => '16-May-25', 'FP S/N' => '04002500117643916', 'MCN ORDER NO' => '026 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '53.010.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '027 / INV-F / V / 25', 'INV DATE' => '02-May-25', 'DUE DATE' => '16-May-25', 'FP S/N' => '04002500117643843', 'MCN ORDER NO' => '027 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '495.700.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => '4%'],
            ['INVOICE NO' => '028 / INV-F / V / 25', 'INV DATE' => '05-May-25', 'DUE DATE' => '19-May-25', 'FP S/N' => '04002500120309309', 'MCN ORDER NO' => '028 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.750.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '029 / INV-F / V / 25', 'INV DATE' => '05-May-25', 'DUE DATE' => '19-May-25', 'FP S/N' => '04002500120309307', 'MCN ORDER NO' => '029 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '22.550.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '030 / INV-F / V / 25', 'INV DATE' => '05-May-25', 'DUE DATE' => '19-May-25', 'FP S/N' => '04002500120309306', 'MCN ORDER NO' => '030 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '452.960.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => '4%'],
            ['INVOICE NO' => '031 / INV-F / V / 25', 'INV DATE' => '13-May-25', 'DUE DATE' => '27-May-25', 'FP S/N' => '04002500131970700', 'MCN ORDER NO' => '031 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '90.000.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => '4%, PPH'],
            ['INVOICE NO' => '032 / INV-F / V / 25', 'INV DATE' => '13-May-25', 'DUE DATE' => '27-May-25', 'FP S/N' => '04002500131970666', 'MCN ORDER NO' => '032 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '492.125.000,00', 'INC DATE' => '28-May-25', 'REMARKS' => '4%, PPH'],
            ['INVOICE NO' => '033 / INV-F / V / 25', 'INV DATE' => '27-May-25', 'DUE DATE' => '10-Jun-25', 'FP S/N' => '04002500148890550', 'MCN ORDER NO' => '033 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '13.180.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '034 / INV-F / V / 25', 'INV DATE' => '27-May-25', 'DUE DATE' => '10-Jun-25', 'FP S/N' => '04002500148890685', 'MCN ORDER NO' => '034 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '213.300.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => '4%'],
            ['INVOICE NO' => '035 / INV-F / VI / 25', 'INV DATE' => '02-Jun-25', 'DUE DATE' => '16-Jun-25', 'FP S/N' => '04002500153028935', 'MCN ORDER NO' => '035 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '119.260.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '036 / INV-F / VI / 25', 'INV DATE' => '11-Jun-25', 'DUE DATE' => '25-Jun-25', 'FP S/N' => '04002500163692869', 'MCN ORDER NO' => '036 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '84.370.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '037 / INV-F / VI / 25', 'INV DATE' => '11-Jun-25', 'DUE DATE' => '25-Jun-25', 'FP S/N' => '04002500163692849', 'MCN ORDER NO' => '037 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '44.175.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '038 / INV-F / VI / 25', 'INV DATE' => '11-Jun-25', 'DUE DATE' => '25-Jun-25', 'FP S/N' => '04002500163692805', 'MCN ORDER NO' => '038 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.750.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '039 / INV-F / VI / 25', 'INV DATE' => '11-Jun-25', 'DUE DATE' => '25-Jun-25', 'FP S/N' => '04002500163692841', 'MCN ORDER NO' => '039 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.754.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '040 / INV-F / VI / 25', 'INV DATE' => '12-Jun-25', 'DUE DATE' => '26-Jun-25', 'FP S/N' => '04002500166709919', 'MCN ORDER NO' => '040 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '47.970.000,00', 'INC DATE' => '26-Jun-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '041 / INV-F / VI / 25', 'INV DATE' => '16-Jun-25', 'DUE DATE' => '30-Jun-25', 'FP S/N' => '04002500173892302', 'MCN ORDER NO' => '041 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '244.700.000,00', 'INC DATE' => '15-Jul-25', 'REMARKS' => '4%, PPH'],
            ['INVOICE NO' => '042 / INV-F / VI / 25', 'INV DATE' => '17-Jun-25', 'DUE DATE' => '01-Jul-25', 'FP S/N' => '04002500175820183', 'MCN ORDER NO' => '042 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '217.800.000,00', 'INC DATE' => '15-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '043 / INV-F / VII / 25', 'INV DATE' => '01-Jul-25', 'DUE DATE' => '15-Jul-25', 'FP S/N' => '04002500188117255', 'MCN ORDER NO' => '043 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '29.850.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '044 / INV-F / VII / 25', 'INV DATE' => '01-Jul-25', 'DUE DATE' => '15-Jul-25', 'FP S/N' => '04002500188117236', 'MCN ORDER NO' => '044 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '58.125.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '045 / INV-F / VII / 25', 'INV DATE' => '03-Jul-25', 'DUE DATE' => '17-Jul-25', 'FP S/N' => '04002500192069308', 'MCN ORDER NO' => '045 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '84.370.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '046 / INV-F / VII / 25', 'INV DATE' => '03-Jul-25', 'DUE DATE' => '17-Jul-25', 'FP S/N' => '04002500192069309', 'MCN ORDER NO' => '046 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '44.175.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '047 / INV-F / VII / 25', 'INV DATE' => '03-Jul-25', 'DUE DATE' => '17-Jul-25', 'FP S/N' => '04002500192069310', 'MCN ORDER NO' => '047 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '30.450.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '048 / INV-F / VII / 25', 'INV DATE' => '03-Jul-25', 'DUE DATE' => '17-Jul-25', 'FP S/N' => '04002500192069311', 'MCN ORDER NO' => '048 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.754.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '049 / INV-F / VII / 25', 'INV DATE' => '10-Jul-25', 'DUE DATE' => '24-Jul-25', 'FP S/N' => '04002500201640956', 'MCN ORDER NO' => '049 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '106.000.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '050 / INV-F / VII / 25', 'INV DATE' => '10-Jul-25', 'DUE DATE' => '24-Jul-25', 'FP S/N' => '04002500201640952', 'MCN ORDER NO' => '050 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '30.268.000,00', 'INC DATE' => '30-Jul-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '051 / INV-F / VII / 25', 'INV DATE' => '21-Jul-25', 'DUE DATE' => '04-Aug-25', 'FP S/N' => '04002500218564341', 'MCN ORDER NO' => '051 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '87.168.000,00', 'INC DATE' => '15-Aug-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '052 / INV-F / VII / 25', 'INV DATE' => '21-Jul-25', 'DUE DATE' => '04-Aug-25', 'FP S/N' => '04002500218564340', 'MCN ORDER NO' => '052 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '45.630.000,00', 'INC DATE' => '15-Aug-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '053 / INV-F / VIII / 25', 'INV DATE' => '07-Aug-25', 'DUE DATE' => '21-Aug-25', 'FP S/N' => '04002500235721802', 'MCN ORDER NO' => '053 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '29.520.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '054 / INV-F / VIII / 25', 'INV DATE' => '07-Aug-25', 'DUE DATE' => '21-Aug-25', 'FP S/N' => '04002500235721803', 'MCN ORDER NO' => '054 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '26.100.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '055 / INV-F / VIII / 25', 'INV DATE' => '07-Aug-25', 'DUE DATE' => '21-Aug-25', 'FP S/N' => '04002500235721804', 'MCN ORDER NO' => '055 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '6.265.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '056 / INV-F / VIII / 25', 'INV DATE' => '12-Aug-25', 'DUE DATE' => '26-Aug-25', 'FP S/N' => '04002500242902517', 'MCN ORDER NO' => '056 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '475.000.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => '4%'],
            ['INVOICE NO' => '057 / INV-F / VIII / 25', 'INV DATE' => '14-Aug-25', 'DUE DATE' => '28-Aug-25', 'FP S/N' => '04002500248364329', 'MCN ORDER NO' => '057 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '66.125.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '058 / INV-F / VIII / 25', 'INV DATE' => '14-Aug-25', 'DUE DATE' => '28-Aug-25', 'FP S/N' => '04002500248364259', 'MCN ORDER NO' => '058 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '44.175.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '059 / INV-F / VIII / 25', 'INV DATE' => '14-Aug-25', 'DUE DATE' => '28-Aug-25', 'FP S/N' => '04002500248364302', 'MCN ORDER NO' => '059 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '67.496.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '060 / INV-F / VIII / 25', 'INV DATE' => '14-Aug-25', 'DUE DATE' => '28-Aug-25', 'FP S/N' => '04002500248364252', 'MCN ORDER NO' => '060 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '35.340.000,00', 'INC DATE' => '28-Aug-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '061 / INV-F / VIII / 25', 'INV DATE' => '26-Aug-25', 'DUE DATE' => '09-Sep-25', 'FP S/N' => '04002500262872997', 'MCN ORDER NO' => '061 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '29.815.000,00', 'INC DATE' => null, 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '062 / INV-F / VIII / 25', 'INV DATE' => '26-Aug-25', 'DUE DATE' => '09-Sep-25', 'FP S/N' => '04002500262872996', 'MCN ORDER NO' => '062 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '39.680.000,00', 'INC DATE' => null, 'REMARKS' => null, 'D-CODE' =>'260'],
            ['INVOICE NO' => '063 / INV-F / IX / 25', 'INV DATE' => '01-Sep-25', 'DUE DATE' => '15-Sep-25', 'FP S/N' => '04002500268314679', 'MCN ORDER NO' => '063 / A2-AM / IX / 25', 'CUR' => 'IDR', 'AMOUNT' => '351.595.800,00', 'INC DATE' => null, 'REMARKS' => '4%'],
            ['INVOICE NO' => '064 / INV-F / IX / 25', 'INV DATE' => '01-Sep-25', 'DUE DATE' => '15-Sep-25', 'FP S/N' => '04002500268314676', 'MCN ORDER NO' => '064 / A2-AM / IX / 25', 'CUR' => 'IDR', 'AMOUNT' => '259.800.000,00', 'INC DATE' => null, 'REMARKS' => null, 'D-CODE' =>'260'],
        ];

        $incomingInvoiceData = [
            // Incoming invoice list (fully converted from your SQL data)
            ['INVOICE NO' => '0035 / HWN / 01-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Jan-25', 'INV DATE' => '07-Jan-25', 'FP S/N' => null, 'MCN ORDER NO' => '001 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '24.273.000,00', 'PMT DATE' => '30-Jan-25', 'REMARKS' => null],
            ['INVOICE NO' => '0036 / HWN / 01-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Jan-25', 'INV DATE' => '07-Jan-25', 'FP S/N' => null, 'MCN ORDER NO' => '002 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '13.261.800,00', 'PMT DATE' => '30-Jan-25', 'REMARKS' => null],
            ['INVOICE NO' => '0040 / HWN / 01-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '08-Jan-25', 'INV DATE' => '08-Jan-25', 'FP S/N' => null, 'MCN ORDER NO' => '003 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '82.929.030,00', 'PMT DATE' => '30-Jan-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1037268', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '14-Jan-25', 'INV DATE' => '13-Jan-25', 'FP S/N' => '04002500001925150', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '24-Jan-25', 'REMARKS' => null],
            ['INVOICE NO' => '0115 / HWN / 01-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '23-Jan-25', 'INV DATE' => '23-Jan-25', 'FP S/N' => null, 'MCN ORDER NO' => '004 / A2-AM / I / 25', 'CUR' => 'IDR', 'AMOUNT' => '181.001.250,00', 'PMT DATE' => '17-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => '0175 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '05-Feb-25', 'INV DATE' => '05-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '006 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '22.467.900,00', 'PMT DATE' => '27-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => '0180 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '06-Feb-25', 'INV DATE' => '06-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '007 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '337.531.968,00', 'PMT DATE' => '27-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => '0185 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Feb-25', 'INV DATE' => '07-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '008 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '55.455.900,00', 'PMT DATE' => '27-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => '0215 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '13-Feb-25', 'INV DATE' => '13-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '009 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '36.936.900,00', 'PMT DATE' => '27-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => '0216 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '13-Feb-25', 'INV DATE' => '13-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '005 / C7-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '65.975.000,00', 'PMT DATE' => '27-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1038705', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '17-Feb-25', 'INV DATE' => '13-Feb-25', 'FP S/N' => '04002500030631699', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '25-Feb-25', 'REMARKS' => null],
            ['INVOICE NO' => '0245 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '19-Feb-25', 'INV DATE' => '19-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '010 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '24.273.000,00', 'PMT DATE' => '17-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0246 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '19-Feb-25', 'INV DATE' => '19-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '011 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '19.120.800,00', 'PMT DATE' => '17-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0280 / HWN / 02-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '26-Feb-25', 'INV DATE' => '26-Feb-25', 'FP S/N' => null, 'MCN ORDER NO' => '012 / A2-AM / II / 25', 'CUR' => 'IDR', 'AMOUNT' => '110.911.800,00', 'PMT DATE' => '17-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '11602500102', 'SUPPLIER' => 'Sucofindo', 'D-CODE' => '100', 'INV RCVD DATE' => '01-Mar-25', 'INV DATE' => '24-Feb-25', 'FP S/N' => '040.003-25.16101091', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '13.502.538,00', 'PMT DATE' => '21-Mar-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => 'IN-IA-PST-1040091', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '07-Mar-25', 'INV DATE' => '05-Mar-25', 'FP S/N' => '04002500055703845', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '21-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0355 / HWN / 03-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '11-Mar-25', 'INV DATE' => '11-Mar-25', 'FP S/N' => null, 'MCN ORDER NO' => '013 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.227.500,00', 'PMT DATE' => '27-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0356 / HWN / 03-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '11-Mar-25', 'INV DATE' => '11-Mar-25', 'FP S/N' => null, 'MCN ORDER NO' => '014 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '11.411.100,00', 'PMT DATE' => '27-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0385 / HWN / 03-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '17-Mar-25', 'INV DATE' => '17-Mar-25', 'FP S/N' => null, 'MCN ORDER NO' => '015 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '14.763.750,00', 'PMT DATE' => '27-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0386 / HWN / 03-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '17-Mar-25', 'INV DATE' => '17-Mar-25', 'FP S/N' => null, 'MCN ORDER NO' => '016 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '24.645.000,00', 'PMT DATE' => '27-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0395 / HWN / 03-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '19-Mar-25', 'INV DATE' => '19-Mar-25', 'FP S/N' => null, 'MCN ORDER NO' => '017 / A2-AM / III / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.343.750,00', 'PMT DATE' => '27-Mar-25', 'REMARKS' => null],
            ['INVOICE NO' => '0485 / HWN / 04-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Apr-25', 'INV DATE' => '07-Apr-25', 'FP S/N' => null, 'MCN ORDER NO' => '018 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '224.208.800,00', 'PMT DATE' => '29-Apr-25', 'REMARKS' => null],
            ['INVOICE NO' => '0486 / HWN / 04-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Apr-25', 'INV DATE' => '07-Apr-25', 'FP S/N' => null, 'MCN ORDER NO' => '019 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '475.872.000,00', 'PMT DATE' => '29-Apr-25', 'REMARKS' => null],
            ['INVOICE NO' => '0487 / HWN / 04-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Apr-25', 'INV DATE' => '07-Apr-25', 'FP S/N' => null, 'MCN ORDER NO' => '020 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '49.726.000,00', 'PMT DATE' => '29-Apr-25', 'REMARKS' => null],
            ['INVOICE NO' => '0488 / HWN / 04-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Apr-25', 'INV DATE' => '07-Apr-25', 'FP S/N' => null, 'MCN ORDER NO' => '021 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '32.512.800,00', 'PMT DATE' => '29-Apr-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1043512', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '09-Apr-25', 'INV DATE' => '07-Apr-25', 'FP S/N' => '04002500090051789', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '24-Apr-25', 'REMARKS' => null],
            ['INVOICE NO' => '0500 / HWN / 04-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '10-Apr-25', 'INV DATE' => '10-Apr-25', 'FP S/N' => null, 'MCN ORDER NO' => '022 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '84.600.000,00', 'PMT DATE' => '29-Apr-25', 'REMARKS' => null],
            ['INVOICE NO' => '0610 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '02-May-25', 'INV DATE' => '02-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '023 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '131.130.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0611 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '02-May-25', 'INV DATE' => '02-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '024 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '51.653.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0612 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '02-May-25', 'INV DATE' => '02-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '025 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '74.589.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0613 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '02-May-25', 'INV DATE' => '02-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '026 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '48.769.200,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0614 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '02-May-25', 'INV DATE' => '02-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '027 / A2-AM / IV / 25', 'CUR' => 'IDR', 'AMOUNT' => '475.872.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0625 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '05-May-25', 'INV DATE' => '05-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '028 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.445.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0626 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '05-May-25', 'INV DATE' => '05-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '029 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '21.197.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0627 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '05-May-25', 'INV DATE' => '05-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '030 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '434.841.600,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '2322044', 'SUPPLIER' => 'Exabytes Network Indonesia', 'D-CODE' => '100', 'INV RCVD DATE' => '05-May-25', 'INV DATE' => '04-Jun-25', 'FP S/N' => '04002500163048978', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '350.000,00', 'PMT DATE' => '04-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1044600', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '08-May-25', 'INV DATE' => '06-May-25', 'FP S/N' => '04002500121967588', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '23-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0665 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '13-May-25', 'INV DATE' => '13-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '031 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '84.600.000,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0666 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '13-May-25', 'INV DATE' => '13-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '032 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '462.597.500,00', 'PMT DATE' => '28-May-25', 'REMARKS' => null],
            ['INVOICE NO' => '0735 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '27-May-25', 'INV DATE' => '27-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '033 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '12.389.200,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0736 / HWN / 05-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '27-May-25', 'INV DATE' => '27-May-25', 'FP S/N' => null, 'MCN ORDER NO' => '034 / A2-AM / V / 25', 'CUR' => 'IDR', 'AMOUNT' => '204.768.000,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0760 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '02-Jun-25', 'INV DATE' => '02-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '035 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '112.104.400,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1045631', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '09-Jun-25', 'INV DATE' => '05-Jun-25', 'FP S/N' => '04002500157537099', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '5007/INV/LJA/VI/2025', 'SUPPLIER' => 'Leri Jaya Abadi', 'D-CODE' => '100', 'INV RCVD DATE' => '10-Jun-25', 'INV DATE' => '10-Jun-25', 'FP S/N' => '04002500161628806', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '7.875.000,00', 'PMT DATE' => '10-Jun-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '0805 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '11-Jun-25', 'INV DATE' => '11-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '036 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '79.307.800,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0806 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '11-Jun-25', 'INV DATE' => '11-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '037 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '40.641.000,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0807 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '11-Jun-25', 'INV DATE' => '11-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '038 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.445.000,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0808 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '11-Jun-25', 'INV DATE' => '11-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '039 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.448.760,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0810 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '12-Jun-25', 'INV DATE' => '12-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '040 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '44.132.400,00', 'PMT DATE' => '26-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0830 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '16-Jun-25', 'INV DATE' => '16-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '041 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '230.018.000,00', 'PMT DATE' => '15-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0835 / HWN / 06-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '17-Jun-25', 'INV DATE' => '17-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '042 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '204.732.000,00', 'PMT DATE' => '15-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '11602500354', 'SUPPLIER' => 'Sucofindo', 'D-CODE' => '100', 'INV RCVD DATE' => '24-Jun-25', 'INV DATE' => '16-Jun-25', 'FP S/N' => '040.004-25.86026399', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '13.502.538,00', 'PMT DATE' => '03-Jul-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '001-WEB / VI / 2025', 'SUPPLIER' => 'Athara.my.id', 'D-CODE' => '100', 'INV RCVD DATE' => '20-Jun-25', 'INV DATE' => '20-Jun-25', 'FP S/N' => null, 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '1.000.000,00', 'PMT DATE' => '25-Jun-25', 'REMARKS' => null],
            ['INVOICE NO' => '0905 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '01-Jul-25', 'INV DATE' => '01-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '043 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '28.059.000,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0906 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '01-Jul-25', 'INV DATE' => '01-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '044 / A2-AM / VI / 25', 'CUR' => 'IDR', 'AMOUNT' => '54.637.500,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0915 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '03-Jul-25', 'INV DATE' => '03-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '045 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '79.307.800,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0916 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '03-Jul-25', 'INV DATE' => '03-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '046 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '40.641.000,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0917 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '03-Jul-25', 'INV DATE' => '03-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '047 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '28.623.000,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0918 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '03-Jul-25', 'INV DATE' => '03-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '048 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '20.448.760,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0950 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '10-Jul-25', 'INV DATE' => '10-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '049 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '99.640.000,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '0951 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '10-Jul-25', 'INV DATE' => '10-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '050 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '28.451.920,00', 'PMT DATE' => '30-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1049854', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '10-Jul-25', 'INV DATE' => '07-Jul-25', 'FP S/N' => '04002500196782334', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '25-Jul-25', 'REMARKS' => null],
            ['INVOICE NO' => '2344017', 'SUPPLIER' => 'Exabytes Network Indonesia', 'D-CODE' => '100', 'INV RCVD DATE' => '18-Jul-25', 'INV DATE' => '06-Aug-25', 'FP S/N' => '04002500239548376', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '319.000,00', 'PMT DATE' => '06-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1005 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '21-Jul-25', 'INV DATE' => '21-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '051 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '81.937.920,00', 'PMT DATE' => '15-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1006 / HWN / 07-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '21-Jul-25', 'INV DATE' => '21-Jul-25', 'FP S/N' => null, 'MCN ORDER NO' => '052 / A2-AM / VII / 25', 'CUR' => 'IDR', 'AMOUNT' => '42.892.200,00', 'PMT DATE' => '15-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1085 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Aug-25', 'INV DATE' => '07-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '053 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '27.158.400,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1086 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Aug-25', 'INV DATE' => '07-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '054 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '24.534.000,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1087 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '07-Aug-25', 'INV DATE' => '07-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '055 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '5.889.100,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => 'IN-IA-PST-1051948', 'SUPPLIER' => 'Putraduta Buanasentosa', 'D-CODE' => '310', 'INV RCVD DATE' => '08-Aug-25', 'INV DATE' => '06-Aug-25', 'FP S/N' => '04002500236155779', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '189.000,00', 'PMT DATE' => '25-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1110 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '12-Aug-25', 'INV DATE' => '12-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '056 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '456.000.000,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1120 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '14-Aug-25', 'INV DATE' => '12-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '057 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '62.157.500,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1121 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '14-Aug-25', 'INV DATE' => '12-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '058 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '40.641.000,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1122 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '14-Aug-25', 'INV DATE' => '12-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '059 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '63.446.240,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1123 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '14-Aug-25', 'INV DATE' => '12-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '060 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '32.512.800,00', 'PMT DATE' => '28-Aug-25', 'REMARKS' => null],
            ['INVOICE NO' => '1180 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '26-Aug-25', 'INV DATE' => '26-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '061 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '28.026.100,00', 'PMT DATE' => null, 'REMARKS' => null],
            ['INVOICE NO' => '1181 / HWN / 08-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '26-Aug-25', 'INV DATE' => '26-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '062 / A2-AM / VIII / 25', 'CUR' => 'IDR', 'AMOUNT' => '37.299.200,00', 'PMT DATE' => null, 'REMARKS' => null],
            ['INVOICE NO' => '005-APP / VIII / 2025', 'SUPPLIER' => 'Athara.my.id', 'D-CODE' => '100', 'INV RCVD DATE' => '26-Aug-25', 'INV DATE' => '26-Aug-25', 'FP S/N' => null, 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '1.200.000,00', 'PMT DATE' => '01-Sep-25', 'REMARKS' => null],
            ['INVOICE NO' => '5136/INV/LJA/VIII/2025', 'SUPPLIER' => 'Leri Jaya Abadi', 'D-CODE' => '100', 'INV RCVD DATE' => '29-Aug-25', 'INV DATE' => '28-Aug-25', 'FP S/N' => '04002500265532716', 'MCN ORDER NO' => '100', 'CUR' => 'IDR', 'AMOUNT' => '7.875.000,00', 'PMT DATE' => '01-Sep-25', 'REMARKS' => 'PPH'],
            ['INVOICE NO' => '1205 / HWN / 09-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '01-Sep-25', 'INV DATE' => '01-Sep-25', 'FP S/N' => null, 'MCN ORDER NO' => '063 / A2-AM / IX / 25', 'CUR' => 'IDR', 'AMOUNT' => '337.531.968,00', 'PMT DATE' => null, 'REMARKS' => null],
            ['INVOICE NO' => '1206 / HWN / 09-2025', 'SUPPLIER' => 'HW Networks & Supplies', 'D-CODE' => '260', 'INV RCVD DATE' => '01-Sep-25', 'INV DATE' => '01-Sep-25', 'FP S/N' => null, 'MCN ORDER NO' => '064 / A2-AM / IX / 25', 'CUR' => 'IDR', 'AMOUNT' => '244.212.000,00', 'PMT DATE' => null, 'REMARKS' => null],
        ];

        // ---------------------------------------------------------------------
        // 2. SEED LOOKUP TABLES (Mapping names/codes to IDs)
        // ---------------------------------------------------------------------

        // Departments (D-CODE)
        $departmentsMap = [];
        $dCodes = array_unique(array_merge(array_column($orderListData, 'D-CODE'), array_column($incomingInvoiceData, 'D-CODE')));
        foreach ($dCodes as $code) {
            $department = Department::firstOrCreate(['department_code' => $code], ['department_name' => 'Department ' . $code]);
            $departmentsMap[$code] = $department->id;
        }

        // Clients (Customer Name)
        $clientsMap = [];
        $clientName = $orderListData[0]['CUSTOMER NAME']; // All orders are for 'Toyota Boshoku Indonesia'
        $client = Client::firstOrCreate(['client_name' => $clientName]);
        $clientsMap[$clientName] = $client->id;

        // Vendors (Supplier / Subcon)
        $vendorsMap = [];
        $uniqueVendorNames = array_unique(array_column($incomingInvoiceData, 'SUPPLIER'));
        foreach ($uniqueVendorNames as $name) {
            $vendor = Vendor::firstOrCreate(['vendor_name' => $name]);
            $vendorsMap[$name] = $vendor->id;
        }

        //PPh Tax
        $pphTax = Tax::firstOrCreate(
            ['tax_name' => 'PPh'],
            ['tax_percentage' => 2.00] // Use the actual PPh percentage if known (e.g., 2.00 for PPh 23)
        );
        $pphTaxId = $pphTax->id;

        // ---------------------------------------------------------------------
        // 3. SEED TRANSACTIONAL DATA
        // ---------------------------------------------------------------------

        $ordersMap = []; // To store Order objects for quick lookup

        // A. Seed ORDERS and Purchase Orders
        foreach ($orderListData as $data) {
            // 3.1. Create Purchase Order (Customer PO)
            $po = PurchaseOrder::firstOrCreate(
                ['po_number' => $data['CUSTOMER PO NO']],
                ['po_date' => $this->cleanDate($data['PO DATE'])]
            );

            // 3.2. Create Order
            $order = Order::create([
                'ord_number' => $data['ORDER NO'],
                'ord_date' => $this->cleanDate($data['ORDER DATE']),
                'project_name' => $data['PROJECT NAME'],
                'cur' => $data['CUR'],
                'amount' => $this->cleanAmount($data['AMOUNT']),
                'remark' => $data['REMARKS'],
                'client_id' => $clientsMap[$data['CUSTOMER NAME']],
                'purchase_order_id' => $po->id,
                'department_id' => $departmentsMap[$data['D-CODE']],
                'created_at' => $this->cleanDate($data['ORDER DATE']),
                'updated_at' => $this->cleanDate($data['ORDER DATE']),
            ]);
            $ordersMap[$data['ORDER NO']] = $order;
        }

        // B. Seed OUTGOING INVOICES
        foreach ($outgoingInvoiceData as $data) {
            $order = $ordersMap[$data['MCN ORDER NO']];

            OutgoingInvoice::create([
                'inv_number' => $data['INVOICE NO'],
                'inv_date' => $this->cleanDate($data['INV DATE']),
                'due_date' => $this->cleanDate($data['DUE DATE']),
                'fp_number' => $data['FP S/N'],
                'income_date' => $this->cleanDate($data['INC DATE']),
                'cur' => $data['CUR'],
                'amount' => $this->cleanAmount($data['AMOUNT']),
                'remark' => $data['REMARKS'],
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'department_id' => $order->department_id,
                'created_at' => $this->cleanDate($data['INV DATE']),
                'updated_at' => $this->cleanDate($data['INV DATE']),
            ]);

            // Populate the remarks map for the incoming invoice logic
            $outgoingInvoiceRemarksMap[$data['MCN ORDER NO']] = $data['REMARKS'];
        }

        // C. Seed INCOMING INVOICES
        foreach ($incomingInvoiceData as $data) {
            // Check if MCN ORDER NO is in the orders list, otherwise use null
            $order = $ordersMap[$data['MCN ORDER NO']] ?? null;
            $orderId = $order->id ?? null;

            // 1. Determine profit_percentage (from Outgoing Invoice remark)
            $outgoingRemark = $outgoingInvoiceRemarksMap[$data['MCN ORDER NO']] ?? null;
            $profitPercentage = 6.00; // Default is 6%
            
            // Check for '4%' in the outgoing remark (case-insensitive)
            if ($outgoingRemark !== null && str_contains(strtoupper($outgoingRemark), '4%')) {
                $profitPercentage = 4.00; // Set to 4%
            }

            // 2. Create Incoming Invoice
            $incomingInvoice =IncomingInvoice::create([
                'inv_number' => $data['INVOICE NO'],
                'inv_received_date' => $this->cleanDate($data['INV RCVD DATE']),
                'fp_date' => $this->cleanDate($data['INV DATE']),
                'fp_number' => $data['FP S/N'],
                'cur' => $data['CUR'],
                'amount' => $this->cleanAmount($data['AMOUNT']),
                'payment_date' => $this->cleanDate($data['PMT DATE']),
                'remark' => $data['REMARKS'],
                'order_id' => $order->id ?? null, // Will be NULL for '100' or other missing orders
                'vendor_id' => $vendorsMap[$data['SUPPLIER']],
                'department_id' => $departmentsMap[$data['D-CODE']],
                'profit_percentage' => $profitPercentage,
                'created_at' => $this->cleanDate($data['INV DATE']),
                'updated_at' => $this->cleanDate($data['INV DATE']),
            ]);

            // 3. Check for PPh remark (in Incoming Invoice remark) and link the tax
            // Check for 'PPH' in the incoming remark (case-insensitive)
            if ($data['REMARKS'] !== null && str_contains(strtoupper($data['REMARKS']), 'PPH')) {
                // Manually insert into the pivot table using DB::table()
                DB::table('incoming_invoice_taxes')->insert([
                    'incoming_invoice_id' => $incomingInvoice->id,
                    'tax_id' => $pphTaxId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}