<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('admin.login_title') }} - {{ config('app.name') }}</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-primary': '#1877F2',
                        'brand-secondary': '#42B883',
                        'brand-accent': '#FF6B6B',
                        'brand-dark': '#1a1a1a',
                        'brand-light': '#f8fafc',
                        'admin-primary': '#7C3AED',
                        'admin-secondary': '#A855F7'
                    }
                }
            }
        }
    </script>

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .glassmorphism {
            background: rgba(17, 24, 39, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(75, 85, 99, 0.3);
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .btn-admin {
            background: linear-gradient(135deg, #7C3AED, #A855F7);
            transition: all 0.3s ease;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.3);
        }

        .hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #4338ca 50%, #7c3aed 100%);
        }

        .form-input {
            background: rgba(75, 85, 99, 0.3);
            border: 1px solid rgba(75, 85, 99, 0.5);
            color: white;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #7C3AED;
            background: rgba(75, 85, 99, 0.4);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .form-input::placeholder {
            color: rgba(156, 163, 175, 0.7);
        }

        /* Hide scrollbar while keeping functionality */
        html, body {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer 10+ */
        }
        
        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 0px; /* Chrome, Safari and Opera */
            background: transparent;
        }
    </style>
</head>
<body class="min-h-screen hero-bg">
    {{-- Header --}}
    <header class="relative z-10 glassmorphism border-b border-gray-800">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-white text-lg"></i>
                </div>
                <span class="text-xl font-bold text-white">{{ config('app.name', 'MessengerPro') }} Admin</span>
            </a>

            {{-- Language Switcher --}}
            <div class="flex space-x-2">
                <a href="{{ route('set-language', 'en') }}" class="px-3 py-1 text-sm rounded-md {{ app()->getLocale() == 'en' ? 'bg-admin-primary text-white' : 'text-gray-300 bg-gray-700 hover:bg-gray-600' }}">EN</a>
                <a href="{{ route('set-language', 'bn') }}" class="px-3 py-1 text-sm rounded-md {{ app()->getLocale() == 'bn' ? 'bg-admin-primary text-white' : 'text-gray-300 bg-gray-700 hover:bg-gray-600' }}">BN</a>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <section class="relative overflow-hidden py-20 lg:py-32">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto animate-fade-in">
                <div class="glassmorphism rounded-2xl p-8 border border-gray-700">
                    {{-- Header --}}
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-white text-2xl"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-white mb-2">{{ __('admin.login_title') }}</h1>
                        <p class="text-gray-300">{{ __('admin.system_management') }}</p>
                    </div>

                    {{-- Error Messages --}}
                    @if ($errors->any())
                        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                                <ul class="text-red-300 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Login Form --}}
                    <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                        @csrf
                        
                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-envelope mr-2"></i>{{ __('admin.email_address') }}
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="{{ __('admin.email_address') }}"
                                   class="form-input w-full px-4 py-3 rounded-lg focus:outline-none"
                                   required>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2"></i>{{ __('admin.password') }}
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="{{ __('admin.password') }}"
                                   class="form-input w-full px-4 py-3 rounded-lg focus:outline-none"
                                   required>
                        </div>

                        {{-- Remember Me --}}
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }} 
                                   class="w-4 h-4 text-admin-primary bg-gray-600 border-gray-500 rounded focus:ring-admin-primary focus:ring-2">
                            <label for="remember" class="ml-2 text-sm text-gray-300">{{ __('admin.keep_me_logged_in') }}</label>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn-admin w-full py-3 px-4 text-white font-semibold rounded-lg flex items-center justify-center group">
                            <i class="fas fa-sign-in-alt mr-2 group-hover:translate-x-1 transition-transform"></i>
                            {{ __('admin.log_in') }}
                        </button>
                    </form>

                    {{-- Footer Info --}}
                    <div class="mt-8 text-center space-y-3">
                        <div class="bg-amber-500/20 border border-amber-500/50 rounded-lg p-3">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-amber-400 mr-2"></i>
                                <p class="text-amber-300 text-sm">{{ __('admin.access_restricted') }}</p>
                            </div>
                        </div>
                        <p>
                            <a href="{{ route('home') }}" class="text-gray-400 hover:text-admin-primary transition-colors flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                {{ __('welcome.back_to_homepage') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Animated Background Elements --}}
        <div class="absolute top-20 left-10 w-72 h-72 bg-admin-primary/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-admin-secondary/10 rounded-full blur-3xl animate-float" style="animation-delay: 3s;"></div>
    </section>

    <script>
        // Simple form validation enhancements
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>{{ __("common.loading") }}';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds as fallback
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
</body>
</html>