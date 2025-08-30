<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('welcome.title') }}</title>

    {{-- Tailwind CSS CDN - You can also install it via npm --}}
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
                        'brand-light': '#f8fafc'
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

        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            background: rgba(31, 41, 55, 0.6);
        }

        .gradient-text {
            background: linear-gradient(135deg, #1877F2, #42B883, #FF6B6B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-primary {
            background: #1877F2;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #1565C0;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(24, 119, 242, 0.3);
        }

        .hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #312e81 100%);
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

        /* Custom scrollbar for webkit browsers (optional - shows thin scrollbar on hover) */
        html:hover::-webkit-scrollbar,
        body:hover::-webkit-scrollbar {
            width: 6px;
        }

        html:hover::-webkit-scrollbar-track,
        body:hover::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        html:hover::-webkit-scrollbar-thumb,
        body:hover::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.5);
            border-radius: 3px;
        }

        html:hover::-webkit-scrollbar-thumb:hover,
        body:hover::-webkit-scrollbar-thumb:hover {
            background: rgba(102, 126, 234, 0.8);
        }
    </style>
</head>
<body class="min-h-screen hero-bg">
{{-- Header --}}
<header class="relative z-10 glassmorphism border-b border-gray-800">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center space-x-2">
            <div class="w-10 h-10 bg-gradient-to-r from-brand-primary to-brand-secondary rounded-lg flex items-center justify-center">
                <i class="fab fa-facebook-f text-white text-lg"></i>
            </div>
            <span class="text-xl font-bold text-white">{{ config('app.name', 'MessengerPro') }}</span>
        </a>

        <nav class="hidden md:flex space-x-8">
            <a href="#features" class="text-gray-300 hover:text-brand-primary transition-colors">{{ __('welcome.features') }}</a>
            <a href="#pricing" class="text-gray-300 hover:text-brand-primary transition-colors">{{ __('welcome.pricing') }}</a>
            <a href="#contact" class="text-gray-300 hover:text-brand-primary transition-colors">{{ __('welcome.contact') }}</a>
        </nav>

        <div class="flex space-x-3 items-center">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2 text-gray-300 hover:text-brand-primary transition-colors">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-gray-300 hover:text-brand-primary transition-colors">
                    {{ __('welcome.login') }}
                </a>
                <a href="{{ route('register') }}" class="btn-primary px-6 py-2 text-white rounded-lg font-medium">
                    {{ __('welcome.get_started') }}
                </a>
            @endauth

            {{-- Language Switcher --}}
            <div class="flex space-x-2">
                <a href="{{ route('set-language', 'en') }}" class="px-3 py-1 text-sm rounded-md {{ app()->getLocale() == 'en' ? 'bg-brand-primary text-white' : 'text-gray-300 bg-gray-700 hover:bg-gray-600' }}">EN</a>
                <a href="{{ route('set-language', 'bn') }}" class="px-3 py-1 text-sm rounded-md {{ app()->getLocale() == 'bn' ? 'bg-brand-primary text-white' : 'text-gray-300 bg-gray-700 hover:bg-gray-600' }}">BN</a>
            </div>
        </div>

        {{-- Mobile menu button --}}
        <button class="md:hidden text-white" id="mobile-menu-btn">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-4 py-4 space-y-4 border-t border-gray-800">
            <a href="#features" class="block text-gray-300 hover:text-brand-primary transition-colors">{{ __('welcome.features') }}</a>
            <a href="#pricing" class="block text-gray-300 hover:text-brand-primary transition-colors">{{ __('welcome.pricing') }}</a>
            <a href="#contact" class="block text-gray-300 hover:text-brand-primary transition-colors">{{ __('welcome.contact') }}</a>
        </div>
    </div>
</header>

{{-- Hero Section --}}
<section class="relative overflow-hidden py-20 lg:py-32">
    <div class="container mx-auto px-4">
        <div class="text-center animate-fade-in">
            <div class="inline-flex items-center rounded-full border px-4 py-2 mb-6 bg-brand-primary/10 text-brand-primary border-brand-primary/20">
                <i class="fas fa-bolt mr-2"></i>
                Powered by AI Automation
            </div>

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                {{ __('welcome.hero_title') }}
            </h1>

            <p class="text-xl text-gray-300 mb-10 max-w-3xl mx-auto leading-relaxed">
                {{ __('welcome.hero_subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="btn-primary px-8 py-4 text-lg font-medium rounded-lg flex items-center group">
                        Go to Dashboard
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="btn-primary px-8 py-4 text-lg font-medium rounded-lg flex items-center group">
                        {{ __('welcome.hero_button') }}
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                @endauth
                <button class="px-8 py-4 text-lg text-gray-300 border border-gray-600 rounded-lg hover:bg-gray-800 transition-colors flex items-center group"
                        onclick="playDemo()">
                    <i class="fas fa-play mr-2 group-hover:scale-110 transition-transform"></i>
                    {{ __('welcome.watch_demo') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Animated Background Elements --}}
    <div class="absolute top-20 left-10 w-72 h-72 bg-brand-primary/10 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-brand-secondary/10 rounded-full blur-3xl animate-float"
         style="animation-delay: 3s;"></div>
</section>

{{-- Features Section --}}
<section id="features" class="py-20 glassmorphism">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                {{ __('welcome.section_title_features') }}
            </h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Feature 1 --}}
            <div class="feature-card glassmorphism rounded-lg p-6 border border-gray-800">
                <div class="mb-4">
                    <i class="fas fa-comments text-4xl text-brand-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">{{ __('welcome.feature_1_title') }}</h3>
                <p class="text-gray-300 leading-relaxed">{{ __('welcome.feature_1_description') }}</p>
            </div>

            {{-- Feature 2 --}}
            <div class="feature-card glassmorphism rounded-lg p-6 border border-gray-800">
                <div class="mb-4">
                    <i class="fas fa-robot text-4xl text-brand-secondary"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">{{ __('welcome.feature_2_title') }}</h3>
                <p class="text-gray-300 leading-relaxed">{{ __('welcome.feature_2_description') }}</p>
            </div>

            {{-- Feature 3 --}}
            <div class="feature-card glassmorphism rounded-lg p-6 border border-gray-800">
                <div class="mb-4">
                    <i class="fas fa-shopping-cart text-4xl text-brand-accent"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">{{ __('welcome.feature_3_title') }}</h3>
                <p class="text-gray-300 leading-relaxed">{{ __('welcome.feature_3_description') }}</p>
            </div>

            {{-- Feature 4 --}}
            <div class="feature-card glassmorphism rounded-lg p-6 border border-gray-800">
                <div class="mb-4">
                    <i class="fas fa-chart-line text-4xl text-brand-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">{{ __('welcome.feature_4_title') }}</h3>
                <p class="text-gray-300 leading-relaxed">{{ __('welcome.feature_4_description') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- How It Works Section --}}
<section id="how-it-works" class="py-20 bg-gray-950">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                {{ __('welcome.section_title_how_it_works') }}
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            {{-- Step 1 --}}
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-brand-primary/10 flex items-center justify-center mb-4">
                    <i class="fas fa-plug text-4xl text-brand-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('welcome.step_1_title') }}</h3>
                <p class="text-gray-400">{{ __('welcome.step_1_description') }}</p>
            </div>
            {{-- Step 2 --}}
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-brand-secondary/10 flex items-center justify-center mb-4">
                    <i class="fas fa-cogs text-4xl text-brand-secondary"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('welcome.step_2_title') }}</h3>
                <p class="text-gray-400">{{ __('welcome.step_2_description') }}</p>
            </div>
            {{-- Step 3 --}}
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-brand-accent/10 flex items-center justify-center mb-4">
                    <i class="fas fa-rocket text-4xl text-brand-accent"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('welcome.step_3_title') }}</h3>
                <p class="text-gray-400">{{ __('welcome.step_3_description') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials Section --}}
<section id="testimonials" class="py-20 glassmorphism">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                {{ __('welcome.section_title_testimonials') }}
            </h2>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="feature-card glassmorphism rounded-lg p-6 border border-gray-800">
                <p class="text-gray-300 italic mb-4">"{{ __('welcome.testimonial_1_text') }}"</p>
                <p class="text-white font-semibold">- {{ __('welcome.testimonial_1_author') }}</p>
            </div>
            <div class="feature-card glassmorphism rounded-lg p-6 border border-gray-800">
                <p class="text-gray-300 italic mb-4">"{{ __('welcome.testimonial_2_text') }}"</p>
                <p class="text-white font-semibold">- {{ __('welcome.testimonial_2_author') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Integrations Section --}}
<section id="integrations" class="py-20 bg-gray-950">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                {{ __('welcome.section_title_integrations') }}
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-gray-800 flex items-center justify-center mb-4">
                    <i class="fas fa-file-excel text-4xl text-green-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('welcome.integration_1_title') }}</h3>
                <p class="text-gray-400">{{ __('welcome.integration_1_description') }}</p>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-gray-800 flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-4xl text-yellow-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('welcome.integration_2_title') }}</h3>
                <p class="text-gray-400">{{ __('welcome.integration_2_description') }}</p>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-gray-800 flex items-center justify-center mb-4">
                    <i class="fas fa-bolt text-4xl text-blue-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('welcome.integration_3_title') }}</h3>
                <p class="text-gray-400">{{ __('welcome.integration_3_description') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-20 glassmorphism">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">
            {{ __('welcome.section_title_cta') }}
        </h2>
        <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto">
            {{ __('welcome.cta_text') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="btn-primary px-8 py-4 text-lg font-medium rounded-lg flex items-center group">
                    Go to Dashboard
                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="btn-primary px-8 py-4 text-lg font-medium rounded-lg flex items-center group">
                    {{ __('welcome.get_started') }}
                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            @endauth
            <div class="flex items-center text-gray-400 text-sm">
                <i class="fas fa-shield-alt mr-2"></i>
                No credit card required
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-gray-950 text-gray-300 py-12 border-t border-gray-800">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-r from-brand-primary to-brand-secondary rounded-lg flex items-center justify-center">
                        <i class="fab fa-facebook-f text-white"></i>
                    </div>
                    <span class="text-lg font-bold text-white">{{ config('app.name', 'MessengerPro') }}</span>
                </div>
                <p class="text-gray-400">
                    {{ __('welcome.description') }}
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">{{ __('welcome.features') }}</h4>
                <ul class="space-y-2">
                    <li><a href="#features" class="hover:text-brand-primary transition-colors">{{ __('welcome.features') }}</a></li>
                    <li><a href="#pricing" class="hover:text-brand-primary transition-colors">{{ __('welcome.pricing') }}</a></li>
                    <li><a href="#" class="hover:text-brand-primary transition-colors">API</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">{{ __('welcome.about') }}</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-brand-primary transition-colors">{{ __('welcome.about') }}</a></li>
                    <li><a href="#" class="hover:text-brand-primary transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-brand-primary transition-colors">Careers</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">{{ __('welcome.contact') }}</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-brand-primary transition-colors">{{ __('welcome.contact') }}</a></li>
                    <li><a href="#" class="hover:text-brand-primary transition-colors">Help Center</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'MessengerPro') }}. {{ __('welcome.footer_text') }}</p>
        </div>
    </div>
</footer>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn').addEventListener('click', function () {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobile-menu');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
    });

    // Demo function
    function playDemo() {
        // Create and show video modal
        showDemoModal();
    }

    // Function to create and show demo modal
    function showDemoModal() {
        // Get translated content
        const modalTitle = @json(__('welcome.demo_modal_title'));
        const modalDescription = @json(__('welcome.demo_modal_description'));
        
        // Create modal HTML
        const modalHtml = `
            <div id="demo-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
                <div class="relative max-w-4xl w-full mx-4">
                    <!-- Close button -->
                    <button onclick="closeDemoModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 text-2xl z-10">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <!-- Video container -->
                    <div class="bg-black rounded-lg overflow-hidden">
                        <video id="modal-video" controls autoplay class="w-full h-auto max-h-[70vh]">
                            <source src="/demo/demo.mp4" type="video/mp4">
                            <p class="text-white text-center p-8">
                                Your browser does not support the video tag.
                                <a href="/demo/demo.mp4" class="text-blue-400 underline">Download the video</a>
                            </p>
                        </video>
                        
                        <!-- Video info -->
                        <div class="p-6 bg-gray-900">
                            <h3 class="text-xl font-semibold text-white mb-2">${modalTitle}</h3>
                            <p class="text-gray-300">${modalDescription}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Close modal when clicking outside video
        document.getElementById('demo-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDemoModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDemoModal();
            }
        }, { once: true });
    }

    // Function to close demo modal
    function closeDemoModal() {
        const modal = document.getElementById('demo-modal');
        if (modal) {
            // Pause video before removing
            const video = document.getElementById('modal-video');
            if (video) {
                video.pause();
            }
            
            // Remove modal
            modal.remove();
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }
    }

    // Header background on scroll
    window.addEventListener('scroll', function () {
        const header = document.querySelector('header');
        if (window.scrollY > 50) {
            header.style.background = 'rgba(17, 24, 39, 0.8)';
        } else {
            header.style.background = 'rgba(17, 24, 39, 0.5)';
        }
    });

    console.log('{{ config("app.name", "MessengerPro") }} Landing Page Loaded!');
</script>
</body>
</html>
