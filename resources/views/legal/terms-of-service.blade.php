<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - {{ config('app.name') }}</title>
    @include('components.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="mb-4">Terms of Service</h1>
                <p class="text-muted">Last updated: {{ now()->format('F d, Y') }}</p>

                <div class="mt-4">
                    <h2>1. Acceptance of Terms</h2>
                    <p>By accessing or using our Facebook messaging automation service ("Service"), you agree to be bound by these Terms of Service ("Terms"). If you disagree with any part of these terms, you may not access the Service.</p>

                    <h2>2. Description of Service</h2>
                    <p>Our Service provides:</p>
                    <ul>
                        <li>Automated Facebook Messenger responses for businesses</li>
                        <li>Customer message management and organization</li>
                        <li>Order processing through Messenger conversations</li>
                        <li>Customer data collection and analytics</li>
                        <li>Integration with Facebook Pages for business communication</li>
                    </ul>

                    <h2>3. User Accounts</h2>
                    <h3>3.1 Account Types</h3>
                    <ul>
                        <li><strong>Business Clients:</strong> Organizations using our Service for customer communication</li>
                        <li><strong>End Users:</strong> Customers who message business pages through Facebook Messenger</li>
                    </ul>

                    <h3>3.2 Account Responsibilities</h3>
                    <p>You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your account credentials</li>
                        <li>All activities that occur under your account</li>
                        <li>Ensuring information provided is accurate and up-to-date</li>
                        <li>Compliance with Facebook's Terms of Service and Platform Policies</li>
                    </ul>

                    <h2>4. Acceptable Use Policy</h2>
                    <p>You agree not to:</p>
                    <ul>
                        <li>Send spam, unsolicited, or promotional messages</li>
                        <li>Violate any applicable laws or regulations</li>
                        <li>Infringe on intellectual property rights</li>
                        <li>Upload or transmit malicious code or harmful content</li>
                        <li>Attempt to gain unauthorized access to our systems</li>
                        <li>Use the Service for fraudulent or deceptive purposes</li>
                        <li>Violate Facebook Platform Terms or Community Standards</li>
                    </ul>

                    <h2>5. Facebook Platform Integration</h2>
                    <h3>5.1 Platform Compliance</h3>
                    <p>Our Service integrates with Facebook Platform and is subject to:</p>
                    <ul>
                        <li>Facebook Platform Terms of Service</li>
                        <li>Facebook Community Standards</li>
                        <li>Facebook Messenger Platform Policies</li>
                        <li>Changes in Facebook's APIs and policies</li>
                    </ul>

                    <h3>5.2 Data Access</h3>
                    <p>By using our Service, you consent to our access of:</p>
                    <ul>
                        <li>Messages sent to connected Facebook pages</li>
                        <li>Basic profile information of message senders</li>
                        <li>Page information and settings necessary for functionality</li>
                    </ul>

                    <h2>6. Subscription and Billing</h2>
                    <h3>6.1 Plan Types</h3>
                    <ul>
                        <li><strong>Free Plan:</strong> Limited features with usage restrictions</li>
                        <li><strong>Premium Plans:</strong> Extended features and higher limits</li>
                    </ul>

                    <h3>6.2 Billing Terms</h3>
                    <ul>
                        <li>Subscription fees are billed in advance</li>
                        <li>All fees are non-refundable unless otherwise stated</li>
                        <li>Prices may change with 30 days notice</li>
                        <li>Failure to pay may result in service suspension</li>
                    </ul>

                    <h2>7. Data and Privacy</h2>
                    <p>Your privacy is important to us. Please review our Privacy Policy, which also governs your use of the Service, to understand our data practices.</p>

                    <h2>8. Intellectual Property Rights</h2>
                    <h3>8.1 Our Rights</h3>
                    <p>The Service and its original content, features, and functionality are owned by us and are protected by copyright, trademark, and other laws.</p>

                    <h3>8.2 Your Rights</h3>
                    <p>You retain ownership of content you submit through the Service, but grant us a license to use it for providing our services.</p>

                    <h2>9. Service Availability</h2>
                    <ul>
                        <li>We strive for 99.9% uptime but do not guarantee uninterrupted service</li>
                        <li>Scheduled maintenance will be announced in advance when possible</li>
                        <li>Service may be affected by Facebook platform changes</li>
                        <li>We reserve the right to suspend service for maintenance or security reasons</li>
                    </ul>

                    <h2>10. Limitation of Liability</h2>
                    <p>To the maximum extent permitted by law:</p>
                    <ul>
                        <li>We are not liable for indirect, incidental, or consequential damages</li>
                        <li>Our total liability shall not exceed the amount paid for the Service</li>
                        <li>We are not responsible for Facebook platform changes affecting the Service</li>
                        <li>Users are responsible for backing up their data</li>
                    </ul>

                    <h2>11. Disclaimers</h2>
                    <p>THE SERVICE IS PROVIDED "AS IS" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p>

                    <h2>12. Indemnification</h2>
                    <p>You agree to indemnify and hold us harmless from any claims, damages, or expenses arising from your use of the Service or violation of these Terms.</p>

                    <h2>13. Termination</h2>
                    <h3>13.1 By You</h3>
                    <p>You may terminate your account at any time by contacting us or using account settings.</p>

                    <h3>13.2 By Us</h3>
                    <p>We may terminate or suspend your account immediately if you:</p>
                    <ul>
                        <li>Violate these Terms or our policies</li>
                        <li>Engage in fraudulent or illegal activities</li>
                        <li>Fail to pay applicable fees</li>
                        <li>Violate Facebook Platform Terms</li>
                    </ul>

                    <h2>14. Changes to Terms</h2>
                    <p>We reserve the right to modify these Terms at any time. Material changes will be notified via email or service announcement. Continued use constitutes acceptance of modified Terms.</p>

                    <h2>15. Dispute Resolution</h2>
                    <p>Any disputes arising from these Terms will be resolved through:</p>
                    <ul>
                        <li>Good faith negotiation first</li>
                        <li>Binding arbitration if negotiation fails</li>
                        <li>Laws of [Your Jurisdiction] apply</li>
                    </ul>

                    <h2>16. Contact Information</h2>
                    <p>For questions about these Terms, contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> info@softstation.xyz</li>
                        <li><strong>Address:</strong> Wet Shewrapara, Mirpur, Dhaka</li>
                        <li><strong>Phone:</strong> +880-1925013478</li>
                    </ul>

                    <div class="alert alert-warning mt-4">
                        <strong>Facebook Platform Notice:</strong> This service is not affiliated with Facebook. Use of this service is subject to Facebook's Platform Terms and Community Standards.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>