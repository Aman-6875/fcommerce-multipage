<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Deletion Request - {{ config('app.name') }}</title>
    @include('components.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4">Data Deletion Request</h1>
                <p class="text-muted">Request deletion of your personal data from our Facebook messaging platform</p>

                <div class="alert alert-info mt-4">
                    <strong>Facebook Platform Requirement:</strong> This page allows you to request deletion of any data collected through our Facebook integration.
                </div>

                <div class="mt-4">
                    <h2>What Data We Collect</h2>
                    <p>When you interact with businesses using our Facebook Messenger automation, we may collect:</p>
                    <ul>
                        <li>Your Facebook profile information (name, profile picture, User ID)</li>
                        <li>Messages you send to connected business pages</li>
                        <li>Contact information you voluntarily share (phone, email, address)</li>
                        <li>Order and service request details</li>
                        <li>Interaction preferences and history</li>
                    </ul>

                    <h2>How to Request Data Deletion</h2>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Option 1: Automatic Deletion</h5>
                                </div>
                                <div class="card-body">
                                    <p>Fill out the form below for automatic processing:</p>
                                    
                                    <form action="{{ route('data-deletion.submit') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="facebook_user_id" class="form-label">Facebook User ID</label>
                                            <input type="text" class="form-control" id="facebook_user_id" name="facebook_user_id" placeholder="Your Facebook User ID" required>
                                            <div class="form-text">Find this in your Facebook profile URL or ask the business.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number (Optional)</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+880-XXXX-XXXX">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="business_pages" class="form-label">Business Pages You Interacted With</label>
                                            <textarea class="form-control" id="business_pages" name="business_pages" rows="3" placeholder="List the Facebook page names you messaged..."></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="deletion_reason" class="form-label">Reason for Deletion (Optional)</label>
                                            <select class="form-select" name="deletion_reason">
                                                <option value="">Select a reason...</option>
                                                <option value="privacy_concerns">Privacy concerns</option>
                                                <option value="no_longer_needed">Service no longer needed</option>
                                                <option value="account_closure">Closing Facebook account</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="confirm_deletion" name="confirm_deletion" required>
                                            <label class="form-check-label" for="confirm_deletion">
                                                I confirm that I want all my data deleted from this platform. This action cannot be undone.
                                            </label>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-danger">Submit Deletion Request</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">Option 2: Manual Request</h5>
                                </div>
                                <div class="card-body">
                                    <p>Contact us directly for manual processing:</p>
                                    
                                    <div class="mb-3">
                                        <strong>Email:</strong> info@softstation.xyz
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Subject:</strong> "Data Deletion Request - Facebook Platform"
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Include:</strong>
                                        <ul>
                                            <li>Your Facebook User ID</li>
                                            <li>Email address used</li>
                                            <li>Business pages you messaged</li>
                                            <li>Approximate date range of interactions</li>
                                        </ul>
                                    </div>
                                    
                                    <a href="mailto:info@softstation.xyz?subject=Data Deletion Request - Facebook Platform" class="btn btn-outline-primary">Send Email Request</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-5">What Happens Next?</h2>
                    <div class="timeline">
                        <div class="alert alert-light">
                            <strong>Step 1:</strong> We receive your deletion request
                        </div>
                        <div class="alert alert-light">
                            <strong>Step 2:</strong> We verify your identity and locate your data (1-3 business days)
                        </div>
                        <div class="alert alert-light">
                            <strong>Step 3:</strong> We permanently delete all your data from our systems (within 30 days)
                        </div>
                        <div class="alert alert-light">
                            <strong>Step 4:</strong> We send you a confirmation email when deletion is complete
                        </div>
                    </div>

                    <h2>Data Retention Policy</h2>
                    <ul>
                        <li><strong>Personal Data:</strong> Deleted immediately upon request</li>
                        <li><strong>Message History:</strong> Deleted from all systems and backups</li>
                        <li><strong>Analytics Data:</strong> Anonymized or deleted</li>
                        <li><strong>Legal Requirements:</strong> Some data may be retained if required by law</li>
                    </ul>

                    <h2>Important Notes</h2>
                    <div class="alert alert-warning">
                        <ul class="mb-0">
                            <li>Data deletion is permanent and cannot be undone</li>
                            <li>You will no longer receive automated responses from businesses using our platform</li>
                            <li>Deletion applies only to data collected through our Facebook integration</li>
                            <li>Businesses may have separate data retention policies for their own records</li>
                            <li>Processing may take up to 30 days as required by data protection laws</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <h3>Contact Information</h3>
                        <p>For questions about data deletion:</p>
                        <ul>
                            <li><strong>Email:</strong> info@softstation.xyz</li>
                            <li><strong>Address:</strong> Wet Shewrapara, Mirpur, Dhaka</li>
                            <li><strong>Phone:</strong> +880-1925013478</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>