<?php
/**
 * Dashboard Controller
 * Main analytics and KPI display
 */

namespace App\Controllers;

use App\Services\InvoiceService;
use App\Repositories\ExpenseRepository;

class DashboardController extends Controller
{
    private InvoiceService $invoiceService;
    
    public function __construct()
    {
        parent::__construct();
        $this->invoiceService = new InvoiceService();
        
        $this->middleware('auth');
    }
    
    /**
     * Show main dashboard
     */
    public function index(): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        // Get KPI data
        $stats = $this->invoiceService->getDashboardStats($workspaceId);
        
        // Get revenue vs expenses chart data
        $revenueVsExpenses = $this->invoiceService->getRevenueVsExpenses($workspaceId, 12);
        
        // Get top clients
        $topClients = $this->invoiceService->getTopClients($workspaceId, 5);
        
        // Get recent invoices
        $recentInvoices = $this->getRecentInvoices($workspaceId, 5);
        
        // Get recent expenses
        $recentExpenses = $this->getRecentExpenses($workspaceId, 5);
        
        // Get overdue invoices
        $overdueInvoices = $this->getOverdueInvoices($workspaceId);
        
        // Get cashflow data
        $cashflowData = $this->getCashflowData($workspaceId);
        
        // Get invoice status distribution
        $statusDistribution = $this->getStatusDistribution($workspaceId);
        
        // Get VAT estimate
        $vatEstimate = $this->getVatEstimate($workspaceId);
        
        // Get currency exposure
        $currencyExposure = $this->getCurrencyExposure($workspaceId);
        
        // Get workspace info
        $workspace = $this->getWorkspaceInfo($workspaceId);
        
        $this->render('dashboard/index', [
            'workspace' => $workspace,
            'stats' => $stats,
            'revenueVsExpenses' => $revenueVsExpenses,
            'topClients' => $topClients,
            'recentInvoices' => $recentInvoices,
            'recentExpenses' => $recentExpenses,
            'overdueInvoices' => $overdueInvoices,
            'cashflowData' => $cashflowData,
            'statusDistribution' => $statusDistribution,
            'vatEstimate' => $vatEstimate,
            'currencyExposure' => $currencyExposure,
            'pageTitle' => 'Dashboard'
        ]);
    }
    
    /**
     * Get recent invoices
     */
    private function getRecentInvoices(int $workspaceId, int $limit = 5): array
    {
        $sql = "SELECT i.*, c.company_name 
                FROM eb_invoices i
                JOIN eb_clients c ON i.client_id = c.id
                WHERE i.workspace_id = :workspace_id AND i.deleted_at IS NULL
                ORDER BY i.created_at DESC
                LIMIT :limit";
        
        $sql = str_replace(':limit', $limit, $sql);
        
        return \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
    }
    
    /**
     * Get recent expenses
     */
    private function getRecentExpenses(int $workspaceId, int $limit = 5): array
    {
        $sql = "SELECT e.*, c.company_name as vendor_name
                FROM eb_expenses e
                LEFT JOIN eb_clients c ON e.vendor_id = c.id
                WHERE e.workspace_id = :workspace_id AND e.deleted_at IS NULL
                ORDER BY e.expense_date DESC
                LIMIT :limit";
        
        $sql = str_replace(':limit', $limit, $sql);
        
        return \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
    }
    
    /**
     * Get overdue invoices
     */
    private function getOverdueInvoices(int $workspaceId): array
    {
        $sql = "SELECT i.*, c.company_name 
                FROM eb_invoices i
                JOIN eb_clients c ON i.client_id = c.id
                WHERE i.workspace_id = :workspace_id 
                AND i.status IN ('sent', 'partial')
                AND i.due_date < CURRENT_DATE
                AND i.deleted_at IS NULL
                ORDER BY i.due_date ASC
                LIMIT 10";
        
        return \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
    }
    
    /**
     * Get cashflow data for chart
     */
    private function getCashflowData(int $workspaceId): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            $monthLabel = date('M', strtotime("-{$i} months"));
            
            // Cash in (paid invoices)
            $inSql = "SELECT COALESCE(SUM(paid_amount), 0) as total 
                     FROM eb_invoices 
                     WHERE workspace_id = :workspace_id 
                     AND paid_at >= :start AND paid_at <= :end
                     AND deleted_at IS NULL";
            
            $inResult = \Core\Database\Database::getInstance(config('database'))->fetchOne($inSql, [
                'workspace_id' => $workspaceId,
                'start' => $monthStart,
                'end' => $monthEnd
            ]);
            
            // Cash out (expenses)
            $outSql = "SELECT COALESCE(SUM(total), 0) as total 
                      FROM eb_expenses 
                      WHERE workspace_id = :workspace_id 
                      AND expense_date >= :start AND expense_date <= :end
                      AND deleted_at IS NULL";
            
            $outResult = \Core\Database\Database::getInstance(config('database'))->fetchOne($outSql, [
                'workspace_id' => $workspaceId,
                'start' => $monthStart,
                'end' => $monthEnd
            ]);
            
            $data[] = [
                'label' => $monthLabel,
                'in' => (float) ($inResult['total'] ?? 0),
                'out' => (float) ($outResult['total'] ?? 0),
                'net' => (float) ($inResult['total'] ?? 0) - (float) ($outResult['total'] ?? 0)
            ];
        }
        
        return $data;
    }
    
    /**
     * Get invoice status distribution
     */
    private function getStatusDistribution(int $workspaceId): array
    {
        $sql = "SELECT status, COUNT(*) as count 
                FROM eb_invoices 
                WHERE workspace_id = :workspace_id AND deleted_at IS NULL
                GROUP BY status";
        
        $results = \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
        
        $distribution = [
            'draft' => 0,
            'sent' => 0,
            'viewed' => 0,
            'partial' => 0,
            'paid' => 0,
            'overdue' => 0,
            'cancelled' => 0
        ];
        
        foreach ($results as $row) {
            $distribution[$row['status']] = (int) $row['count'];
        }
        
        return $distribution;
    }
    
    /**
     * Get VAT payable estimate
     */
    private function getVatEstimate(int $workspaceId): array
    {
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');
        
        // VAT collected from sales
        $vatCollectedSql = "SELECT COALESCE(SUM(i.tax_total), 0) as total 
                           FROM eb_invoices i
                           WHERE i.workspace_id = :workspace_id 
                           AND i.issue_date >= :start AND i.issue_date <= :end
                           AND i.deleted_at IS NULL";
        
        $vatCollected = \Core\Database\Database::getInstance(config('database'))->fetchOne($vatCollectedSql, [
            'workspace_id' => $workspaceId,
            'start' => $currentMonthStart,
            'end' => $currentMonthEnd
        ]);
        
        // VAT paid on expenses
        $vatPaidSql = "SELECT COALESCE(SUM(e.tax_amount), 0) as total 
                      FROM eb_expenses e
                      WHERE e.workspace_id = :workspace_id 
                      AND e.expense_date >= :start AND e.expense_date <= :end
                      AND e.deleted_at IS NULL";
        
        $vatPaid = \Core\Database\Database::getInstance(config('database'))->fetchOne($vatPaidSql, [
            'workspace_id' => $workspaceId,
            'start' => $currentMonthStart,
            'end' => $currentMonthEnd
        ]);
        
        $collected = (float) ($vatCollected['total'] ?? 0);
        $paid = (float) ($vatPaid['total'] ?? 0);
        
        return [
            'vat_collected' => $collected,
            'vat_paid' => $paid,
            'vat_payable' => max(0, $collected - $paid)
        ];
    }
    
    /**
     * Get currency exposure
     */
    private function getCurrencyExposure(int $workspaceId): array
    {
        $baseCurrency = $this->getWorkspaceBaseCurrency($workspaceId);
        
        $sql = "SELECT currency, 
                SUM(balance_due) as total_receivable,
                COUNT(*) as invoice_count
                FROM eb_invoices 
                WHERE workspace_id = :workspace_id 
                AND status IN ('sent', 'partial', 'overdue')
                AND deleted_at IS NULL
                GROUP BY currency";
        
        $results = \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
        
        $exposure = [];
        
        foreach ($results as $row) {
            $exposure[] = [
                'currency' => $row['currency'],
                'is_base' => $row['currency'] === $baseCurrency,
                'amount' => (float) $row['total_receivable'],
                'count' => (int) $row['invoice_count']
            ];
        }
        
        return $exposure;
    }
    
    /**
     * Get workspace base currency
     */
    private function getWorkspaceBaseCurrency(int $workspaceId): string
    {
        $sql = "SELECT default_currency FROM eb_workspaces WHERE id = :id";
        $result = \Core\Database\Database::getInstance(config('database'))->fetchOne($sql, ['id' => $workspaceId]);
        return $result['default_currency'] ?? 'EUR';
    }
    
    /**
     * Get workspace info
     */
    private function getWorkspaceInfo(int $workspaceId): ?array
    {
        $sql = "SELECT id, name, slug, logo_path, brand_color_primary, default_currency 
                FROM eb_workspaces 
                WHERE id = :id AND deleted_at IS NULL";
        
        return \Core\Database\Database::getInstance(config('database'))->fetchOne($sql, ['id' => $workspaceId]);
    }
    
    /**
     * API endpoint for real-time stats refresh
     */
    public function apiStats(): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $this->json([
            'success' => true,
            'data' => $this->invoiceService->getDashboardStats($workspaceId)
        ]);
    }
}
