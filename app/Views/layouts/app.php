<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Eurobillr' ?> - Eurobillr</title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Smooth transitions */
        .transition-all-300 {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="h-full bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false, darkMode: false }">
    
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
         x-cloak>
    </div>
    
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0">
        
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
            <a href="/dashboard" class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-900">Eurobillr</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            
            <!-- Dashboard -->
            <a href="/dashboard" class="<?= (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            
            <!-- Invoices -->
            <a href="/invoices" class="<?= (strpos($_SERVER['REQUEST_URI'], '/invoices') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/invoices') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Invoices
            </a>
            
            <!-- Expenses -->
            <a href="/expenses" class="<?= (strpos($_SERVER['REQUEST_URI'], '/expenses') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/expenses') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Expenses
            </a>
            
            <!-- Clients -->
            <a href="/clients" class="<?= (strpos($_SERVER['REQUEST_URI'], '/clients') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/clients') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Clients
            </a>
            
            <!-- Reports -->
            <a href="/reports" class="<?= (strpos($_SERVER['REQUEST_URI'], '/reports') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/reports') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reports
            </a>
            
            <!-- Team -->
            <a href="/team" class="<?= (strpos($_SERVER['REQUEST_URI'], '/team') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/team') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Team
            </a>
            
            <!-- Settings -->
            <a href="/settings" class="<?= (strpos($_SERVER['REQUEST_URI'], '/settings') !== false) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-3 <?= (strpos($_SERVER['REQUEST_URI'], '/settings') !== false) ? 'text-primary-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
        </nav>
        
        <!-- User profile -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-primary-700 font-semibold"><?= strtoupper(substr($_SESSION['user_email'] ?? 'U', 0, 1)) ?></span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                </div>
                <a href="/logout" class="ml-auto text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </aside>
    
    <!-- Main content area -->
    <div class="flex-1 flex flex-col overflow-hidden lg:pl-64">
        
        <!-- Top header -->
        <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200">
            <!-- Mobile menu button -->
            <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            
            <!-- Search -->
            <div class="flex-1 max-w-lg ml-4 lg:ml-0">
                <div class="relative">
                    <input type="text" 
                           placeholder="Search invoices, clients... (Press /)" 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Right actions -->
            <div class="flex items-center space-x-4">
                <!-- Quick create button -->
                <a href="/invoices/create" class="hidden sm:inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Invoice
                </a>
                
                <!-- Notifications -->
                <button class="relative p-2 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
            </div>
        </header>
        
        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash'])): ?>
        <div class="px-6 py-4" x-data="{ show: true }" x-show="show" x-cloak>
            <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="max-w-7xl mx-auto">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= htmlspecialchars($_SESSION['flash']['success']) ?>
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['flash']['success']); endif; ?>
            
            <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="max-w-7xl mx-auto">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?= htmlspecialchars($_SESSION['flash']['error']) ?>
                    </div>
                    <button @click="show = false" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['flash']['error']); endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Page content -->
        <main class="flex-1 overflow-y-auto p-6">
            <?= $content ?? '' ?>
        </main>
    </div>
    
    <!-- Keyboard shortcuts -->
    <script>
        // Search shortcut
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && e.target.tagName !== 'INPUT') {
                e.preventDefault();
                document.querySelector('input[type="text"]').focus();
            }
        });
        
        // Auto-hide flash messages
        setTimeout(function() {
            document.querySelectorAll('[x-data]').forEach(el => {
                el.__x.$data.show = false;
            });
        }, 5000);
    </script>
    
</body>
</html>
