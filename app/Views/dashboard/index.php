<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Welcome back! Here's what's happening with your business.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <span class="text-sm text-gray-500"><?= date('F Y') ?></span>
        </div>
    </div>
    
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Revenue This Month -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Revenue (This Month)</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">€<?= number_format($stats['revenue_this_month'], 2) ?></p>
                    <p class="mt-1 text-sm text-green-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        +12.5%
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Outstanding Invoices -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Outstanding</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">€<?= number_format($stats['outstanding_invoices'], 2) ?></p>
                    <p class="mt-1 text-sm text-gray-500"><?= $stats['total_invoices'] ?> total invoices</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Overdue Invoices -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Overdue</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900"><?= $stats['overdue_count'] ?></p>
                    <?php if ($stats['overdue_count'] > 0): ?>
                    <p class="mt-1 text-sm text-red-600">Requires attention</p>
                    <?php else: ?>
                    <p class="mt-1 text-sm text-green-600">All caught up!</p>
                    <?php endif; ?>
                </div>
                <div class="w-12 h-12 <?= $stats['overdue_count'] > 0 ? 'bg-red-100' : 'bg-green-100' ?> rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 <?= $stats['overdue_count'] > 0 ? 'text-red-600' : 'text-green-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- VAT Estimate -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">VAT Payable</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">€<?= number_format($vatEstimate['vat_payable'], 2) ?></p>
                    <p class="mt-1 text-sm text-gray-500">This month estimate</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM19 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Revenue vs Expenses Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue vs Expenses</h3>
            <canvas id="revenueExpensesChart" height="100"></canvas>
        </div>
        
        <!-- Invoice Status Distribution -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Status</h3>
            <canvas id="statusChart" height="200"></canvas>
            <div class="mt-4 space-y-2">
                <?php foreach ($statusDistribution as $status => $count): ?>
                <div class="flex items-center justify-between text-sm">
                    <span class="capitalize text-gray-600"><?= $status ?></span>
                    <span class="font-medium text-gray-900"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Cashflow & Top Clients -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Cashflow -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cash Flow</h3>
            <canvas id="cashflowChart" height="100"></canvas>
        </div>
        
        <!-- Top Clients -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Clients</h3>
            <div class="space-y-4">
                <?php foreach ($topClients as $index => $client): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-bold text-primary-700">#<?= $index + 1 ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($client['company_name'] ?? $client['contact_name']) ?></p>
                            <p class="text-xs text-gray-500"><?= $client['invoice_count'] ?> invoices</p>
                        </div>
                    </div>
                    <span class="font-semibold text-gray-900">€<?= number_format($client['total_revenue'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($topClients)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No clients yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Invoices -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                <a href="/invoices" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="pb-3">Invoice</th>
                            <th class="pb-3">Client</th>
                            <th class="pb-3">Amount</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recentInvoices as $invoice): ?>
                        <tr class="group hover:bg-gray-50">
                            <td class="py-3">
                                <a href="/invoices/<?= $invoice['id'] ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                    <?= $this->e($invoice['invoice_number']) ?>
                                </a>
                            </td>
                            <td class="py-3 text-sm text-gray-700"><?= $this->e($invoice['company_name']) ?></td>
                            <td class="py-3 text-sm font-medium text-gray-900">€<?= number_format($invoice['total'], 2) ?></td>
                            <td class="py-3">
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                    <?= $invoice['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($invoice['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 
                                       ($invoice['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                       'bg-blue-100 text-blue-800')) ?>">
                                    <?= ucfirst($invoice['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recentInvoices)): ?>
                        <tr>
                            <td colspan="4" class="py-8 text-center text-sm text-gray-500">
                                No invoices yet. <a href="/invoices/create" class="text-primary-600 hover:text-primary-700">Create one</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Expenses -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Expenses</h3>
                <a href="/expenses" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="pb-3">Description</th>
                            <th class="pb-3">Vendor</th>
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recentExpenses as $expense): ?>
                        <tr class="group hover:bg-gray-50">
                            <td class="py-3 text-sm text-gray-700"><?= $this->e($expense['description']) ?></td>
                            <td class="py-3 text-sm text-gray-500"><?= $this->e($expense['vendor_name'] ?? 'N/A') ?></td>
                            <td class="py-3 text-sm text-gray-500"><?= date('M d', strtotime($expense['expense_date'])) ?></td>
                            <td class="py-3 text-sm font-medium text-gray-900">€<?= number_format($expense['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recentExpenses)): ?>
                        <tr>
                            <td colspan="4" class="py-8 text-center text-sm text-gray-500">
                                No expenses recorded yet
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Overdue Invoices Alert -->
    <?php if (!empty($overdueInvoices)): ?>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-red-900">Overdue Invoices</h3>
                    <p class="mt-1 text-sm text-red-700">You have <?= count($overdueInvoices) ?> overdue invoice(s) that require attention.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <?php foreach (array_slice($overdueInvoices, 0, 3) as $invoice): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <?= $this->e($invoice['invoice_number']) ?> - €<?= number_format($invoice['balance_due'], 2) ?>
                        </span>
                        <?php endforeach; ?>
                        <?php if (count($overdueInvoices) > 3): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            +<?= count($overdueInvoices) - 3 ?> more
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="/invoices?status=overdue" class="text-sm font-medium text-red-700 hover:text-red-900">
                View all →
            </a>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<!-- Chart Initialization Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Revenue vs Expenses Chart
    const revCtx = document.getElementById('revenueExpensesChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($revenueVsExpenses, 'label')) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode(array_column($revenueVsExpenses, 'revenue')) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Expenses',
                data: <?= json_encode(array_column($revenueVsExpenses, 'expenses')) ?>,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(<?= json_encode($statusDistribution) ?>),
            datasets: [{
                data: Object.values(<?= json_encode($statusDistribution) ?>),
                backgroundColor: [
                    '#9ca3af', // draft
                    '#3b82f6', // sent
                    '#8b5cf6', // viewed
                    '#f59e0b', // partial
                    '#10b981', // paid
                    '#ef4444', // overdue
                    '#6b7280'  // cancelled
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Cashflow Chart
    const cashCtx = document.getElementById('cashflowChart').getContext('2d');
    new Chart(cashCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($cashflowData, 'label')) ?>,
            datasets: [{
                label: 'Cash In',
                data: <?= json_encode(array_column($cashflowData, 'in')) ?>,
                backgroundColor: '#10b981',
                borderRadius: 6
            }, {
                label: 'Cash Out',
                data: <?= json_encode(array_column($cashflowData, 'out')) ?>,
                backgroundColor: '#ef4444',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
