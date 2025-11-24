@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="mb-4">Terms of Service</h1>
                    <p class="text-muted mb-4">Last updated: {{ date('F d, Y') }}</p>

                    <div class="content">
                        <section class="mb-5">
                            <h2 class="h4 mb-3">1. Acceptance of Terms</h2>
                            <p>
                                By accessing and using BulkSMS by Matech Technologies ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. 
                                If you do not agree to these Terms of Service, please do not use our Service.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">2. Description of Service</h2>
                            <p>
                                BulkSMS is a multi-channel messaging platform that enables businesses to send SMS, WhatsApp, and Email messages to their customers. 
                                The Service includes contact management, campaign creation, message scheduling, and analytics features.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">3. User Accounts</h2>
                            <h3 class="h6 mb-2">3.1 Account Creation</h3>
                            <p>To use the Service, you must:</p>
                            <ul>
                                <li>Provide accurate and complete registration information</li>
                                <li>Maintain and update your account information</li>
                                <li>Maintain the security of your account credentials</li>
                                <li>Accept responsibility for all activities under your account</li>
                            </ul>

                            <h3 class="h6 mb-2 mt-3">3.2 Account Security</h3>
                            <p>
                                You are responsible for maintaining the confidentiality of your account password and for all activities that occur under your account. 
                                You agree to notify us immediately of any unauthorized use of your account.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">4. Payment and Billing</h2>
                            <p>
                                The Service operates on a prepaid credit system. You must maintain a sufficient balance to send messages. 
                                Credits are non-refundable except as required by law. We reserve the right to change our pricing at any time with prior notice.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">5. Acceptable Use</h2>
                            <p>You agree not to use the Service to:</p>
                            <ul>
                                <li>Send spam, unsolicited messages, or messages that violate applicable laws</li>
                                <li>Send messages containing illegal, harmful, or offensive content</li>
                                <li>Impersonate any person or entity or misrepresent your affiliation</li>
                                <li>Interfere with or disrupt the Service or servers</li>
                                <li>Attempt to gain unauthorized access to any portion of the Service</li>
                                <li>Use the Service for any fraudulent or illegal purpose</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">6. Message Delivery</h2>
                            <p>
                                While we strive for high delivery rates, we do not guarantee that all messages will be delivered. 
                                Delivery depends on various factors including recipient network availability, message content, and recipient preferences. 
                                We are not liable for messages that are not delivered or are delayed.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">7. API Usage</h2>
                            <p>
                                If you use our API, you agree to comply with our API documentation and rate limits. 
                                We reserve the right to limit or suspend API access for accounts that exceed reasonable usage or violate these terms.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">8. Intellectual Property</h2>
                            <p>
                                The Service, including its original content, features, and functionality, is owned by Matech Technologies and is protected by international copyright, 
                                trademark, patent, trade secret, and other intellectual property laws.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">9. Termination</h2>
                            <p>
                                We may terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms of Service or is harmful to other users, 
                                us, or third parties. You may terminate your account at any time by contacting us.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">10. Limitation of Liability</h2>
                            <p>
                                To the maximum extent permitted by law, Matech Technologies shall not be liable for any indirect, incidental, special, consequential, or punitive damages, 
                                or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">11. Indemnification</h2>
                            <p>
                                You agree to indemnify and hold harmless Matech Technologies from any claims, damages, losses, liabilities, and expenses (including legal fees) 
                                arising out of your use of the Service or violation of these Terms.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">12. Changes to Terms</h2>
                            <p>
                                We reserve the right to modify these Terms of Service at any time. We will notify users of any material changes by posting the new Terms on this page 
                                and updating the "Last updated" date. Your continued use of the Service after such changes constitutes acceptance of the new Terms.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">13. Governing Law</h2>
                            <p>
                                These Terms shall be governed by and construed in accordance with the laws of Kenya, without regard to its conflict of law provisions.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">14. Contact Information</h2>
                            <p>If you have any questions about these Terms of Service, please contact us:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-envelope me-2"></i> Email: support@matechtechnologies.com</li>
                                <li><i class="fas fa-phone me-2"></i> Phone: +254 728 883 160</li>
                                <li><i class="fas fa-building me-2"></i> Matech Technologies</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

