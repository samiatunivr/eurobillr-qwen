<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Eurobillr</title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
</head>
<body class="h-full bg-gradient-to-br from-primary-50 via-white to-primary-100">
    
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        
        <!-- Logo -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-gray-900">Create your account</h2>
            <p class="mt-2 text-sm text-gray-600">Start managing your invoices today</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
                
                <!-- Flash messages -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php if (isset($_SESSION['flash']['success'])): ?>
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                        <?= htmlspecialchars($_SESSION['flash']['success']) ?>
                    </div>
                    <?php unset($_SESSION['flash']['success']); endif; ?>
                    
                    <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                        <?= htmlspecialchars($_SESSION['flash']['error']) ?>
                    </div>
                    <?php unset($_SESSION['flash']['error']); endif; ?>
                <?php endif; ?>
                
                <!-- Registration form -->
                <form class="space-y-5" action="/register" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                            <div class="mt-1">
                                <input id="first_name" name="first_name" type="text" required 
                                       class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                       placeholder="John"
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                            </div>
                            <?php if (isset($errors['first_name'])): ?>
                            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['first_name']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                            <div class="mt-1">
                                <input id="last_name" name="last_name" type="text" required 
                                       class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                       placeholder="Doe"
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                            </div>
                            <?php if (isset($errors['last_name'])): ?>
                            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['last_name']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                   placeholder="you@company.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8"
                                   class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                   placeholder="••••••••">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                        <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                        <div class="mt-1">
                            <select id="country" name="country" 
                                    class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all bg-white">
                                <option value="BE" <?= ($_POST['country'] ?? '') === 'BE' ? 'selected' : '' ?>>Belgium</option>
                                <option value="NL" <?= ($_POST['country'] ?? '') === 'NL' ? 'selected' : '' ?>>Netherlands</option>
                                <option value="FR" <?= ($_POST['country'] ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                                <option value="DE" <?= ($_POST['country'] ?? '') === 'DE' ? 'selected' : '' ?>>Germany</option>
                                <option value="ES" <?= ($_POST['country'] ?? '') === 'ES' ? 'selected' : '' ?>>Spain</option>
                                <option value="GB" <?= ($_POST['country'] ?? '') === 'GB' ? 'selected' : '' ?>>United Kingdom</option>
                                <option value="US" <?= ($_POST['country'] ?? '') === 'US' ? 'selected' : '' ?>>United States</option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">We'll suggest appropriate currency and tax settings</p>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox" required
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-2 text-sm">
                            <label for="terms" class="text-gray-700">I agree to the 
                                <a href="#" class="font-medium text-primary-600 hover:text-primary-500">Terms of Service</a> 
                                and 
                                <a href="#" class="font-medium text-primary-600 hover:text-primary-500">Privacy Policy</a>
                            </label>
                        </div>
                    </div>
                    <?php if (isset($errors['terms'])): ?>
                    <p class="text-xs text-red-600"><?= htmlspecialchars($errors['terms']) ?></p>
                    <?php endif; ?>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all transform hover:scale-[1.02]">
                            Create account
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="/login" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all">
                            Sign in instead
                        </a>
                    </div>
                </div>
            </div>
            
            <p class="mt-6 text-center text-xs text-gray-600">
                Eurobillr is GDPR compliant and keeps your data secure.
            </p>
        </div>
    </div>

</body>
</html>
