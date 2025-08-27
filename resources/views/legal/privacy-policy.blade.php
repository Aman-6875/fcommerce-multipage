<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('app.name') }}</title>
    @include('components.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="mb-4">Privacy Policy</h1>
                <p class="text-muted">Last updated: {{ now()->format('F d, Y') }}</p>

                <div class="mt-4">
                    <h2>1. Information We Collect</h2>
                    <p>When you use our Facebook messaging automation service, we may collect:</p>
                    <ul>
                        <li><strong>Facebook Profile Information:</strong> Name, profile picture, Facebook User ID</li>
                        <li><strong>Message Content:</strong> Messages you send to connected Facebook pages</li>
                        <li><strong>Contact Information:</strong> Phone numbers, email addresses shared voluntarily</li>
                        <li><strong>Business Information:</strong> Order details, service requests, preferences</li>
                        <li><strong>Usage Data:</strong> Interaction patterns, response times, message history</li>
                    </ul>

                    <h2>2. How We Use Your Information</h2>
                    <p>We use collected information to:</p>
                    <ul>
                        <li>Provide customer support and respond to inquiries</li>
                        <li>Process orders and manage service requests</li>
                        <li>Send automated responses and confirmations</li>
                        <li>Improve our messaging automation services</li>
                        <li>Analyze customer preferences and behavior patterns</li>
                        <li>Comply with legal obligations and business requirements</li>
                    </ul>

                    <h2>3. Information Sharing</h2>
                    <p>We do not sell, trade, or share your personal information with third parties except:</p>
                    <ul>
                        <li>With business owners whose Facebook pages you interact with</li>
                        <li>When required by law or legal process</li>
                        <li>To protect our rights, property, or safety</li>
                        <li>With your explicit consent</li>
                    </ul>

                    <h2>4. Facebook Integration</h2>
                    <p>Our service integrates with Facebook Messenger to:</p>
                    <ul>
                        <li>Receive and respond to messages sent to business pages</li>
                        <li>Access basic profile information of message senders</li>
                        <li>Store message history for customer service purposes</li>
                        <li>Provide automated responses and order processing</li>
                    </ul>
                    <p>We only access information necessary for providing our services and comply with Facebook's Platform Terms.</p>

                    <h2>5. Data Security</h2>
                    <p>We implement appropriate security measures to protect your information:</p>
                    <ul>
                        <li>Encrypted data transmission (HTTPS/TLS)</li>
                        <li>Secure database storage with access controls</li>
                        <li>Regular security audits and updates</li>
                        <li>Limited access to personal data by authorized personnel only</li>
                    </ul>

                    <h2>6. Data Retention</h2>
                    <p>We retain your information for as long as:</p>
                    <ul>
                        <li>Your account remains active</li>
                        <li>Needed to provide our services</li>
                        <li>Required by law or for legitimate business purposes</li>
                        <li>You may request deletion of your data at any time</li>
                    </ul>

                    <h2>7. Your Rights</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal information we have collected</li>
                        <li>Request correction of inaccurate information</li>
                        <li>Request deletion of your personal data</li>
                        <li>Opt-out of automated messaging</li>
                        <li>Withdraw consent at any time</li>
                    </ul>

                    <h2>8. Cookies and Tracking</h2>
                    <p>We use cookies and similar technologies to:</p>
                    <ul>
                        <li>Maintain your session and preferences</li>
                        <li>Analyze website usage and performance</li>
                        <li>Provide personalized experiences</li>
                        <li>Ensure security and prevent fraud</li>
                    </ul>

                    <h2>9. Children's Privacy</h2>
                    <p>Our services are not intended for users under 13 years of age. We do not knowingly collect personal information from children under 13.</p>

                    <h2>10. International Data Transfers</h2>
                    <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.</p>

                    <h2>11. Changes to This Policy</h2>
                    <p>We may update this privacy policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date.</p>

                    <h2>12. Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy, please contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> info@softstation.xyz</li>
                        <li><strong>Address:</strong> Wet Shewrapara, Mirpur, Dhaka</li>
                        <li><strong>Phone:</strong> +880-1925013478</li>
                    </ul>

                    <div class="alert alert-info mt-4">
                        <strong>Facebook Platform Compliance:</strong> This privacy policy complies with Facebook Platform Terms and covers our use of Facebook Platform APIs for messaging automation services.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>