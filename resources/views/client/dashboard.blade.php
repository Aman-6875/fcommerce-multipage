@extends('layouts.client')

@section('title', __('client.dashboard'))

@section('content')
{{-- Dashboard Header --}}
<div class="dashboard-header" style="background: linear-gradient(135deg, #1877F2 0%, #42B883 100%); padding: 30px; border-radius: 0px; margin-bottom: 30px; position: relative; overflow: hidden;">
    {{-- Background Pattern --}}
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: radial-gradient(circle at 20% 50%, white 2px, transparent 2px), radial-gradient(circle at 80% 50%, white 2px, transparent 2px); background-size: 30px 30px;"></div>
    
    <div class="row align-items-center position-relative">
        <div class="col-lg-8">
            <h1 class="text-white mb-2" style="font-size: 2.5rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">{{ __('client.dashboard') }}</h1>
            <p class="text-white mb-0" style="font-size: 1.1rem; opacity: 0.9;">{{ __('client.welcome_message') }}</p>
        </div>
        <div class="col-lg-4 text-right">
            @if($client->isFree())
                <div class="d-flex align-items-center justify-content-end flex-wrap">
                    <div class="mr-3 mb-2">
                        <div class="bg-white rounded-lg px-3 py-2 d-inline-block" style="box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <span class="badge badge-warning">{{ __('client.free_plan') }}</span>
                            @if($trialDaysLeft > 0)
                                <small class="text-muted ml-2">{{ $trialDaysLeft }} {{ __('client.days_left') }}</small>
                            @else
                                <small class="text-danger ml-2">{{ __('client.trial_expired') }}</small>
                            @endif
                        </div>
                    </div>
                    <a href="#" class="btn text-white mb-2" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                        <i class="fas fa-crown mr-2"></i>{{ __('client.upgrade_now') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Free Plan Limits --}}
@if($client->isFree())
<div class="row mb-4">
    <div class="col-lg-12">
        <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
            <h5 style="color: #2d3748; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center;">
                <i class="fas fa-info-circle text-info mr-2"></i> {{ __('client.free_plan_limits') }}
            </h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 12px; padding: 20px; border-left: 4px solid #1877F2;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="color: #4a5568; font-weight: 500;">{{ __('client.trial_period') }}</span>
                            <strong style="color: #2d3748;">{{ $trialDaysLeft }}/10 {{ __('client.days_left') }}</strong>
                        </div>
                        <div style="background: #e2e8f0; border-radius: 10px; height: 8px; overflow: hidden;">
                            <div class="bg-{{ $trialDaysLeft > 3 ? 'success' : ($trialDaysLeft > 1 ? 'warning' : 'danger') }}" 
                                 style="width: {{ ($trialDaysLeft / 10) * 100 }}%; height: 100%; border-radius: 10px; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 12px; padding: 20px; border-left: 4px solid #42B883;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="color: #4a5568; font-weight: 500;">{{ __('client.subscribers') }}</span>
                            <strong style="color: #2d3748;">{{ $stats['total_customers'] }}/20</strong>
                        </div>
                        <div style="background: #e2e8f0; border-radius: 10px; height: 8px; overflow: hidden;">
                            <div class="bg-{{ $stats['total_customers'] < 15 ? 'success' : ($stats['total_customers'] < 18 ? 'warning' : 'danger') }}" 
                                 style="width: {{ ($stats['total_customers'] / 20) * 100 }}%; height: 100%; border-radius: 10px; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 12px; padding: 20px; border-left: 4px solid #FF6B6B;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="color: #4a5568; font-weight: 500;">{{ __('client.messages_sent') }}</span>
                            <strong style="color: #2d3748;">{{ $stats['messages_sent'] }}/50</strong>
                        </div>
                        <div style="background: #e2e8f0; border-radius: 10px; height: 8px; overflow: hidden;">
                            <div class="bg-{{ $stats['messages_sent'] < 35 ? 'success' : ($stats['messages_sent'] < 45 ? 'warning' : 'danger') }}" 
                                 style="width: {{ ($stats['messages_sent'] / 50) * 100 }}%; height: 100%; border-radius: 10px; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                </div>
            </div>
            @if($freeLimitsReached)
                <div class="alert alert-danger mt-3" style="background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%); border: none; border-radius: 12px;">
                    <h6><i class="fas fa-exclamation-triangle"></i> {{ __('client.upgrade_required') }}</h6>
                    <p class="mb-2">{{ __('client.free_limits_reached_message') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('client.unlimited_subscribers') }}</li>
                        <li>{{ __('client.unlimited_messages') }}</li>
                        <li>{{ __('client.advanced_automation') }}</li>
                        <li>{{ __('client.priority_support') }}</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Stats Cards - Modern Design --}}
<div class="row mb-4">
    <div class="col-xl-3 col-sm-6 mb-4">
        <div style="background: linear-gradient(135deg, #1877F2, #4267B2); border-radius: 20px; padding: 25px; color: white; position: relative; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;" 
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(24,119,242,0.3)'" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)'">
            {{-- Background Pattern --}}
            <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.7;"></div>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-1" style="font-size: 2.5rem; font-weight: 700;">{{ $stats['facebook_pages'] ?? 0 }}</h3>
                    <p class="mb-0" style="opacity: 0.9; font-size: 1rem;">{{ __('client.facebook_pages') }}</p>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                    <i class="fab fa-facebook-f" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-4">
        <div style="background: linear-gradient(135deg, #42B883, #369970); border-radius: 20px; padding: 25px; color: white; position: relative; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;" 
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(66,184,131,0.3)'" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)'">
            {{-- Background Pattern --}}
            <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.7;"></div>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-1" style="font-size: 2.5rem; font-weight: 700;">{{ $stats['total_customers'] ?? 0 }}</h3>
                    <p class="mb-0" style="opacity: 0.9; font-size: 1rem;">{{ __('client.total_customers') }}</p>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-users" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-4">
        <div style="background: linear-gradient(135deg, #FF6B6B, #EE5A52); border-radius: 20px; padding: 25px; color: white; position: relative; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;" 
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(255,107,107,0.3)'" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)'">
            {{-- Background Pattern --}}
            <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.7;"></div>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-1" style="font-size: 2.5rem; font-weight: 700;">{{ $stats['total_orders'] ?? 0 }}</h3>
                    <p class="mb-0" style="opacity: 0.9; font-size: 1rem;">{{ __('client.total_orders') }}</p>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-shopping-cart" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-4">
        <div style="background: linear-gradient(135deg, #4F46E5, #7C3AED); border-radius: 20px; padding: 25px; color: white; position: relative; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;" 
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(79,70,229,0.3)'" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)'">
            {{-- Background Pattern --}}
            <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.7;"></div>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-1" style="font-size: 2.5rem; font-weight: 700;">{{ $stats['messages_sent'] ?? 0 }}</h3>
                    <p class="mb-0" style="opacity: 0.9; font-size: 1rem;">{{ __('client.messages_sent') }}</p>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-envelope" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Content Row --}}
<div class="row">
    {{-- Quick Stats --}}
    <div class="col-lg-8 mb-4">
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
            <h4 style="color: #2d3748; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center;">
                <i class="fas fa-chart-line mr-2" style="color: #1877F2;"></i> {{ __('client.quick_stats') }}
            </h4>
            <div class="row">
                <div class="col-sm-6 mb-4">
                    <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 15px; padding: 20px; border-left: 4px solid #1877F2;">
                        <div class="d-flex align-items-center">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #1877F2, #4267B2); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                <i class="fab fa-facebook-f text-white" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 2rem; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $stats['connected_pages'] ?? 1 }}</h3>
                                <p style="color: #718096; margin: 0; font-size: 0.9rem;">{{ __('client.connected_pages') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mb-4">
                    <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 15px; padding: 20px; border-left: 4px solid #42B883;">
                        <div class="d-flex align-items-center">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #42B883, #369970); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                <i class="fas fa-users text-white" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 2rem; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $stats['active_customers'] ?? 15 }}</h3>
                                <p style="color: #718096; margin: 0; font-size: 0.9rem;">{{ __('client.active_customers') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mb-4">
                    <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 15px; padding: 20px; border-left: 4px solid #FF6B6B;">
                        <div class="d-flex align-items-center">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B6B, #EE5A52); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                <i class="fas fa-shopping-cart text-white" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 2rem; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $stats['pending_orders'] ?? 0 }}</h3>
                                <p style="color: #718096; margin: 0; font-size: 0.9rem;">{{ __('client.pending_orders') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mb-4">
                    <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 15px; padding: 20px; border-left: 4px solid #4F46E5;">
                        <div class="d-flex align-items-center">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #4F46E5, #7C3AED); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                <i class="fas fa-cogs text-white" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 2rem; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $stats['upcoming_services'] ?? 0 }}</h3>
                                <p style="color: #718096; margin: 0; font-size: 0.9rem;">{{ __('client.upcoming_services') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="col-lg-4 mb-4">
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
            <h4 style="color: #2d3748; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center;">
                <i class="fas fa-bolt mr-2" style="color: #42B883;"></i> {{ __('client.quick_actions') }}
            </h4>
            <div class="space-y-3">
                <a href="{{ route('client.facebook-pages') }}" class="btn btn-block mb-3" style="background: linear-gradient(135deg, #1877F2, #4267B2); color: white; border-radius: 12px; padding: 12px 20px; border: none; transition: all 0.3s ease; text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(24,119,242,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="fab fa-facebook mr-2"></i> {{ __('client.connect_facebook_page') }}
                </a>
                <a href="{{ route('client.customers') }}" class="btn btn-block mb-3" style="background: linear-gradient(135deg, #42B883, #369970); color: white; border-radius: 12px; padding: 12px 20px; border: none; transition: all 0.3s ease; text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(66,184,131,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="fas fa-users mr-2"></i> {{ __('client.view_customers') }}
                </a>
                <a href="{{ route('client.orders') }}" class="btn btn-block mb-3" style="background: linear-gradient(135deg, #FF6B6B, #EE5A52); color: white; border-radius: 12px; padding: 12px 20px; border: none; transition: all 0.3s ease; text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(255,107,107,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="fas fa-shopping-cart mr-2"></i> {{ __('client.manage_orders') }}
                </a>
                @if($client->isPremium())
                    <a href="#" class="btn btn-block" style="background: linear-gradient(135deg, #4F46E5, #7C3AED); color: white; border-radius: 12px; padding: 12px 20px; border: none; transition: all 0.3s ease; text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(79,70,229,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-robot mr-2"></i> {{ __('client.setup_automation') }}
                    </a>
                @else
                    <a href="#" class="btn btn-block" style="background: linear-gradient(135deg, #e2e8f0, #cbd5e0); color: #718096; border-radius: 12px; padding: 12px 20px; border: none; transition: all 0.3s ease; text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-crown mr-2"></i> {{ __('client.unlock_automation') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Recent Data Tables --}}
<div class="row">
    <div class="col-lg-6 mb-4">
        <div style="background: white; border-radius: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); overflow: hidden;">
            <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 style="color: #2d3748; font-weight: 700; margin: 0; display: flex; align-items: center;">
                        <i class="fas fa-users mr-2" style="color: #42B883;"></i> {{ __('client.recent_customers') }}
                    </h4>
                    <a href="{{ route('client.customers') }}" style="background: linear-gradient(135deg, #42B883, #369970); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        {{ __('client.view_all') }}
                    </a>
                </div>
            </div>
            <div style="padding: 0;">
                <table class="table mb-0" style="border: none;">
                    <thead style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0);">
                        <tr>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.name') }}</th>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.status') }}</th>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.joined') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_customers as $customer)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="border: none; padding: 15px 20px; color: #2d3748; font-weight: 500;">{{ $customer->name ?: __('common.anonymous') }}</td>
                                <td style="border: none; padding: 15px 20px;">
                                    <span style="background: {{ $customer->status === 'active' ? 'linear-gradient(135deg, #48bb78, #38a169)' : 'linear-gradient(135deg, #a0aec0, #718096)' }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                        {{ $customer->status === 'active' ? __('common.active') : __('common.inactive') }}
                                    </span>
                                </td>
                                <td style="border: none; padding: 15px 20px; color: #718096; font-size: 0.9rem;">{{ $customer->created_at->format('M d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="border: none; padding: 40px 20px; text-align: center; color: #a0aec0;">{{ __('client.no_customers_yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div style="background: white; border-radius: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); overflow: hidden;">
            <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 style="color: #2d3748; font-weight: 700; margin: 0; display: flex; align-items: center;">
                        <i class="fas fa-shopping-cart mr-2" style="color: #FF6B6B;"></i> {{ __('client.recent_orders') }}
                    </h4>
                    <a href="{{ route('client.orders') }}" style="background: linear-gradient(135deg, #FF6B6B, #EE5A52); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        {{ __('client.view_all') }}
                    </a>
                </div>
            </div>
            <div style="padding: 0;">
                <table class="table mb-0" style="border: none;">
                    <thead style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0);">
                        <tr>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.order') }} #</th>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.product') }}</th>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.status') }}</th>
                            <th style="border: none; padding: 15px 20px; color: #4a5568; font-weight: 600; font-size: 0.85rem;">{{ __('common.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_orders as $order)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="border: none; padding: 15px 20px; color: #2d3748; font-weight: 600; font-family: monospace;">#{{ $order->order_number }}</td>
                                <td style="border: none; padding: 15px 20px; color: #4a5568;">{{ Str::limit($order->product_name, 20) }}</td>
                                <td style="border: none; padding: 15px 20px;">
                                    <span style="background: {{ $order->status === 'delivered' ? 'linear-gradient(135deg, #48bb78, #38a169)' : ($order->status === 'pending' ? 'linear-gradient(135deg, #ed8936, #dd6b20)' : 'linear-gradient(135deg, #4299e1, #3182ce)') }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                        {{ $order->status === 'delivered' ? __('common.delivered') : ($order->status === 'pending' ? __('common.pending') : __('common.processing')) }}
                                    </span>
                                </td>
                                <td style="border: none; padding: 15px 20px; color: #2d3748; font-weight: 600;">${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="border: none; padding: 40px 20px; text-align: center; color: #a0aec0;">{{ __('client.no_orders_yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendors/count_up/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('vendors/count_up/jquery.waypoints.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.counter').counterUp({
        delay: 10,
        time: 1000
    });
});
</script>
@endpush