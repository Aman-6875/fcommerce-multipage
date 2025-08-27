@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
/* Complete override of all green/teal backgrounds */
.main_content.dashboard_part.large_header_bg,
.main_content.dashboard_part,
.main_content,
section.main_content,
.large_header_bg,
.header_bg,
.dashboard_part {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    background-color: #f8f9fa !important;
    min-height: 100vh;
}

/* Remove any potential pseudo-element backgrounds */
.main_content::before,
.main_content::after,
.dashboard_part::before, 
.dashboard_part::after,
.large_header_bg::before,
.large_header_bg::after {
    display: none !important;
    background: none !important;
}

/* Override any container backgrounds */
.container-fluid,
.main_content_iner,
.main_content_iner > *,
body.crm_body_bg {
    background: transparent !important;
}

/* Override body and main layout backgrounds only */
html, body {
    background: #f8f9fa !important;
}

.dashboard-content {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

/* Specific overrides for known green elements */
.bg-success,
.bg-teal,
.bg-green,
[class*="green"],
[class*="teal"],
[style*="green"],
[style*="teal"],
[style*="#20c997"],
[style*="#198754"] {
    background: transparent !important;
}

/* High specificity overrides for layout sections */
html body .main_content.dashboard_part.large_header_bg,
html body section.main_content.dashboard_part.large_header_bg,
html body .main_content.dashboard_part,
html body section.main_content.dashboard_part {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    background-image: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    background-color: #f8f9fa !important;
}

/* Remove any CSS background images that might be green */
.main_content.dashboard_part.large_header_bg,
.main_content.dashboard_part,
section.main_content {
    background-image: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

/* Force sidebar and main content area backgrounds */
.sidebar,
nav.sidebar {
    background: #ffffff !important;
}

/* Override any remaining layout backgrounds */
.crm_body_bg,
body.crm_body_bg {
    background: #f8f9fa !important;
    background-image: none !important;
}

/* Footer styling - modern design */
.footer_part,
div.footer_part {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    background-color: #667eea !important;
    background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    margin-top: 50px;
    padding: 25px 0;
    border-top: 1px solid rgba(102, 126, 234, 0.1);
    box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
}

.footer_iner {
    padding: 15px 0 !important;
}

.footer_part p {
    color: white !important;
    font-size: 0.95rem;
    font-weight: 500;
    margin: 0 !important;
    opacity: 0.9;
    text-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.footer_part .container-fluid {
    background: transparent !important;
}

/* Footer enhancements */
.footer_part {
    position: relative;
    overflow: hidden;
}

.footer_part::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
    pointer-events: none;
}

.footer_part p {
    position: relative;
    z-index: 2;
    letter-spacing: 0.5px;
    font-family: inherit;
}

/* Add some breathing room above footer */
.main_content_iner {
    padding-bottom: 30px !important;
}

/* Fix any side margin/padding issues that might show green */
.main_content_iner {
    margin: 0 !important;
    padding: 20px 15px !important;
}

/* Ensure full width coverage */
.dashboard-content {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Catch-all for any remaining background issues */
* {
    box-sizing: border-box;
}

/* Specific overrides for layout containers */
section, div, main, article {
    background-color: inherit !important;
}

/* Remove any potential green backgrounds from all elements */
[style*="background: #20c997"],
[style*="background: #198754"],
[style*="background-color: #20c997"],
[style*="background-color: #198754"],
[class*="bg-teal"],
[class*="bg-success"]:not(.stats-card):not(.badge-success) {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
}

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0px;
    color: white;
    margin: -20px -15px 30px -15px;
    padding: 40px 30px;
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    pointer-events: none;
}

.dashboard-title {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 12px;
    text-shadow: 0 3px 6px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
}

.dashboard-subtitle {
    font-size: 1.2rem;
    opacity: 1;
    margin-bottom: 0;
    font-weight: 400;
    text-shadow: 0 2px 4px rgba(0,0,0,0.15);
    position: relative;
    z-index: 2;
    color: rgba(255, 255, 255, 0.95);
}

.dashboard-date {
    font-size: 1rem;
    opacity: 0.9;
    font-weight: 500;
    position: relative;
    z-index: 2;
}

.stats-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    margin-bottom: 25px;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.stats-card-body {
    padding: 25px;
    position: relative;
}

.stats-number {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 8px;
    line-height: 1;
}

.stats-label {
    font-size: 0.95rem;
    font-weight: 500;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 2.5rem;
    opacity: 0.3;
}

.card-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.card-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.card-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.card-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

.quick-stats-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.quick-stats-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #2c3e50;
}

.single-quick-stat {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    border-left: 4px solid #667eea;
}

.single-quick-stat:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.quick-stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.quick-stat-icon i {
    color: white;
    font-size: 1.2rem;
}

.quick-stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.quick-stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

.recent-activities {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f1f1f1;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.activity-icon i {
    color: white;
    font-size: 0.9rem;
}

.activity-content h6 {
    margin-bottom: 3px;
    font-weight: 600;
    color: #2c3e50;
}

.activity-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.clients-table-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0;
}

.search-add-wrapper {
    display: flex;
    gap: 15px;
    align-items: center;
}

.search-input {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 8px 15px;
    font-size: 0.9rem;
    width: 250px;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.1rem rgba(102, 126, 234, 0.25);
}

.btn-add {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.modern-table {
    border: none;
    border-radius: 10px;
    overflow: hidden;
}

.modern-table thead th {
    background: #f8f9fa;
    border: none;
    font-weight: 600;
    color: #2c3e50;
    padding: 15px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody td {
    border: none;
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f1f1;
}

.modern-table tbody tr:hover {
    background: #f8f9fa;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-btn-view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-btn-edit {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.badge-modern {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.8rem;
}

.badge-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.badge-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.badge-secondary { background: linear-gradient(135deg, #6c757d 0%, #9599a4 100%); }
.badge-danger { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }

/* Main Content Area Improvements */
.main_content_iner {
    background: transparent !important;
    padding: 20px 15px !important;
}

/* Container Fluid Improvements */
.container-fluid {
    padding: 0 !important;
}

/* Overall Layout Improvements */
.dashboard-content {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
}

/* Section Spacing */
.dashboard-section {
    margin-bottom: 35px;
}

/* Enhanced Card Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stats-card, .quick-stats-section, .recent-activities, .clients-table-section {
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
}

.stats-card:nth-child(1) { animation-delay: 0.1s; }
.stats-card:nth-child(2) { animation-delay: 0.2s; }
.stats-card:nth-child(3) { animation-delay: 0.3s; }
.stats-card:nth-child(4) { animation-delay: 0.4s; }

.quick-stats-section { animation-delay: 0.5s; }
.recent-activities { animation-delay: 0.6s; }
.clients-table-section { animation-delay: 0.7s; }

/* Improved Hover Effects */
.stats-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* Better Button Styling */
.btn-add {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-add:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .dashboard-header {
        margin: -15px -10px 20px -10px;
        padding: 25px 20px;
    }
    
    .dashboard-title {
        font-size: 2.2rem;
    }
    
    .dashboard-subtitle {
        font-size: 1rem;
    }
    
    .stats-card-body {
        padding: 20px;
    }
    
    .stats-number {
        font-size: 2.2rem;
    }
}

/* Loading Animation for Page */
.dashboard-loader {
    opacity: 0;
    animation: fadeIn 0.5s ease-in-out 0.8s forwards;
}

@keyframes fadeIn {
    to { opacity: 1; }
}
</style>
@endpush

@section('content')
<div class="dashboard-content dashboard-loader">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">{{ __('admin.dashboard') }}</h1>
                <p class="dashboard-subtitle">{{ __('admin.welcome_back') }}</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="dashboard-date">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ date('F j, Y') }}
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); border-radius: 50%;"></div>
    </div>

    <!-- Stats Cards Section -->
    <div class="dashboard-section">
        <div class="row">
    <div class="col-xl-3 col-sm-6">
        <div class="stats-card card-success">
            <div class="stats-card-body text-white">
                <div class="stats-number counter">{{ $stats['total_clients'] ?? 0 }}</div>
                <div class="stats-label">{{ __('admin.total_clients') }}</div>
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="stats-card card-warning">
            <div class="stats-card-body text-white">
                <div class="stats-number counter">{{ $stats['premium_clients'] ?? 0 }}</div>
                <div class="stats-label">{{ __('admin.premium_clients') }}</div>
                <div class="stats-icon">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="stats-card card-info">
            <div class="stats-card-body text-white">
                <div class="stats-number counter">{{ $stats['total_orders'] ?? 0 }}</div>
                <div class="stats-label">{{ __('admin.total_orders') }}</div>
                <div class="stats-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="stats-card card-primary">
            <div class="stats-card-body text-white">
                <div class="stats-number">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                <div class="stats-label">{{ __('admin.total_revenue') }}</div>
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Quick Stats and Recent Activities Section -->
    <div class="dashboard-section">
        <div class="row">
    <div class="col-lg-8">
        <div class="quick-stats-section">
            <h4 class="quick-stats-title">{{ __('admin.todays_overview') }}</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="single-quick-stat d-flex align-items-center">
                        <div class="quick-stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <div class="quick-stat-number counter">{{ $stats['new_clients_today'] ?? 0 }}</div>
                            <div class="quick-stat-label">{{ __('admin.new_clients_today') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="single-quick-stat d-flex align-items-center">
                        <div class="quick-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="quick-stat-number counter">{{ $stats['pending_orders'] ?? 0 }}</div>
                            <div class="quick-stat-label">{{ __('admin.pending_orders') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="single-quick-stat d-flex align-items-center">
                        <div class="quick-stat-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <div class="quick-stat-number counter">{{ $stats['active_services'] ?? 0 }}</div>
                            <div class="quick-stat-label">{{ __('admin.active_services') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="single-quick-stat d-flex align-items-center">
                        <div class="quick-stat-icon">
                            <i class="fab fa-facebook"></i>
                        </div>
                        <div>
                            <div class="quick-stat-number counter">{{ $stats['facebook_pages'] ?? 0 }}</div>
                            <div class="quick-stat-label">{{ __('admin.connected_pages') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="recent-activities">
            <h4 class="quick-stats-title">{{ __('admin.recent_activities') }}</h4>
            <div class="activity-list">
                @forelse($recent_activities ?? [] as $activity)
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-{{ $activity['icon'] }}"></i>
                        </div>
                        <div class="activity-content">
                            <h6>{{ $activity['message'] }}</h6>
                            <div class="activity-time">{{ $activity['time'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                        <p>No recent activities</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Recent Clients Table Section -->
    <div class="dashboard-section">
        <div class="row">
    <div class="col-12">
        <div class="clients-table-section">
            <div class="table-header">
                <h4 class="table-title">{{ __('admin.recent_clients') }}</h4>
                <div class="search-add-wrapper">
                    <input type="text" class="search-input" placeholder="{{ __('admin.search_clients') }}" id="clientSearch">
                    <a href="{{ route('admin.clients.create') }}" class="btn-add">
                        <i class="fas fa-plus me-2"></i>{{ __('admin.add_client') }}
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th>{{ __('common.name') }}</th>
                            <th>{{ __('common.email') }}</th>
                            <th>{{ __('admin.plan') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('admin.joined') }}</th>
                            <th>{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_clients ?? [] as $client)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <div class="avatar-circle" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ strtoupper(substr($client->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-600">{{ $client->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $client->email }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-modern text-white badge-{{ $client->plan_type === 'premium' ? 'success' : ($client->plan_type === 'enterprise' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($client->plan_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-modern text-white badge-{{ $client->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($client->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $client->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.clients.show', $client) }}" class="action-btn action-btn-view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.clients.edit', $client) }}" class="action-btn action-btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">{{ __('admin.no_clients_found') }}</h5>
                                        <p class="text-muted">Start by adding your first client</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
    // Initialize page with fade-in effect
    setTimeout(function() {
        $('.dashboard-loader').addClass('loaded');
    }, 300);
    
    // Animate counters with waypoints
    $('.counter').waypoint(function() {
        $(this.element).counterUp({
            delay: 10,
            time: 2000
        });
    }, {
        offset: '80%',
        triggerOnce: true
    });
    
    // Enhanced search functionality with debouncing
    let searchTimeout;
    $('#clientSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        const value = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(function() {
            $('.modern-table tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });
            
            // Show no results message if needed
            const visibleRows = $('.modern-table tbody tr:visible').length;
            if (visibleRows === 0 && value !== '') {
                if ($('.no-results-row').length === 0) {
                    $('.modern-table tbody').append(
                        '<tr class="no-results-row"><td colspan="6" class="text-center py-4 text-muted">No clients found matching "' + 
                        $('<div>').text(value).html() + '"</td></tr>'
                    );
                }
            } else {
                $('.no-results-row').remove();
            }
        }, 300);
    });
    
    // Enhanced card interactions
    $('.stats-card').hover(
        function() {
            $(this).addClass('shadow-lg');
            $(this).find('.stats-icon').css('opacity', '0.6');
        },
        function() {
            $(this).removeClass('shadow-lg');
            $(this).find('.stats-icon').css('opacity', '0.3');
        }
    );
    
    // Initialize tooltips with custom styling
    $('[title]').tooltip({
        placement: 'top',
        trigger: 'hover',
        template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="background: #333; border-radius: 6px; font-size: 12px;"></div></div>'
    });
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $($(this).attr('href'));
        if(target.length) {
            event.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800, 'easeInOutCubic');
        }
    });
    
    // Enhanced action button interactions
    $('.action-btn').on('click', function(e) {
        const btn = $(this);
        const icon = btn.find('i');
        const originalClass = icon.attr('class');
        
        // Add click ripple effect
        btn.addClass('btn-clicked');
        setTimeout(() => btn.removeClass('btn-clicked'), 300);
        
        // Show loading spinner
        icon.attr('class', 'fas fa-spinner fa-spin');
        
        // Restore original icon
        setTimeout(function() {
            icon.attr('class', originalClass);
        }, 1500);
    });
    
    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        // You can add AJAX call here to refresh stats
        console.log('Auto-refresh stats (implementation needed)');
    }, 300000);
    
    // Add keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + K for search focus
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
            e.preventDefault();
            $('#clientSearch').focus().select();
        }
        
        // Escape to clear search
        if (e.keyCode === 27) {
            $('#clientSearch').val('').trigger('keyup');
        }
    });
    
    // Welcome message with user name
    const currentTime = new Date().getHours();
    let greeting = 'Good morning';
    if (currentTime >= 12 && currentTime < 17) {
        greeting = 'Good afternoon';
    } else if (currentTime >= 17) {
        greeting = 'Good evening';
    }
    
    // Add dynamic greeting if needed
    $('.dashboard-subtitle').each(function() {
        const text = $(this).text();
        if (text.includes('Welcome back!')) {
            $(this).text(text.replace('Welcome back!', greeting + '!'));
        }
    });
    
    // Responsive table handling
    function handleResponsiveTable() {
        if ($(window).width() < 768) {
            $('.modern-table').addClass('table-sm');
        } else {
            $('.modern-table').removeClass('table-sm');
        }
    }
    
    handleResponsiveTable();
    $(window).resize(handleResponsiveTable);
});

// Add custom CSS for clicked effect and final background overrides
$('<style>').text(`
    .btn-clicked {
        transform: scale(0.95) !important;
        transition: transform 0.1s ease !important;
    }
    .loaded {
        opacity: 1 !important;
    }
    
    /* Final background cleanup with highest priority */
    html body .main_content.dashboard_part.large_header_bg,
    html body section.main_content.dashboard_part.large_header_bg {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        background-image: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        background-color: #f8f9fa !important;
    }
`).appendTo('head');

// Force remove any green backgrounds after page load
setTimeout(function() {
    // Main layout backgrounds
    $('.main_content, .dashboard_part, .large_header_bg').css({
        'background': 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)',
        'background-image': 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)',
        'background-color': '#f8f9fa'
    });
    
    // Footer backgrounds - apply modern styling
    $('.footer_part, div.footer_part').css({
        'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'background-color': '#667eea',
        'background-image': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'display': 'block'
    });
    
    // Body and html backgrounds
    $('html, body').css({
        'background': '#f8f9fa',
        'background-color': '#f8f9fa',
        'background-image': 'none'
    });
    
    // Remove any elements that might have green/teal backgrounds
    $('[style*="green"], [style*="teal"], [style*="#20c997"], [style*="#198754"]').css({
        'background': 'transparent',
        'background-color': 'transparent',
        'background-image': 'none'
    });
    
    // Force any side areas or containers to have clean backgrounds
    $('.container-fluid, .main_content_iner').css({
        'background': 'transparent',
        'background-color': 'transparent',
        'background-image': 'none'
    });
    
    // Ensure footer text is visible and styled properly
    $('.footer_part p').css({
        'color': 'white',
        'text-shadow': '0 1px 3px rgba(0,0,0,0.2)',
        'font-weight': '500'
    });
}, 500);
</script>
@endpush