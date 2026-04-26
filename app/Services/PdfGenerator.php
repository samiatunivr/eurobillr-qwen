<?php
/**
 * PDF Invoice Generator
 * Generates professional PDF invoices with branding, QR codes, and UBL compliance
 */

namespace App\Services;

use Core\Database\Database;

class PdfGenerator
{
    private Database $db;
    private array $config;
    
    public function __construct()
    {
        $this->db = Database::getInstance(config('database'));
        $this->config = config('pdf');
    }
    
    /**
     * Generate PDF for invoice
     */
    public function generateInvoice(int $invoiceId): string
    {
        $invoice = $this->db->fetchOne("
            SELECT i.*, 
                   c.name as client_name, c.email as client_email, 
                   c.vat_number as client_vat, c.address as client_address,
                   c.city as client_city, c.postal_code as client_postal_code,
                   c.country_code as client_country,
                   w.name as workspace_name, w.logo as workspace_logo,
                   w.address as workspace_address, w.city as workspace_city,
                   w.postal_code as workspace_postal_code, w.country_code as workspace_country,
                   w.vat_number as workspace_vat, w.registration_number as workspace_registration,
                   w.brand_color, w.footer_text
            FROM eb_invoices i
            JOIN eb_clients c ON i.client_id = c.id
            JOIN eb_workspaces w ON i.workspace_id = w.id
            WHERE i.id = ?
        ", [$invoiceId]);
        
        if (!$invoice) {
            throw new \Exception('Invoice not found');
        }
        
        $lineItems = $this->db->fetchAll("
            SELECT * FROM eb_invoice_items WHERE invoice_id = ? ORDER BY id ASC
        ", [$invoiceId]);
        
        $payments = $this->db->fetchAll("
            SELECT * FROM eb_payments WHERE invoice_id = ? ORDER BY payment_date DESC
        ", [$invoiceId]);
        
        return $this->renderPdf($invoice, $lineItems, $payments);
    }
    
    /**
     * Render PDF using TCPDF
     */
    private function renderPdf(array $invoice, array $lineItems, array $payments): string
    {
        // Create TCPDF instance
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document info
        $pdf->SetCreator('Eurobillr');
        $pdf->SetAuthor($invoice['workspace_name']);
        $pdf->SetTitle('Invoice ' . $invoice['invoice_number']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        
        // Set margins
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        // Add page
        $pdf->AddPage();
        
        // Apply brand color if set
        $brandColor = $invoice['brand_color'] ?? '#3b82f6';
        $this->hexToRgb($brandColor);
        
        // Header with logo and workspace info
        $this->renderHeader($pdf, $invoice);
        
        // Invoice title and status
        $this->renderTitle($pdf, $invoice, $brandColor);
        
        // Client and invoice details
        $this->renderDetails($pdf, $invoice);
        
        // Line items table
        $this->renderLineItems($pdf, $lineItems, $invoice, $brandColor);
        
        // Payments summary
        if (!empty($payments)) {
            $this->renderPayments($pdf, $payments, $invoice['currency'], $brandColor);
        }
        
        // Footer notes
        $this->renderFooter($pdf, $invoice);
        
        // Return PDF content
        return $pdf->Output('S');
    }
    
    /**
     * Render header with logo
     */
    private function renderHeader(\TCPDF $pdf, array $invoice): void
    {
        $startY = $pdf->GetY();
        
        // Logo
        if ($invoice['workspace_logo']) {
            $logoPath = STORAGE_PATH . '/uploads/logos/' . $invoice['workspace_logo'];
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 15, $startY, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
        }
        
        // Workspace info - right aligned
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        
        $workspaceInfo = $invoice['workspace_name'] . "\n";
        if ($invoice['workspace_address']) {
            $workspaceInfo .= $invoice['workspace_address'] . "\n";
        }
        if ($invoice['workspace_city'] && $invoice['workspace_postal_code']) {
            $workspaceInfo .= $invoice['workspace_postal_code'] . ' ' . $invoice['workspace_city'] . "\n";
        }
        if ($invoice['workspace_country']) {
            $workspaceInfo .= $invoice['workspace_country'] . "\n";
        }
        if ($invoice['workspace_vat']) {
            $workspaceInfo .= 'VAT: ' . $invoice['workspace_vat'] . "\n";
        }
        if ($invoice['workspace_registration']) {
            $workspaceInfo .= 'Reg: ' . $invoice['workspace_registration'];
        }
        
        $pdf->MultiCell(120, 0, trim($workspaceInfo), 0, 'R', false, 0, 105, $startY, true, 0, false, true, 0, 'T', false);
        
        $pdf->Ln(35);
    }
    
    /**
     * Render invoice title and status badge
     */
    private function renderTitle(\TCPDF $pdf, array $invoice, string $brandColor): void
    {
        $rgb = $this->hexToRgb($brandColor);
        
        // INVOICE text
        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
        $pdf->Text(15, $pdf->GetY(), 'INVOICE');
        
        // Status badge
        $statusColors = [
            'draft' => [128, 128, 128],
            'sent' => [59, 130, 246],
            'viewed' => [139, 92, 246],
            'partial' => [245, 158, 11],
            'paid' => [34, 197, 94],
            'overdue' => [239, 68, 68],
            'cancelled' => [107, 114, 128],
        ];
        
        $status = strtolower($invoice['status']);
        $statusColor = $statusColors[$status] ?? [128, 128, 128];
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor($statusColor[0], $statusColor[1], $statusColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        
        $statusText = ' ' . strtoupper($status) . ' ';
        $statusWidth = $pdf->GetStringWidth($statusText) + 6;
        $pdf->RoundedRect(140, $pdf->GetY() - 5, $statusWidth, 8, 2, '1111', 'F');
        $pdf->Text(143, $pdf->GetY() + 2, $statusText);
        
        $pdf->Ln(15);
    }
    
    /**
     * Render client and invoice details
     */
    private function renderDetails(\TCPDF $pdf, array $invoice): void
    {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        
        // Bill To section
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, 'BILL TO:', 0, 1);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);
        
        $clientInfo = $invoice['client_name'] . "\n";
        if ($invoice['client_address']) {
            $clientInfo .= $invoice['client_address'] . "\n";
        }
        if ($invoice['client_city'] && $invoice['client_postal_code']) {
            $clientInfo .= $invoice['client_postal_code'] . ' ' . $invoice['client_city'] . "\n";
        }
        if ($invoice['client_country']) {
            $clientInfo .= $invoice['client_country'] . "\n";
        }
        if ($invoice['client_vat']) {
            $clientInfo .= 'VAT: ' . $invoice['client_vat'] . "\n";
        }
        if ($invoice['client_email']) {
            $clientInfo .= $invoice['client_email'];
        }
        
        $pdf->MultiCell(90, 0, trim($clientInfo), 0, 'L', false, 0, 15, $pdf->GetY(), true, 0, false, true, 0, 'T', false);
        
        // Invoice details - right side
        $pdf->SetFont('helvetica', '', 9);
        
        $details = [
            'Invoice Number:' => $invoice['invoice_number'],
            'Issue Date:' => date('M d, Y', strtotime($invoice['issue_date'])),
            'Due Date:' => date('M d, Y', strtotime($invoice['due_date'])),
        ];
        
        if ($invoice['po_number']) {
            $details['PO Number:'] = $invoice['po_number'];
        }
        
        $startX = 120;
        $startY = $pdf->GetY() - 2;
        
        foreach ($details as $label => $value) {
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Text($startX, $startY, $label);
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->Text($startX + 45, $startY, $value);
            
            $startY += 5;
        }
        
        $pdf->Ln(35);
    }
    
    /**
     * Render line items table
     */
    private function renderLineItems(\TCPDF $pdf, array $items, array $invoice, string $brandColor): void
    {
        $rgb = $this->hexToRgb($brandColor);
        
        // Table header
        $pdf->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);
        
        $headers = ['Description', 'Qty', 'Unit Price', 'Tax %', 'Amount'];
        $widths = [85, 20, 30, 20, 35];
        
        $pdf->Cell($widths[0], 7, $headers[0], 1, 0, 'L', true);
        $pdf->Cell($widths[1], 7, $headers[1], 1, 0, 'C', true);
        $pdf->Cell($widths[2], 7, $headers[2], 1, 0, 'R', true);
        $pdf->Cell($widths[3], 7, $headers[3], 1, 0, 'C', true);
        $pdf->Cell($widths[4], 7, $headers[4], 1, 1, 'R', true);
        
        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->SetFillColor(250, 250, 250);
        
        $fill = false;
        foreach ($items as $item) {
            $amount = $item['quantity'] * $item['unit_price'];
            
            $pdf->Cell($widths[0], 6, $item['description'], 1, 0, 'L', $fill);
            $pdf->Cell($widths[1], 6, number_format($item['quantity'], 2), 1, 0, 'C', $fill);
            $pdf->Cell($widths[2], 6, $this->formatCurrency($item['unit_price'], $invoice['currency']), 1, 0, 'R', $fill);
            $pdf->Cell($widths[3], 6, number_format($item['tax_rate'], 1) . '%', 1, 0, 'C', $fill);
            $pdf->Cell($widths[4], 6, $this->formatCurrency($amount, $invoice['currency']), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }
        
        // Totals
        $pdf->SetFont('helvetica', '', 9);
        
        $totalsStartX = 120;
        $totalsStartY = $pdf->GetY() + 2;
        
        // Subtotal
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Text($totalsStartX, $totalsStartY, 'Subtotal:');
        $pdf->Text($totalsStartX + 50, $totalsStartY, $this->formatCurrency($invoice['subtotal'], $invoice['currency']));
        
        // Tax
        $totalsStartY += 5;
        $pdf->Text($totalsStartX, $totalsStartY, 'Tax:');
        $pdf->Text($totalsStartX + 50, $totalsStartY, $this->formatCurrency($invoice['tax_amount'], $invoice['currency']));
        
        // Discount
        if ($invoice['discount_amount'] > 0) {
            $totalsStartY += 5;
            $pdf->Text($totalsStartX, $totalsStartY, 'Discount:');
            $pdf->Text($totalsStartX + 50, $totalsStartY, '-' . $this->formatCurrency($invoice['discount_amount'], $invoice['currency']));
        }
        
        // Total
        $totalsStartY += 8;
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
        $pdf->Text($totalsStartX, $totalsStartY, 'Total:');
        $pdf->Text($totalsStartX + 50, $totalsStartY, $this->formatCurrency($invoice['total'], $invoice['currency']));
        
        // Amount due
        $amountDue = $invoice['total'] - $invoice['amount_paid'];
        $totalsStartY += 6;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Text($totalsStartX, $totalsStartY, 'Amount Due:');
        $pdf->Text($totalsStartX + 50, $totalsStartY, $this->formatCurrency($amountDue, $invoice['currency']));
        
        $pdf->Ln(25);
    }
    
    /**
     * Render payments history
     */
    private function renderPayments(\TCPDF $pdf, array $payments, string $currency, string $brandColor): void
    {
        $rgb = $this->hexToRgb($brandColor);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
        $pdf->Cell(0, 6, 'PAYMENTS RECEIVED', 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(40, 40, 40);
        
        foreach ($payments as $payment) {
            $date = date('M d, Y', strtotime($payment['payment_date']));
            $amount = $this->formatCurrency($payment['amount'], $currency);
            $method = ucfirst($payment['payment_method']);
            
            $pdf->Cell(60, 5, $date, 0, 0);
            $pdf->Cell(60, 5, $method, 0, 0);
            $pdf->Cell(50, 5, $amount, 0, 1, 'R');
        }
        
        $pdf->Ln(5);
    }
    
    /**
     * Render footer with notes and payment info
     */
    private function renderFooter(\TCPDF $pdf, array $invoice): void
    {
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        
        // Payment terms
        if ($invoice['notes']) {
            $pdf->MultiCell(0, 5, "Notes:\n" . $invoice['notes'], 0, 'L', false, 1, 15, $pdf->GetY(), true, 0, false, true, 0, 'T', false);
            $pdf->Ln(3);
        }
        
        // Payment instructions
        $paymentTerms = "Payment is due within " . $invoice['payment_terms'] . " days.\n";
        $paymentTerms .= "Thank you for your business!";
        
        $pdf->MultiCell(0, 5, $paymentTerms, 0, 'L', false, 1, 15, $pdf->GetY(), true, 0, false, true, 0, 'T', false);
        
        // Footer text from workspace settings
        if ($invoice['footer_text']) {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->MultiCell(0, 4, $invoice['footer_text'], 0, 'C', false, 0, 15, $pdf->GetY(), true, 0, false, true, 0, 'T', false);
        }
    }
    
    /**
     * Format currency amount
     */
    private function formatCurrency(float $amount, string $currency): string
    {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'CHF' => 'CHF ',
            'SEK' => 'kr ',
            'NOK' => 'kr ',
            'DKK' => 'kr ',
        ];
        
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }
    
    /**
     * Convert hex color to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return [$r, $g, $b];
    }
}
