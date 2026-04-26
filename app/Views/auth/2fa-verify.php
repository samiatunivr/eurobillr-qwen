<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Eurobillr</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gradient-to-br from-primary-50 via-white to-primary-100">
    
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-gray-900">Two-Factor Authentication</h2>
            <p class="mt-2 text-sm text-gray-600">Enter the code from your authenticator app</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
                
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                        <?= htmlspecialchars($_SESSION['flash']['error']) ?>
                    </div>
                    <?php unset($_SESSION['flash']['error']); endif; ?>
                <?php endif; ?>
                
                <form class="space-y-6" action="/2fa-verify" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 text-center">
                            Authentication Code
                        </label>
                        <div class="mt-2 flex justify-center">
                            <input id="code" name="code" type="text" inputmode="numeric" 
                                   pattern="[0-9]*" maxlength="6" required autofocus
                                   class="appearance-none block w-48 px-4 py-4 text-3xl text-center tracking-[1em] border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                   placeholder="000000"
                                   autocomplete="one-time-code">
                        </div>
                        <p class="mt-2 text-xs text-gray-500 text-center">
                            Open your authenticator app and enter the 6-digit code
                        </p>
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all">
                            Verify
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Can't access your authenticator app? 
                        <a href="#" class="font-medium text-primary-600 hover:text-primary-500">Use backup code</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits entered
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
        
        // Focus on load
        document.getElementById('code').focus();
    </script>

</body>
</html>
