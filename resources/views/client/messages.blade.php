@extends('layouts.client')

@section('title', __('Messages'))

@push('styles')
<style>
    /* Override the main layout background */
    .main_content_iner.overly_inner {
        background: #f8f9fa !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .messaging-wrapper {
        background: #f8f9fa;
        min-height: calc(100vh - 160px);
        padding: 20px;
        margin: -20px -30px;
    }
    
    @media (max-width: 991.98px) {
        .messaging-wrapper {
            padding-left: 20px;
        }
    }
    
    .messaging-container {
        display: flex;
        height: calc(100vh - 200px);
        max-width: 1400px;
        margin: 0 auto;
        gap: 20px;
        overflow: hidden;
        position: relative;
    }
    
    .customers-column {
        width: 350px;
        flex-shrink: 0;
        transition: transform 0.3s ease;
        position: relative;
        z-index: 2;
    }
    
    .messages-column {
        flex: 1;
        min-width: 0;
        position: relative;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .messaging-container {
            gap: 0;
        }
        
        .customers-column {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            background: #f8f9fa;
            transform: translateX(0);
            z-index: 10;
        }
        
        .customers-column.hide-mobile {
            transform: translateX(-100%);
        }
        
        .messages-column {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 5;
        }
        
        .messages-column.show-mobile {
            transform: translateX(0);
        }
    }
    
    .customers-sidebar {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .customers-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 18px 24px;
        border-radius: 16px 16px 0 0;
        flex-shrink: 0;
    }
    
    .customers-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }
    
    .customer-search {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        flex-shrink: 0;
    }
    
    .customer-search input {
        border-radius: 25px;
        padding: 10px 18px;
        border: 1px solid #ddd;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .customer-search input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .customers-list {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
    
    .customer-item {
        padding: 15px 18px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        position: relative;
    }
    
    .customer-item:hover {
        background: #f8f9fa;
        transform: translateX(3px);
    }
    
    .customer-item.active {
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        border-left: 4px solid #667eea;
    }
    
    .customer-avatar {
        position: relative;
        margin-right: 15px;
    }
    
    .customer-avatar img, .customer-avatar .avatar-placeholder {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .customer-avatar .avatar-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .online-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: #4caf50;
        border: 2px solid white;
        border-radius: 50%;
    }
    
    .new-message-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: #ff4757;
        border: 2px solid white;
        border-radius: 50%;
        animation: pulse-red 2s infinite;
    }
    
    @keyframes pulse-red {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .customer-with-unread {
        background: #f8f9ff !important;
        border-left: 4px solid #667eea !important;
    }
    
    .customer-with-unread:hover {
        background: #f0f2ff !important;
    }
    
    .font-weight-bold {
        font-weight: 600 !important;
        color: #333 !important;
    }
    
    .customer-info {
        flex: 1;
    }
    
    .customer-name {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 4px;
        color: #333;
    }
    
    .customer-last-message {
        color: #666;
        font-size: 13px;
        margin-bottom: 2px;
    }
    
    .customer-time {
        color: #999;
        font-size: 11px;
    }
    
    .unread-badge {
        background: #667eea;
        color: white;
        border-radius: 15px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
        min-width: 20px;
        text-align: center;
    }
    
    .messages-container {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .messages-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
        min-height: 70px;
    }
    
    .back-button {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white !important;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .back-button:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }
    
    .back-button:focus {
        outline: none;
        background: rgba(255,255,255,0.3);
    }
    
    @media (max-width: 768px) {
        .back-button {
            display: flex !important;
            visibility: visible !important;
            background: rgba(255,255,255,0.4) !important;
            border: 2px solid rgba(255,255,255,0.6) !important;
            opacity: 1 !important;
            z-index: 1000 !important;
        }
        
        .back-button i {
            color: white !important;
            font-size: 18px !important;
        }
        
        .messages-header {
            padding: 12px 15px !important;
        }
        
        .customer-info-header {
            width: 100%;
        }
    }
    
    /* Force back button visibility on all screen sizes for testing */
    .back-button {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .customer-info-header {
        display: flex;
        align-items: center;
    }
    
    .customer-info-header img, .customer-info-header .avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 12px;
    }
    
    .customer-info-header .avatar-placeholder {
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .customer-details {
        flex: 1;
    }
    
    .customer-details h6 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
        color: white;
    }
    
    .customer-details small {
        opacity: 0.9;
        color: white;
        font-size: 13px;
        display: block;
        margin-top: 2px;
    }
    
    .typing-indicator {
        background: rgba(255,255,255,0.1);
        padding: 5px 15px;
        border-radius: 15px;
        font-size: 12px;
        display: none;
    }
    
    .messages-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        min-height: 0;
        max-height: calc(100vh - 340px);
    }
    
    
    .message-item {
        margin-bottom: 20px;
        display: flex;
        animation: messageSlideIn 0.3s ease-out;
    }
    
    @keyframes messageSlideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .message-item.outgoing {
        justify-content: flex-end;
    }
    
    .message-bubble {
        max-width: 70%;
        padding: 12px 18px;
        border-radius: 18px;
        position: relative;
        word-wrap: break-word;
    }
    
    .message-bubble.incoming {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-bottom-left-radius: 4px;
    }
    
    .message-bubble.outgoing {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .message-content {
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 4px;
    }
    
    .message-meta {
        font-size: 11px;
        opacity: 0.7;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .message-status {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .messages-footer {
        background: #fff;
        border-top: 1px solid #eee;
        padding: 15px 20px;
        flex-shrink: 0;
    }
    
    .message-input-container {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        background: #fff;
        padding: 15px;
        border-radius: 25px;
        border: 2px solid #e0e0e0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .message-input-container:focus-within {
        border-color: #667eea;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
    }
    
    .message-input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        padding: 12px 15px;
        font-size: 14px;
        resize: none;
        min-height: 24px;
        max-height: 120px;
        line-height: 1.4;
        font-family: inherit;
    }
    
    .message-input::placeholder {
        color: #999;
    }
    
    .send-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .send-button:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .send-button:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .no-selection {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        color: #666;
    }
    
    .no-selection i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .loading-spinner {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .connection-status {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 15px;
        border-radius: 8px;
        color: white;
        font-size: 12px;
        z-index: 1000;
        display: none;
    }
    
    .connection-status.online {
        background: #4caf50;
    }
    
    .connection-status.offline {
        background: #f44336;
    }
    
    /* Scrollbar Styling */
    .customers-list::-webkit-scrollbar,
    .messages-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .customers-list::-webkit-scrollbar-track,
    .messages-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .customers-list::-webkit-scrollbar-thumb,
    .messages-body::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 3px;
    }
    
    .customers-list::-webkit-scrollbar-thumb:hover,
    .messages-body::-webkit-scrollbar-thumb:hover {
        background: #5a67d8;
    }
    
    /* Notification animations */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Message character counter */
    .character-counter {
        position: absolute;
        bottom: 5px;
        right: 15px;
        font-size: 11px;
        color: #999;
        pointer-events: none;
    }
    
    .character-counter.warning {
        color: #ffc107;
    }
    
    .character-counter.danger {
        color: #dc3545;
    }
    
    /* Global Message Toast Notifications */
    .message-toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        pointer-events: none;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }
    
    .message-toast {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        border: 1px solid rgba(0,0,0,0.1);
        min-width: 320px;
        max-width: 380px;
        margin-bottom: 15px;
        pointer-events: auto;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: slideInFromRight 0.4s ease-out;
        position: relative;
        overflow: hidden;
        z-index: 100000;
    }
    
    .message-toast:hover {
        transform: translateX(-3px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.18);
    }
    
    .message-toast::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .message-toast-header {
        display: flex;
        align-items: center;
        padding: 15px 20px 10px 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .message-toast-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 12px;
        flex-shrink: 0;
        position: relative;
    }
    
    .message-toast-avatar img, .message-toast-avatar .avatar-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .message-toast-avatar .avatar-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }
    
    .message-toast-avatar::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 16px;
        height: 16px;
        background: #4caf50;
        border: 3px solid white;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .message-toast-info {
        flex: 1;
        min-width: 0;
    }
    
    .message-toast-name {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .message-toast-time {
        font-size: 11px;
        color: #999;
    }
    
    .message-toast-close {
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        margin-left: 10px;
        flex-shrink: 0;
    }
    
    .message-toast-close:hover {
        background: #f0f0f0;
        color: #666;
    }
    
    .message-toast-body {
        padding: 12px 20px 15px 20px;
    }
    
    .message-toast-content {
        color: #555;
        font-size: 13px;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .message-toast-action {
        padding: 10px 20px 15px 20px;
        border-top: 1px solid #f0f0f0;
        background: #fafafa;
    }
    
    .message-toast-reply-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .message-toast-reply-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    @keyframes slideInFromRight {
        from {
            opacity: 0;
            transform: translateX(100px) scale(0.8);
        }
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }
    
    .message-toast.removing {
        animation: slideOutToRight 0.3s ease-in forwards;
    }
    
    @keyframes slideOutToRight {
        to {
            opacity: 0;
            transform: translateX(100px) scale(0.8);
        }
    }
    
    /* Facebook Messenger icon */
    .messenger-icon {
        color: #0078ff;
        font-size: 12px;
    }
    
    /* Toast responsive positioning */
    @media (max-width: 768px) {
        .message-toast-container {
            top: 10px;
            right: 10px;
            left: 10px;
            right: 10px;
        }
        
        .message-toast {
            min-width: auto;
            max-width: 100%;
            margin: 0 auto 15px auto;
        }
    }
    
    @media (max-width: 480px) {
        .message-toast {
            margin: 0 0 15px 0;
        }
        
        .message-toast-container {
            left: 5px;
            right: 5px;
        }
    }
</style>
@endpush

@section('content')
<div class="messaging-wrapper">
    <div class="messaging-container">
        <!-- Customers Sidebar -->
        <div class="customers-column">
            <div class="customers-sidebar">
                <div class="customers-header">
                    <h5 id="customers-count">{{ __('Messages') }} (0)</h5>
                </div>
                
                <div class="customer-search">
                    <input type="text" class="form-control" id="customer-search" placeholder="Search customers...">
                </div>
                
                <div class="customers-list" id="customers-list">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="messages-column">
            <div class="messages-container">
                <div id="no-selection" class="no-selection">
                    <i class="fas fa-comments"></i>
                    <h5>Select a customer to view messages</h5>
                    <p>Choose a customer from the sidebar to start viewing and sending messages</p>
                </div>
                
                <div id="messages-interface" style="display: none; height: 100%; flex-direction: column;">
                    
                    <!-- EMERGENCY HEADER - ALWAYS VISIBLE -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; display: flex; align-items: center; min-height: 70px; flex-shrink: 0; border-radius: 16px 16px 0 0;">
                        <button onclick="goBackToCustomers()" style="background: rgba(255,255,255,0.3); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; cursor: pointer; font-size: 16px;">
                            <i class="fas fa-arrow-left" style="color: white;"></i>
                        </button>
                        <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 12px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div style="flex: 1;">
                            <h6 id="customer-name-display" style="color: white; margin: 0; font-size: 16px; font-weight: 600;">Facebook User</h6>
                            <small id="customer-info-display" style="color: white; opacity: 0.9; font-size: 13px; display: block; margin-top: 2px;">Facebook Messenger</small>
                        </div>
                    </div>
                    
                    <!-- Original Messages Header - HIDDEN (backup only) -->
                    <div class="messages-header" id="chat-header" style="display: none !important; visibility: hidden !important;">
                        <div class="customer-info-header">
                            <button class="back-button" id="back-to-customers-backup">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div id="selected-customer-avatar">
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="customer-details">
                                <h6 id="selected-customer-name">Loading Customer...</h6>
                                <small id="selected-customer-info">Please wait...</small>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button id="sound-toggle" class="sound-toggle sound-on" onclick="toggleSoundNotifications()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;" title="Sound notifications on - Click to disable">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <div class="typing-indicator" id="typing-indicator" style="display: none;">
                                <i class="fas fa-circle-notch fa-spin"></i> Typing...
                            </div>
                        </div>
                    </div>

                    <!-- Messages Body -->
                    <div class="messages-body" id="messages-body">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="messages-footer">
                        <!-- Quick Actions Row -->
                        <div class="quick-actions-row mb-3" id="quick-actions" style="display: none;">
                            <button class="btn btn-sm btn-outline-primary me-2" id="send-products-btn" onclick="openProductModal()">
                                <i class="fas fa-shopping-bag"></i> Send Products
                            </button>
                            <button class="btn btn-sm btn-outline-info me-2" id="send-services-btn" onclick="openServiceModal()">
                                <i class="fas fa-wrench"></i> Send Services
                            </button>
                            <button class="btn btn-sm btn-outline-success" id="create-order-btn" onclick="openCreateOrderModal()">
                                <i class="fas fa-plus-circle"></i> Create Order
                            </button>
                        </div>
                        
                        <div class="message-input-container" style="position: relative;">
                            <textarea 
                                id="message-input" 
                                class="message-input" 
                                placeholder="Type a message..." 
                                rows="1"
                                maxlength="1000"
                            ></textarea>
                            <div id="character-counter" class="character-counter">0/1000</div>
                            <button id="send-button" class="send-button" type="button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connection Status -->
<div id="connection-status" class="connection-status">
    <i class="fas fa-wifi"></i> Connected
</div>

<!-- Global Message Toast Notifications -->
<div id="message-toast-container" class="message-toast-container">
    <!-- Toast notifications will be dynamically inserted here -->
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Configuration
    const config = {
        updateInterval: {{ env('MESSAGES_AUTO_UPDATE_INTERVAL', 30000) }},
        loadLimit: {{ env('MESSAGES_LOAD_LIMIT', 100) }},
        typingIndicator: {{ env('MESSAGES_TYPING_INDICATOR', 'true') ? 'true' : 'false' }}
    };
    
    selectedCustomerId = {{ $selectedCustomer ? $selectedCustomer->id : 'null' }}; // Use global variable
    let updateInterval;
    let lastSeenMessages = {}; // Track last seen message ID for each customer
    let soundEnabled = true;
    
    // CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initialize
    loadCustomers();
    loadSoundPreference(); // Load sound preference on page load
    
    if (selectedCustomerId) {
        console.log('Initial customer selected:', selectedCustomerId);
        setTimeout(() => selectCustomer(selectedCustomerId), 500); // Small delay to ensure DOM is ready
    } else {
        console.log('No initial customer selected');
    }
    
    // Initialize last seen messages from existing data (prevent initial notifications)
    setTimeout(() => {
        $.get('/client/api/customers')
            .done(function(response) {
                if (response.success) {
                    response.customers.forEach(customer => {
                        if (customer.last_message) {
                            lastSeenMessages[customer.id] = customer.last_message.id;
                        }
                    });
                    console.log('Initialized last seen messages:', lastSeenMessages);
                }
            });
    }, 1000);
    
    // Auto-update interval
    function startAutoUpdate() {
        if (updateInterval) clearInterval(updateInterval);
        
        updateInterval = setInterval(() => {
            if (selectedCustomerId && !isLoading) {
                loadMessages(selectedCustomerId, true);
            }
            loadCustomers(true);
        }, config.updateInterval);
    }
    
    startAutoUpdate();
    
    // Load customers
    function loadCustomers(silent = false) {
        $.get('/client/api/customers')
            .done(function(response) {
                if (response.success) {
                    // Check for new messages before updating the list
                    checkForNewMessages(response.customers);
                    
                    updateCustomersList(response.customers);
                    $('#customers-count').text(`{{ __('Messages') }} (${response.customers.length})`);
                    
                    if (!silent) {
                        showConnectionStatus('online');
                    }
                }
            })
            .fail(function() {
                if (!silent) {
                    showConnectionStatus('offline');
                }
            });
    }
    
    // Check for new messages and handle notifications
    function checkForNewMessages(customers) {
        let hasNewMessages = false;
        let newMessageCount = 0;
        
        customers.forEach(customer => {
            if (customer.last_message && customer.last_message.type === 'incoming') {
                const messageId = customer.last_message.id;
                const lastSeenId = lastSeenMessages[customer.id] || 0;
                
                // If this is a new incoming message we haven't seen before
                if (messageId > lastSeenId) {
                    hasNewMessages = true;
                    newMessageCount++;
                    
                    // Update the last seen message ID for this customer
                    lastSeenMessages[customer.id] = messageId;
                    
                    console.log(`New message from ${customer.name}: ${customer.last_message.content}`);
                }
            }
        });
        
        // If we have new messages, play sound and update UI
        if (hasNewMessages) {
            // Play notification sound
            if (soundEnabled) {
                playNotificationSound();
            }
            
            // Update page title with new message count
            updatePageTitle(newMessageCount);
            
            // Show a subtle notification in browser
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(`New message${newMessageCount > 1 ? 's' : ''} received`, {
                    body: newMessageCount > 1 
                        ? `You have ${newMessageCount} new messages` 
                        : customers.find(c => c.last_message && c.last_message.type === 'incoming')?.last_message.content.substring(0, 50) + '...',
                    icon: '/favicon.ico',
                    tag: 'new-messages'
                });
            }
            
            console.log(`${newMessageCount} new message(s) received`);
        }
    }
    
    // Update customers list
    function updateCustomersList(customersData) {
        // Store customers data in global variable for modal access
        customers = customersData;
        const customersList = $('#customers-list');
        
        if (customersData.length === 0) {
            customersList.html(`
                <div class="text-center p-4">
                    <i class="fas fa-users mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="text-muted">No customers yet</p>
                    <small class="text-muted">Customers will appear here when they message your Facebook pages</small>
                </div>
            `);
            return;
        }
        
        // Sort customers: unread messages first, then by last interaction
        const sortedCustomers = customersData.sort((a, b) => {
            // First priority: customers with unread messages
            if (a.unread_count > 0 && b.unread_count === 0) return -1;
            if (b.unread_count > 0 && a.unread_count === 0) return 1;
            
            // Second priority: most recent interaction
            if (a.last_interaction && b.last_interaction) {
                return new Date(b.last_interaction) - new Date(a.last_interaction);
            }
            if (a.last_interaction && !b.last_interaction) return -1;
            if (b.last_interaction && !a.last_interaction) return 1;
            
            return 0;
        });
        
        let html = '';
        sortedCustomers.forEach(customer => {
            const isActive = selectedCustomerId == customer.id ? 'active' : '';
            const hasUnread = customer.unread_count > 0;
            const avatarHtml = customer.profile_picture 
                ? `<img src="${customer.profile_picture}" alt="Profile">`
                : `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`;
            
            const lastMessage = customer.last_message 
                ? `<div class="customer-last-message ${hasUnread ? 'font-weight-bold' : ''}">${customer.last_message.content}</div>`
                : '<div class="customer-last-message">No messages yet</div>';
            
            const unreadBadge = hasUnread 
                ? `<div class="unread-badge">${customer.unread_count}</div>`
                : '';
            
            // Add pulse animation for customers with unread messages
            const pulseClass = hasUnread ? 'customer-with-unread' : '';
            
            html += `
                <div class="customer-item ${isActive} ${pulseClass}" data-customer-id="${customer.id}">
                    <div class="customer-avatar">
                        ${avatarHtml}
                        ${hasUnread ? '<div class="new-message-indicator"></div>' : '<div class="online-indicator"></div>'}
                    </div>
                    <div class="customer-info">
                        <div class="customer-name ${hasUnread ? 'font-weight-bold' : ''}">${customer.name}</div>
                        ${lastMessage}
                        <div class="customer-time">${customer.last_interaction || 'Never'}</div>
                    </div>
                    ${unreadBadge}
                </div>
            `;
        });
        
        customersList.html(html);
    }
    
    // Customer click handler
    $(document).on('click', '.customer-item', function() {
        const customerId = $(this).data('customer-id');
        selectCustomer(customerId);
        
        // Handle mobile view transition
        if (isMobile()) {
            $('.customers-column').addClass('hide-mobile');
            $('.messages-column').addClass('show-mobile');
        }
        
        // Update URL without page refresh
        window.history.pushState({}, '', `/client/messages/${customerId}`);
    });
    
    // Select customer
    function selectCustomer(customerId) {
        console.log('Selecting customer:', customerId); // Debug log
        selectedCustomerId = customerId;
        isLoading = true;
        
        // Show quick actions for the selected customer
        $('#quick-actions').show();
        
        // Update active state
        $('.customer-item').removeClass('active');
        $(`.customer-item[data-customer-id="${customerId}"]`).addClass('active');
        
        // Show loading
        $('#no-selection').hide();
        $('#messages-interface').show().css({
            'display': 'flex',
            'height': '100%',
            'flex-direction': 'column'
        });
        $('#messages-body').html('<div class="loading-spinner"><div class="spinner"></div></div>');
        
        // The emergency header is always visible, no need to show backup
        $('#customer-name-display').text('Loading Customer...');
        $('#customer-info-display').text('Please wait...');
        
        console.log('Emergency header should be visible now');
        
        loadMessages(customerId);
    }
    
    // Load messages
    function loadMessages(customerId, update = false) {
        const url = `/client/api/messages/${customerId}`;
        const params = update && lastMessageId > 0 ? `?last_message_id=${lastMessageId}` : '';
        
        $.get(url + params)
            .done(function(response) {
                if (response.success) {
                    if (!update) {
                        updateCustomerHeader(response.customer);
                        displayMessages(response.messages);
                    } else if (response.messages.length > 0) {
                        appendNewMessages(response.messages);
                    }
                    
                    if (response.last_message_id > lastMessageId) {
                        lastMessageId = response.last_message_id;
                    }
                    
                    isLoading = false;
                    showConnectionStatus('online');
                }
            })
            .fail(function() {
                isLoading = false;
                showConnectionStatus('offline');
                $('#messages-body').html('<div class="text-center p-4 text-danger">Failed to load messages</div>');
            });
    }
    
    // Update customer header
    function updateCustomerHeader(customer) {
        console.log('Updating customer header:', customer);
        
        const avatarHtml = customer.profile_picture 
            ? `<img src="${customer.profile_picture}" alt="Profile">`
            : `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`;
        
        $('#selected-customer-avatar').html(avatarHtml);
        $('#selected-customer-name').text(customer.name || 'Unknown Customer');
        
        let infoText = '';
        if (customer.phone) infoText += `<i class="fas fa-phone"></i> ${customer.phone} `;
        if (customer.email) infoText += `<i class="fas fa-envelope"></i> ${customer.email} `;
        if (customer.page_name) infoText += `<i class="fab fa-facebook"></i> ${customer.page_name}`;
        
        if (!infoText) infoText = 'Facebook Messenger';
        
        $('#selected-customer-info').html(infoText);
        
        // Update EMERGENCY header
        $('#customer-name-display').text(customer.name || 'Unknown Customer');
        $('#customer-info-display').html(infoText);
    }
    
    function showConnectionStatus(status) {
        const statusEl = $('#connection-status');
        statusEl.removeClass('online offline').addClass(status);
        statusEl.text(status === 'online' ? 'Connected' : 'Disconnected');
        if (status === 'online') {
            setTimeout(() => statusEl.hide(), 2000);
        }
    }
    
    // Display messages
    function displayMessages(messages) {
        if (messages.length === 0) {
            $('#messages-body').html(`
                <div class="text-center p-4">
                    <i class="fas fa-comments mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="text-muted">No messages yet</p>
                    <small class="text-muted">Start the conversation!</small>
                </div>
            `);
            return;
        }
        
        let html = '';
        messages.forEach(message => {
            html += createMessageHtml(message);
        });
        
        $('#messages-body').html(html);
        scrollToBottom();
    }
    
    // Append new messages
    function appendNewMessages(messages) {
        messages.forEach(message => {
            $('#messages-body').append(createMessageHtml(message));
        });
        scrollToBottom();
    }
    
    // Create message HTML
    function createMessageHtml(message) {
        const isOutgoing = message.type === 'outgoing';
        const bubbleClass = isOutgoing ? 'outgoing' : 'incoming';
        
        // Determine status icon based on message state
        let statusIcon = 'fa-check';
        let statusColor = '#999';
        let statusTitle = 'Sent';
        
        if (isOutgoing) {
            if (message.is_read) {
                statusIcon = 'fa-check-double';
                statusColor = '#4CAF50';
                statusTitle = 'Read';
            } else {
                statusIcon = 'fa-check';
                statusColor = '#999';
                statusTitle = 'Sent';
            }
        }
        
        return `
            <div class="message ${bubbleClass}">
                <div class="message-bubble">
                    ${escapeHtml(message.content)}
                    <div class="message-meta">
                        <span class="message-time">${message.formatted_time || message.time}</span>
                        ${isOutgoing ? `<i class="fas ${statusIcon} message-status" style="color: ${statusColor};" title="${statusTitle}"></i>` : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    function scrollToBottom() {
        const messagesBody = $('#messages-body');
        messagesBody.scrollTop(messagesBody[0].scrollHeight);
    }
    
    // Show notification to user
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.notification').remove();
        
        const typeIcons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle', 
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const typeColors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        
        const notification = $(`
            <div class="notification ${type}" style="
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                border: 1px solid ${typeColors[type]};
                border-left: 4px solid ${typeColors[type]};
                border-radius: 8px;
                padding: 15px 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
            ">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas ${typeIcons[type]}" style="color: ${typeColors[type]}; font-size: 18px;"></i>
                    <span style="flex: 1; color: #333; font-size: 14px;">${escapeHtml(message)}</span>
                    <button onclick="$(this).parent().parent().remove()" style="
                        background: none;
                        border: none;
                        color: #999;
                        cursor: pointer;
                        font-size: 16px;
                        padding: 0;
                        margin-left: 10px;
                    ">&times;</button>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-remove after delay
        const delay = type === 'error' ? 5000 : 3000;
        setTimeout(() => {
            notification.fadeOut(300, () => notification.remove());
        }, delay);
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Create message HTML
    function createMessageHtml(message) {
        const isOutgoing = message.type === 'outgoing';
        const bubbleClass = isOutgoing ? 'outgoing' : 'incoming';
        
        // Determine status icon based on message state
        let statusIcon = 'fa-check';
        let statusColor = '#999';
        let statusTitle = 'Sent';
        
        if (isOutgoing) {
            if (message.status === 'pending') {
                statusIcon = 'fa-clock';
                statusColor = '#ffc107';
                statusTitle = 'Sending...';
            } else if (message.status === 'failed' || (message.facebook_sent === false && message.status !== 'pending')) {
                statusIcon = 'fa-exclamation-triangle';
                statusColor = '#dc3545';
                statusTitle = 'Failed to send';
            } else if (message.delivered || message.facebook_sent) {
                statusIcon = 'fa-check-double';
                statusColor = '#28a745';
                statusTitle = 'Delivered';
            } else {
                statusIcon = 'fa-check';
                statusColor = '#6c757d';
                statusTitle = 'Sent';
            }
        }
        
        const statusHtml = isOutgoing 
            ? `<div class="message-status" title="${statusTitle}">
                 <i class="fas ${statusIcon}" style="color: ${statusColor};"></i>
               </div>`
            : '';
        
        return `
            <div class="message-item ${message.type}" data-message-id="${message.id}">
                <div class="message-bubble ${bubbleClass}">
                    <div class="message-content">${escapeHtml(message.content)}</div>
                    <div class="message-meta">
                        <span class="message-time">${message.time}</span>
                        ${statusHtml}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Send message
    function sendMessage() {
        if (!selectedCustomerId) return;
        
        const messageInput = $('#message-input');
        const message = messageInput.val().trim();
        
        if (!message) {
            showNotification('Please enter a message', 'warning');
            return;
        }
        
        if (message.length > 1000) {
            showNotification('Message is too long (max 1000 characters)', 'error');
            return;
        }
        
        const sendButton = $('#send-button');
        sendButton.prop('disabled', true);
        
        // Show sending indicator
        sendButton.html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Add message to UI immediately with pending status
        const tempMessage = {
            id: 'temp-' + Date.now(),
            content: message,
            type: 'outgoing',
            time: new Date().toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            }),
            delivered: false,
            status: 'pending',
            facebook_sent: false
        };
        
        $('#messages-body').append(createMessageHtml(tempMessage));
        scrollToBottom();
        messageInput.val('');
        
        // Auto-resize textarea back to normal
        messageInput.css('height', 'auto');
        
        // Send to server
        $.post(`/client/api/messages/${selectedCustomerId}`, {
            message: message
        })
        .done(function(response) {
            if (response.success) {
                // Replace temp message with real one
                $(`[data-message-id="${tempMessage.id}"]`).replaceWith(createMessageHtml(response.message));
                lastMessageId = Math.max(lastMessageId, response.message.id);
                
                // Show feedback based on Facebook sending status
                if (response.facebook_sent) {
                    showNotification('Message sent to Facebook successfully!', 'success');
                } else if (response.facebook_error) {
                    showNotification(`Message saved but Facebook delivery failed: ${response.facebook_error}`, 'warning');
                } else {
                    showNotification('Message saved (Facebook integration not configured)', 'info');
                }
            } else {
                $(`[data-message-id="${tempMessage.id}"]`).remove();
                showNotification(response.message || 'Failed to send message', 'error');
            }
        })
        .fail(function(xhr) {
            $(`[data-message-id="${tempMessage.id}"]`).remove();
            const errorMsg = xhr.responseJSON?.message || 'Failed to send message';
            showNotification(errorMsg, 'error');
            console.error('Send message error:', xhr);
        })
        .always(function() {
            sendButton.prop('disabled', false);
            sendButton.html('<i class="fas fa-paper-plane"></i>');
        });
    }
    
    // Back to customers handler (mobile)
    $(document).on('click', '#back-to-customers', function() {
        $('.customers-column').removeClass('hide-mobile');
        $('.messages-column').removeClass('show-mobile');
        
        // Update URL
        window.history.pushState({}, '', '/client/messages');
    });
    
    // Emergency back button function (global)
    window.goBackToCustomers = function() {
        $('.customers-column').removeClass('hide-mobile');
        $('.messages-column').removeClass('show-mobile');
        window.history.pushState({}, '', '/client/messages');
        console.log('Emergency back button clicked');
    };
    
    // Event handlers
    $('#send-button').on('click', sendMessage);
    
    $('#message-input').on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Auto-resize textarea and update character counter
    $('#message-input').on('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        
        // Update character counter
        const length = $(this).val().length;
        const counter = $('#character-counter');
        counter.text(`${length}/1000`);
        
        // Update counter styling
        counter.removeClass('warning danger');
        if (length > 900) {
            counter.addClass('danger');
        } else if (length > 700) {
            counter.addClass('warning');
        }
        
        // Update send button state
        const sendButton = $('#send-button');
        if (length > 1000) {
            sendButton.prop('disabled', true);
            sendButton.css('opacity', '0.5');
        } else {
            sendButton.prop('disabled', false);
            sendButton.css('opacity', '1');
        }
    });
    
    // Customer search
    $('#customer-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.customer-item').each(function() {
            const customerName = $(this).find('.customer-name').text().toLowerCase();
            $(this).toggle(customerName.includes(searchTerm));
        });
    });
    
    // Format message time consistently
    function formatMessageTime() {
        return new Date().toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
    
    function showConnectionStatus(status) {
        const statusEl = $('#connection-status');
        statusEl.removeClass('online offline').addClass(status);
        statusEl.html(`<i class="fas fa-wifi"></i> ${status === 'online' ? 'Connected' : 'Disconnected'}`);
        statusEl.show();
        
        setTimeout(() => statusEl.hide(), 2000);
    }
    
    // Show notification to user
    
    // Check if we're on mobile device
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Create message toast notification
    function createMessageToast(customer, message) {
        const toastId = 'toast-' + customer.id + '-' + message.id;
        const avatarHtml = customer.profile_picture 
            ? `<img src="${customer.profile_picture}" alt="Profile">`
            : `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`;
            
        const messagePreview = message.content.length > 80 
            ? message.content.substring(0, 80) + '...' 
            : message.content;
            
        return `
            <div class="message-toast" id="${toastId}" data-customer-id="${customer.id}">
                <div class="message-toast-header">
                    <div class="message-toast-avatar">
                        ${avatarHtml}
                    </div>
                    <div class="message-toast-info">
                        <h6 class="message-toast-name">
                            ${escapeHtml(customer.name)}
                            <i class="fab fa-facebook-messenger messenger-icon" title="Facebook Messenger"></i>
                        </h6>
                        <div class="message-toast-time">Just now</div>
                    </div>
                    <button class="message-toast-close" onclick="dismissToast('${toastId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="message-toast-body">
                    <p class="message-toast-content">${escapeHtml(messagePreview)}</p>
                </div>
                <div class="message-toast-action">
                    <button class="message-toast-reply-btn" onclick="openConversation(${customer.id})">
                        <i class="fas fa-reply"></i> Reply
                    </button>
                </div>
            </div>
        `;
    }
    
    // Show message toast notification
    function showMessageToast(customer, message) {
        // Don't show toast if user is already viewing this customer's conversation
        if (selectedCustomerId == customer.id && document.hasFocus()) {
            return;
        }
        
        const container = $('#message-toast-container');
        const toast = $(createMessageToast(customer, message));
        
        // Add click handler for entire toast
        toast.on('click', function(e) {
            if (!$(e.target).closest('.message-toast-close, .message-toast-reply-btn').length) {
                openConversation(customer.id);
            }
        });
        
        container.append(toast);
        
        // Auto-dismiss after 8 seconds
        setTimeout(() => {
            dismissToast(toast.attr('id'));
        }, 8000);
        
        // Play notification sound
        if (soundEnabled) {
            playNotificationSound();
        }
        
        // Update page title to show new message indicator
        updatePageTitle(true);
        
        console.log('Toast shown for message from', customer.name);
    }
    
    // Dismiss toast notification
    function dismissToast(toastId) {
        const toast = $('#' + toastId);
        if (toast.length) {
            toast.addClass('removing');
            setTimeout(() => {
                toast.remove();
                
                // Reset page title if no more toasts
                if ($('.message-toast').length === 0) {
                    updatePageTitle(false);
                }
            }, 300);
        }
    }
    
    // Open conversation (navigate to messages page)
    function openConversation(customerId) {
        // Dismiss any related toasts
        $(`.message-toast[data-customer-id="${customerId}"]`).each(function() {
            dismissToast($(this).attr('id'));
        });
        
        // Navigate to messages page
        window.location.href = `/client/messages/${customerId}`;
    }
    
    // Play notification sound
    function playNotificationSound() {
        try {
            // Create a pleasant notification beep using Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            // Create a pleasant two-tone notification sound
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e) {
            console.log('Could not play notification sound:', e);
        }
    }
    
    // Toggle sound notifications
    function toggleSoundNotifications() {
        soundEnabled = !soundEnabled;
        const soundButton = $('#sound-toggle');
        if (soundEnabled) {
            soundButton.removeClass('sound-off').addClass('sound-on');
            soundButton.html('<i class="fas fa-volume-up"></i>');
            soundButton.attr('title', 'Sound notifications on - Click to disable');
            showNotification('Sound notifications enabled', 'success');
        } else {
            soundButton.removeClass('sound-on').addClass('sound-off');
            soundButton.html('<i class="fas fa-volume-mute"></i>');
            soundButton.attr('title', 'Sound notifications off - Click to enable');
            showNotification('Sound notifications disabled', 'info');
        }
        
        // Store preference in localStorage
        localStorage.setItem('messageSoundEnabled', soundEnabled);
    }
    
    // Load sound preference from localStorage
    function loadSoundPreference() {
        const stored = localStorage.getItem('messageSoundEnabled');
        if (stored !== null) {
            soundEnabled = stored === 'true';
        }
        
        const soundButton = $('#sound-toggle');
        if (soundButton.length) {
            if (soundEnabled) {
                soundButton.removeClass('sound-off').addClass('sound-on');
                soundButton.html('<i class="fas fa-volume-up"></i>');
                soundButton.attr('title', 'Sound notifications on - Click to disable');
            } else {
                soundButton.removeClass('sound-on').addClass('sound-off');
                soundButton.html('<i class="fas fa-volume-mute"></i>');
                soundButton.attr('title', 'Sound notifications off - Click to enable');
            }
        }
    }
    
    // Update page title with new message indicator
    function updatePageTitle(hasNewMessages) {
        const baseTitle = document.title.replace(/^\(\d+\) /, '');
        if (hasNewMessages) {
            const count = $('.message-toast').length;
            document.title = `(${count}) ${baseTitle}`;
        } else {
            document.title = baseTitle;
        }
    }
    
    // Test function for debugging toast notifications
    function testToastNotification() {
        showMessageToast({
            id: 999,
            name: 'Test Customer',
            profile_picture: null
        }, {
            id: 9999,
            content: 'This is a test message to check if toast notifications are working properly!',
            time: 'Just now'
        });
    }
    
    // Global functions for accessing from anywhere
    window.dismissToast = dismissToast;
    window.openConversation = openConversation;
    window.toggleSoundNotifications = toggleSoundNotifications;
    window.showMessageToast = showMessageToast;
    window.playNotificationSound = playNotificationSound;
    window.testToastNotification = testToastNotification; // For debugging
    
    // Handle window resize for mobile responsiveness
    $(window).on('resize', function() {
        if (!isMobile()) {
            // Reset mobile classes on desktop view
            $('.customers-column').removeClass('hide-mobile');
            $('.messages-column').removeClass('show-mobile');
        }
    });
    
    // Initial mobile check
    if (isMobile() && selectedCustomerId) {
        $('.customers-column').addClass('hide-mobile');
        $('.messages-column').addClass('show-mobile');
    }
    
    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
});

// Global Variables
let currentFacebookPageId = null;
let selectedProducts = [];
const maxProductSelection = 3;
let selectedCustomerId = null;
let customers = [];
let lastMessageId = 0;
let isLoading = false;

function openProductModal() {
        if (!selectedCustomerId) {
            showNotification('Please select a customer first', 'warning');
            return;
        }
        
        // Get customer data and find their primary page customer relationship
        const customer = customers.find(c => c.id == selectedCustomerId);
        if (!customer) {
            showNotification('Customer not found', 'error');
            return;
        }
        
        // Get Facebook page ID - support both PageCustomer architecture and backward compatibility
        let facebookPageId = null;
        
        // First try PageCustomer relationships (new architecture)
        if (customer.page_customers && customer.page_customers.length > 0) {
            const activePageCustomer = customer.page_customers
                .filter(pc => pc.status === 'active' && pc.facebook_page)
                .sort((a, b) => new Date(b.last_interaction) - new Date(a.last_interaction))[0];
            
            if (activePageCustomer && activePageCustomer.facebook_page) {
                facebookPageId = activePageCustomer.facebook_page.id;
            }
        }
        
        // Fallback to direct facebook_page relationship (backward compatibility)
        if (!facebookPageId && customer.facebook_page && customer.facebook_page.id) {
            facebookPageId = customer.facebook_page.id;
        }
        
        if (!facebookPageId) {
            showNotification('Customer Facebook page not found. This customer may not have a Facebook page associated.', 'error');
            console.log('Customer data:', customer); // Debug log
            return;
        }
        
        currentFacebookPageId = facebookPageId;
        $('#productModalTitle').text(`Send Products to ${customer.name}`);
        $('#productModal').modal('show');
        loadProductsForModal('', '');
    }

function openServiceModal() {
    // Simple fallback notification since showNotification is in document ready block
    alert('Services modal coming soon!');
}

// Global notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    $('.notification').remove();
    
    const typeIcons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle', 
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const typeColors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    const notification = $(`
        <div class="notification ${type}" style="
            position: fixed;
            top: 80px;
            right: 20px;
            background: white;
            border: 1px solid ${typeColors[type]};
            border-left: 4px solid ${typeColors[type]};
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1060;
            min-width: 300px;
            max-width: 400px;
            word-wrap: break-word;
        ">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="color: ${typeColors[type]};">
                    <i class="fas ${typeIcons[type]} me-2"></i>
                    ${message}
                </div>
                <button type="button" style="border: none; background: none; font-size: 18px; cursor: pointer; color: #999;" onclick="$(this).closest('.notification').remove();">
                    ×
                </button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.fadeOut(() => {
            notification.remove();
        });
    }, 5000);
}

// Order creation modal functions
function openCreateOrderModal() {
    if (!selectedCustomerId) {
        showNotification('Please select a customer first', 'warning');
        return;
    }
    
    // Get customer data
    const customer = customers.find(c => c.id == selectedCustomerId);
    if (!customer) {
        showNotification('Customer not found', 'error');
        return;
    }
    
    // Reset form
    $('#createOrderForm')[0].reset();
    $('#productRows').empty();
    productRowCounter = 0;
    
    // Pre-fill customer information
    $('#customer_name').val(customer.name || '');
    $('#customer_phone').val(customer.phone || '');
    $('#customer_email').val(customer.email || '');
    $('#customer_address').val(customer.address || '');
    
    // Set default shipping charge
    $('#shipping_charge').val('60');
    
    // Load customer dropdown options
    loadCustomersForDropdown();
    
    // Show modal
    $('#createOrderModal').modal('show');
}

// Order Management JavaScript (from orders page)
let productRowCounter = 0;

function loadCustomersForDropdown() {
    const customerSelect = $('#customer_id');
    customerSelect.empty().append('<option value="">Select customer or create new</option>');
    
    customers.forEach(customer => {
        const option = $(`<option value="${customer.id}" 
                           data-name="${customer.name || ''}" 
                           data-phone="${customer.phone || ''}" 
                           data-email="${customer.email || ''}"
                           data-address="${customer.address || ''}">
                           ${customer.name} - ${customer.phone}
                       </option>`);
        customerSelect.append(option);
    });
    
    // Pre-select current customer if available
    if (selectedCustomerId) {
        customerSelect.val(selectedCustomerId);
    }
}

function addProductRow() {
    productRowCounter++;
    
    // Get customer's Facebook page ID
    const customer = customers.find(c => c.id == selectedCustomerId);
    if (!customer) {
        alert('Customer not found');
        return;
    }
    
    // Get Facebook page ID - support both PageCustomer architecture and backward compatibility
    let facebookPageId = null;
    
    // First try PageCustomer relationships (new architecture)
    if (customer.page_customers && customer.page_customers.length > 0) {
        const activePageCustomer = customer.page_customers
            .filter(pc => pc.status === 'active' && pc.facebook_page)
            .sort((a, b) => new Date(b.last_interaction) - new Date(a.last_interaction))[0];
        
        if (activePageCustomer && activePageCustomer.facebook_page) {
            facebookPageId = activePageCustomer.facebook_page.id;
        }
    }
    
    // Fallback to direct facebook_page relationship (backward compatibility)
    if (!facebookPageId && customer.facebook_page && customer.facebook_page.id) {
        facebookPageId = customer.facebook_page.id;
    }
    
    if (!facebookPageId) {
        alert('Customer Facebook page not found. Cannot load products.');
        return;
    }
    
    // Get products from the API (page-specific)
    $.get(`/client/api/products/${facebookPageId}`)
        .done(function(response) {
            let products = response.data || [];
            
            let productOptions = '<option value="">Select Product</option>';
            products.forEach(product => {
                const price = product.sale_price || product.price;
                productOptions += `<option value="${product.id}" data-price="${price}" data-name="${product.name}">${product.name} - ৳${price}</option>`;
            });
            
            const row = `
                <tr id="productRow${productRowCounter}">
                    <td>
                        <select class="form-control product-select" name="products[${productRowCounter}][product_id]" data-row="${productRowCounter}" required>
                            ${productOptions}
                        </select>
                        <input type="hidden" class="product-name" name="products[${productRowCounter}][product_name]">
                    </td>
                    <td>
                        <input type="number" class="form-control product-quantity" name="products[${productRowCounter}][quantity]" 
                               data-row="${productRowCounter}" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" class="form-control product-price" name="products[${productRowCounter}][unit_price]" 
                               data-row="${productRowCounter}" min="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" class="form-control product-discount" name="products[${productRowCounter}][discount_amount]" 
                               data-row="${productRowCounter}" min="0" step="0.01" value="0">
                    </td>
                    <td>
                        <span class="product-total" id="productTotal${productRowCounter}">৳0.00</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductRow(${productRowCounter})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#productRows').append(row);
            
            // Bind events to new row
            bindProductRowEvents(productRowCounter);
        })
        .fail(function() {
            alert('Failed to load products');
        });
}

function bindProductRowEvents(rowId) {
    // Product selection
    $(document).on('change', `[data-row="${rowId}"].product-select`, function() {
        const selectedOption = $(this).find(':selected');
        const price = selectedOption.data('price') || 0;
        const name = selectedOption.data('name') || '';
        
        $(`[data-row="${rowId}"].product-price`).val(price);
        $(`[data-row="${rowId}"] .product-name`).val(name);
        calculateProductTotal(rowId);
    });
    
    // Quantity, price, discount changes
    $(document).on('input', `[data-row="${rowId}"].product-quantity, [data-row="${rowId}"].product-price, [data-row="${rowId}"].product-discount`, function() {
        calculateProductTotal(rowId);
    });
}

function removeProductRow(rowId) {
    $(`#productRow${rowId}`).remove();
    calculateOrderTotal();
}

function calculateProductTotal(rowId) {
    const quantity = parseFloat($(`[data-row="${rowId}"].product-quantity`).val()) || 0;
    const price = parseFloat($(`[data-row="${rowId}"].product-price`).val()) || 0;
    const discount = parseFloat($(`[data-row="${rowId}"].product-discount`).val()) || 0;
    
    const total = Math.max(0, (quantity * price) - discount);
    $(`#productTotal${rowId}`).text('৳' + total.toFixed(2));
    
    calculateOrderTotal();
}

function calculateOrderTotal() {
    let subtotal = 0;
    
    // Calculate products subtotal
    $('.product-total').each(function() {
        const amount = parseFloat($(this).text().replace('৳', '')) || 0;
        subtotal += amount;
    });
    
    // Get other values
    const shipping = parseFloat($('#shipping_charge').val()) || 0;
    const advance = parseFloat($('#advance_payment').val()) || 0;
    const discountAmount = parseFloat($('#order_discount').val()) || 0;
    const discountType = $('#discount_type').val();
    
    // Calculate order discount
    let orderDiscount = 0;
    if (discountType === 'percentage') {
        orderDiscount = (subtotal * discountAmount) / 100;
    } else {
        orderDiscount = Math.min(discountAmount, subtotal);
    }
    
    const finalSubtotal = subtotal - orderDiscount;
    const total = finalSubtotal + shipping;
    const remaining = total - advance;
    
    // Update display
    $('#orderSubtotal').text('৳' + subtotal.toFixed(2));
    $('#shippingAmount').text('৳' + shipping.toFixed(2));
    $('#totalAmount').text('৳' + total.toFixed(2));
    
    // Show/hide discount row
    if (orderDiscount > 0) {
        $('#orderDiscountRow').show();
        $('#orderDiscountAmount').text('-৳' + orderDiscount.toFixed(2));
    } else {
        $('#orderDiscountRow').hide();
    }
    
    // Show/hide advance payment rows
    if (advance > 0) {
        $('#advancePaymentRow, #remainingRow').show();
        $('#advanceAmount').text('৳' + advance.toFixed(2));
        $('#remainingAmount').text('৳' + remaining.toFixed(2));
    } else {
        $('#advancePaymentRow, #remainingRow').hide();
    }
}

function createOrderFromChat() {
    const formData = new FormData(document.getElementById('createOrderForm'));
    
    // Basic validation
    if (!formData.get('customer_info[name]') || !formData.get('customer_info[phone]')) {
        alert('Please fill required fields: Customer name and phone');
        return;
    }
    
    // Check if at least one product is added
    if ($('#productRows tr').length === 0) {
        alert('Please add at least one product');
        return;
    }
    
    // Add existing customer ID to prevent duplicate customer creation
    if (selectedCustomerId) {
        formData.append('customer_id', selectedCustomerId);
    }
    
    // Add facebook page ID (required for page-specific orders)
    const customer = customers.find(c => c.id == selectedCustomerId);
    let facebookPageId = null;
    
    // Get Facebook page ID - support both PageCustomer architecture and backward compatibility
    if (customer.page_customers && customer.page_customers.length > 0) {
        const activePageCustomer = customer.page_customers
            .filter(pc => pc.status === 'active' && pc.facebook_page)
            .sort((a, b) => new Date(b.last_interaction) - new Date(a.last_interaction))[0];
        
        if (activePageCustomer && activePageCustomer.facebook_page) {
            facebookPageId = activePageCustomer.facebook_page.id;
        }
    }
    
    // Fallback to direct facebook_page relationship (backward compatibility)
    if (!facebookPageId && customer.facebook_page && customer.facebook_page.id) {
        facebookPageId = customer.facebook_page.id;
    }
    
    if (!facebookPageId) {
        alert('Customer Facebook page not found. Cannot create order.');
        return;
    }
    
    formData.append('facebook_page_id', facebookPageId);
    
    const createButton = $('button[onclick="createOrderFromChat()"]');
    const originalText = createButton.html();
    createButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
    
    $.ajax({
        url: '/client/orders',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#createOrderModal').modal('hide');
            showNotification('Order created successfully!', 'success');
            
            // Auto-send invoice if order was created successfully
            if (response.success && response.order_id) {
                console.log('Order created successfully, sending invoice for order:', response.order_id);
                
                // Check if customer has facebook_user_id before trying to send invoice
                const customer = customers.find(c => c.id == selectedCustomerId);
                if (!customer || !customer.facebook_user_id) {
                    console.log('Customer missing facebook_user_id:', customer);
                    showNotification('Order created successfully! Note: Cannot send invoice automatically - customer has no Facebook user ID. You can send it manually.', 'warning');
                    return;
                }
                
                sendInvoiceToCustomer(response.order_id);
            } else {
                console.log('Order creation response:', response);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Validation errors:\n';
                Object.keys(errors).forEach(key => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                alert(errorMessage);
            } else {
                const errorMsg = xhr.responseJSON?.message || 'Failed to create order';
                showNotification(errorMsg, 'error');
            }
        },
        complete: function() {
            createButton.prop('disabled', false).html(originalText);
        }
    });
}

function sendInvoiceToCustomer(orderId) {
    console.log('Sending invoice for order ID:', orderId);
    
    $.ajax({
        url: `/client/orders/${orderId}/send-invoice`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Invoice response:', response);
            
            if (response.success) {
                showNotification('Invoice sent to customer via message!', 'success');
                
                // Refresh messages to show the sent invoice
                setTimeout(() => {
                    if (selectedCustomerId) {
                        // Reload the page to show updated messages
                        window.location.reload();
                    }
                }, 1000);
            } else {
                console.log('Invoice sending failed:', response.message);
                showNotification('Order created but failed to send invoice: ' + (response.message || 'Unknown error'), 'warning');
            }
        },
        error: function(xhr) {
            console.log('Invoice AJAX error:', xhr.responseJSON);
            const errorMsg = xhr.responseJSON?.message || 'Failed to send invoice';
            showNotification('Order created but failed to send invoice: ' + errorMsg, 'warning');
        }
    });
}


// Product Modal Functions (Global scope)
function loadProductsForModal(search = '', category = '') {
    if (!currentFacebookPageId) return;
    
    $('#productModalLoading').show();
    $('#productModalContent').hide();
    
    $.get(`/client/products/modal/${currentFacebookPageId}`, {
        search: search,
        category: category
    })
    .done(function(response) {
        renderProductsList(response.products);
        renderCategoriesDropdown(response.categories);
        $('#productModalLoading').hide();
        $('#productModalContent').show();
    })
    .fail(function(xhr) {
        $('#productModalLoading').hide();
        showNotification('Failed to load products', 'error');
        console.error('Product loading error:', xhr.responseJSON);
    });
}

function renderProductsList(products) {
    const container = $('#productsList');
    container.empty();
    
    if (products.length === 0) {
        container.html('<div class="text-center text-muted py-4">No products found</div>');
        return;
    }
    
    products.forEach(function(product) {
        const isSelected = selectedProducts.find(p => p.id === product.id);
        const effectivePrice = product.sale_price || product.price;
        const formattedPrice = '৳' + Number(effectivePrice).toLocaleString();
        
        const productHtml = `
            <div class="product-item border rounded p-3 mb-3 ${isSelected ? 'selected' : ''}" data-product-id="${product.id}">
                <div class="row align-items-center">
                    <div class="col-2">
                        ${product.image_url ? 
                            `<img src="${product.image_url}" class="img-fluid rounded" style="max-height: 50px;">` : 
                            `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;"><i class="fas fa-image text-muted"></i></div>`
                        }
                    </div>
                    <div class="col-7">
                        <h6 class="mb-1">${product.name}</h6>
                        <small class="text-muted">SKU: ${product.sku || 'N/A'} | Stock: ${product.stock_quantity}</small>
                        <div class="mt-1">
                            <span class="badge badge-secondary">${product.category || 'Uncategorized'}</span>
                        </div>
                    </div>
                    <div class="col-2 text-end">
                        <strong class="text-primary">${formattedPrice}</strong>
                        ${product.sale_price ? '<br><small class="text-muted"><s>৳' + Number(product.price).toLocaleString() + '</s></small>' : ''}
                    </div>
                    <div class="col-1">
                        <div class="form-check">
                            <input class="form-check-input product-checkbox" type="checkbox" 
                                   value="${product.id}" ${isSelected ? 'checked' : ''}
                                   ${selectedProducts.length >= maxProductSelection && !isSelected ? 'disabled' : ''}>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(productHtml);
    });
}

function renderCategoriesDropdown(categories) {
    const dropdown = $('#categoryFilter');
    dropdown.empty().append('<option value="">All Categories</option>');
    
    categories.forEach(function(category) {
        dropdown.append(`<option value="${category}">${category}</option>`);
    });
}

function updateProductSelection() {
    const count = selectedProducts.length;
    $('#selectedProductsCount').text(`${count}/${maxProductSelection} selected`);
    $('#sendSelectedProducts').prop('disabled', count === 0);
    
    // Enable/disable checkboxes based on limit
    $('.product-checkbox:not(:checked)').prop('disabled', count >= maxProductSelection);
}

function sendSelectedProducts() {
    if (selectedProducts.length === 0) {
        showNotification('Please select at least one product', 'warning');
        return;
    }
    
    const productIds = selectedProducts.map(p => p.id);
    
    $('#sendSelectedProducts').prop('disabled', true).text('Sending...');
    
    $.post(`/client/messages/${selectedCustomerId}/send-products`, {
        product_ids: productIds,
        facebook_page_id: currentFacebookPageId
    })
    .done(function(response) {
        $('#productModal').modal('hide');
        showNotification('Products sent successfully!', 'success');
        // Refresh messages to show the sent product carousel
        loadMessages(selectedCustomerId);
    })
    .fail(function(xhr) {
        showNotification('Failed to send products', 'error');
        console.error('Send products error:', xhr.responseJSON);
    })
    .always(function() {
        $('#sendSelectedProducts').prop('disabled', false).text('Send Selected Products');
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize product modal event handlers
$(document).ready(function() {
    // Product selection handling
    $(document).on('change', '.product-checkbox', function() {
        const productId = parseInt($(this).val());
        const isChecked = $(this).is(':checked');
        const productItem = $(this).closest('.product-item');
        
        if (isChecked) {
            if (selectedProducts.length >= maxProductSelection) {
                $(this).prop('checked', false);
                showNotification(`You can select maximum ${maxProductSelection} products`, 'warning');
                return;
            }
            
            // Get product data from the DOM
            const productData = {
                id: productId,
                name: productItem.find('h6').text(),
                // Store other needed data
            };
            
            selectedProducts.push(productData);
            productItem.addClass('selected');
        } else {
            selectedProducts = selectedProducts.filter(p => p.id !== productId);
            productItem.removeClass('selected');
        }
        
        // Update counter and disable other checkboxes if limit reached
        updateProductSelection();
    });
    
    // Search and filter handlers
    $('#productSearch').on('input', debounce(function() {
        const search = $(this).val();
        const category = $('#categoryFilter').val();
        loadProductsForModal(search, category);
    }, 300));
    
    $('#categoryFilter').on('change', function() {
        const search = $('#productSearch').val();
        const category = $(this).val();
        loadProductsForModal(search, category);
    });
    
    // Reset modal when closed
    $('#productModal').on('hidden.bs.modal', function() {
        selectedProducts = [];
        currentFacebookPageId = null;
        $('#productSearch').val('');
        $('#categoryFilter').val('');
        $('#productsList').empty();
        updateProductSelection();
    });
    
    // Order modal event handlers
    $('#createOrderModal').on('shown.bs.modal', function() {
        if ($('#productRows').children().length === 0) {
            addProductRow();
        }
    });
    
    // Handle customer selection in order modal
    $('#customer_id').change(function() {
        const selectedOption = $(this).find(':selected');
        if (selectedOption.val()) {
            $('#customer_name').val(selectedOption.data('name'));
            $('#customer_phone').val(selectedOption.data('phone'));
            $('#customer_email').val(selectedOption.data('email'));
            $('#customer_address').val(selectedOption.data('address'));
        }
    });
    
    // Calculate totals when values change in order modal
    $(document).on('input', '#shipping_charge, #advance_payment, #order_discount', calculateOrderTotal);
    $('#discount_type').change(calculateOrderTotal);
    
    // Reset order modal when closed
    $('#createOrderModal').on('hidden.bs.modal', function() {
        $('#createOrderForm')[0].reset();
        $('#productRows').empty();
        productRowCounter = 0;
        $('#orderSubtotal, #shippingAmount, #totalAmount').text('৳0.00');
        $('#orderDiscountRow, #advancePaymentRow, #remainingRow').hide();
    });
});
</script>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Select Products (Max 3)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="productModalLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading products...</p>
                </div>
                
                <!-- Main Content -->
                <div id="productModalContent" style="display: none;">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="productSearch" placeholder="Search products by name, SKU...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="categoryFilter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="badge badge-info" id="selectedProductsCount">0/3 selected</span>
                        </div>
                    </div>
                    
                    <!-- Products List -->
                    <div id="productsList" style="max-height: 400px; overflow-y: auto;">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendSelectedProducts" onclick="sendSelectedProducts()" disabled>
                    Send Selected Products
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createOrderModal" tabindex="-1" role="dialog" aria-labelledby="createOrderModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="max-height: 95vh;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; position: sticky; top: 0; z-index: 1050;">
                <h5 class="modal-title" id="createOrderModalLabel" style="font-weight: 600;">
                    <i class="fas fa-plus-circle me-2"></i>Create New Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: calc(95vh - 120px); overflow-y: auto; padding: 25px;">
                <form id="createOrderForm">
                    @csrf
                    
                    <!-- Products Section - MOVED TO TOP -->
                    <div class="row mt-0 mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0" style="font-weight: 600;">
                                            <i class="fas fa-boxes me-2"></i>Products <span class="text-warning">*</span>
                                        </h6>
                                        <button type="button" class="btn btn-light btn-sm" onclick="addProductRow()">
                                            <i class="fas fa-plus me-1"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="productsTable">
                                            <thead style="background: #f8f9fa;">
                                                <tr>
                                                    <th width="30%" style="border: none;">Product</th>
                                                    <th width="15%" style="border: none;">Quantity</th>
                                                    <th width="15%" style="border: none;">Unit Price</th>
                                                    <th width="15%" style="border: none;">Discount</th>
                                                    <th width="15%" style="border: none;">Total</th>
                                                    <th width="10%" style="border: none;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productRows">
                                                <!-- Product rows will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Customer Information -->
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0" style="background: #f8f9fa;">
                                <div class="card-header" style="background: #e9ecef; border: none;">
                                    <h6 class="mb-0" style="color: #495057; font-weight: 600;">
                                        <i class="fas fa-user me-2"></i>Customer Information
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="form-group mb-3">
                                        <label for="customer_id" class="form-label">Existing Customer</label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="">Select customer or create new</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_info[name]" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="customer_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="customer_phone" name="customer_info[phone]" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="customer_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="customer_email" name="customer_info[email]">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-0">
                                        <label for="customer_address" class="form-label">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="customer_address" name="customer_info[address]" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment & Order Information -->
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0" style="background: #f8f9fa;">
                                <div class="card-header" style="background: #e9ecef; border: none;">
                                    <h6 class="mb-0" style="color: #495057; font-weight: 600;">
                                        <i class="fas fa-credit-card me-2"></i>Payment Method & Notes
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="form-group mb-3">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method">
                                            <option value="cod" selected>Cash on Delivery</option>
                                            <option value="online">Online Payment</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Order notes, special instructions, etc..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pricing Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; border: none;">
                                    <h6 class="mb-0" style="font-weight: 600;">
                                        <i class="fas fa-calculator me-2"></i>Shipping, Discount & Advance Payment
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="shipping_charge" class="form-label">Shipping Charge (৳)</label>
                                                <input type="number" class="form-control" id="shipping_charge" name="shipping_charge" min="0" step="0.01" value="60">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="discount_type" class="form-label">Discount Type</label>
                                                <select class="form-select" id="discount_type" name="discount_type">
                                                    <option value="fixed">Fixed Amount</option>
                                                    <option value="percentage">Percentage</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="order_discount" class="form-label">Order Discount</label>
                                                <input type="number" class="form-control" id="order_discount" name="discount_amount" min="0" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="advance_payment" class="form-label">Advance Payment (৳)</label>
                                                <input type="number" class="form-control" id="advance_payment" name="advance_payment" min="0" step="0.01" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6;">
                                <div class="card-header" style="background: #343a40; color: white; border: none;">
                                    <h6 class="mb-0" style="font-weight: 600;">
                                        <i class="fas fa-calculator me-2"></i>Order Summary
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <table class="table table-sm mb-0" style="background: white; border-radius: 8px;">
                                                <tbody>
                                                    <tr>
                                                        <td style="border: none; padding: 8px 15px;"><strong>Subtotal:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="orderSubtotal">৳0.00</strong></td>
                                                    </tr>
                                                    <tr id="orderDiscountRow" style="display: none;">
                                                        <td style="border: none; padding: 8px 15px;"><strong>Order Discount:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="orderDiscountAmount" class="text-danger">-৳0.00</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border: none; padding: 8px 15px;"><strong>Shipping:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="shippingAmount">৳0.00</strong></td>
                                                    </tr>
                                                    <tr style="background: #e3f2fd;">
                                                        <td style="border: none; padding: 12px 15px; border-top: 2px solid #2196f3;"><strong>Total Amount:</strong></td>
                                                        <td style="border: none; padding: 12px 15px; border-top: 2px solid #2196f3;" class="text-end"><strong id="totalAmount" class="text-primary" style="font-size: 1.1em;">৳0.00</strong></td>
                                                    </tr>
                                                    <tr id="advancePaymentRow" style="display: none; background: #e8f5e8;">
                                                        <td style="border: none; padding: 8px 15px;"><strong>Advance Payment:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="advanceAmount" class="text-success">৳0.00</strong></td>
                                                    </tr>
                                                    <tr id="remainingRow" style="display: none; background: #fff3cd;">
                                                        <td style="border: none; padding: 8px 15px;"><strong>Remaining Amount:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="remainingAmount" class="text-warning">৳0.00</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border: none; padding: 20px 25px;">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            All required fields must be filled
                        </small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="createOrderFromChat()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-save me-1"></i> Create Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-item {
    cursor: pointer;
    transition: all 0.3s ease;
}

.product-item:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-item.selected {
    background-color: #e3f2fd;
    border-color: #2196f3 !important;
}

.quick-actions-row .btn {
    transition: all 0.3s ease;
}

.quick-actions-row .btn:hover {
    transform: translateY(-1px);
}

/* Order Modal Styling */
.modal-xl {
    max-width: 95% !important;
    width: 95% !important;
}

@media (min-width: 1200px) {
    .modal-xl {
        max-width: 1200px !important;
        width: 1200px !important;
    }
}

@media (min-width: 992px) and (max-width: 1199px) {
    .modal-xl {
        max-width: 90% !important;
        width: 90% !important;
    }
}

@media (max-width: 991px) {
    .modal-xl {
        max-width: 95% !important;
        width: 95% !important;
        margin: 10px auto !important;
    }
    
    .modal-content {
        min-height: auto !important;
        max-height: 90vh !important;
    }
    
    .modal-body {
        max-height: calc(90vh - 120px) !important;
    }
}

/* Form improvements for order modal */
#createOrderModal .form-select, 
#createOrderModal .form-control {
    border-radius: 8px !important;
    border: 1px solid #e0e0e0 !important;
    padding: 10px 15px !important;
    font-size: 14px !important;
}

#createOrderModal .form-select:focus, 
#createOrderModal .form-control:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

#createOrderModal .form-label {
    font-weight: 600 !important;
    color: #495057 !important;
    margin-bottom: 8px !important;
}

/* Product table improvements for order modal */
#createOrderModal #productsTable input, 
#createOrderModal #productsTable select {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 13px;
    width: 100%;
}

#createOrderModal #productsTable .table-responsive {
    border-radius: 8px;
}

#createOrderModal #productsTable th {
    font-weight: 600;
    color: #495057;
    background: #f8f9fa !important;
    padding: 12px 8px;
}

#createOrderModal #productsTable .product-total {
    font-weight: 600;
    color: #28a745;
    font-size: 14px;
}

/* Card improvements for order modal */
#createOrderModal .card {
    border-radius: 12px !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08) !important;
}

#createOrderModal .card-header {
    border-radius: 12px 12px 0 0 !important;
}

/* Animation improvements for order modal */
#createOrderModal.modal.fade .modal-dialog {
    transition: transform 0.4s ease-out, opacity 0.3s ease-out;
    transform: scale(0.9) translateY(-50px);
}

#createOrderModal.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Scrollbar styling for order modal */
#createOrderModal .modal-body::-webkit-scrollbar {
    width: 6px;
}

#createOrderModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#createOrderModal .modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#createOrderModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>
@endpush