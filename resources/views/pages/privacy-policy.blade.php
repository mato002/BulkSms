@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="mb-4">Privacy Policy</h1>
                    <p class="text-muted mb-4">Last updated: {{ date('F d, Y') }}</p>

                    <div class="content">
                        <section class="mb-5">
                            <h2 class="h4 mb-3">1. Introduction</h2>
                            <p>
                                BulkSMS by Matech Technologies ("we", "our", or "us") is committed to protecting your privacy. 
                                This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use 
                                our SMS platform and services.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">2. Information We Collect</h2>
                            <h3 class="h6 mb-2">2.1 Personal Information</h3>
                            <p>We may collect the following personal information:</p>
                            <ul>
                                <li>Name and contact information (email address, phone number)</li>
                                <li>Company name and business information</li>
                                <li>Payment and billing information</li>
                                <li>Account credentials and authentication data</li>
                            </ul>

                            <h3 class="h6 mb-2 mt-3">2.2 Usage Data</h3>
                            <p>We automatically collect information about how you use our services, including:</p>
                            <ul>
                                <li>Message delivery logs and status</li>
                                <li>Campaign performance metrics</li>
                                <li>API usage statistics</li>
                                <li>Device and browser information</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">3. How We Use Your Information</h2>
                            <p>We use the collected information for the following purposes:</p>
                            <ul>
                                <li>To provide, maintain, and improve our services</li>
                                <li>To process transactions and send related information</li>
                                <li>To send administrative information and updates</li>
                                <li>To respond to your inquiries and provide customer support</li>
                                <li>To monitor and analyze usage patterns and trends</li>
                                <li>To detect, prevent, and address technical issues</li>
                                <li>To comply with legal obligations</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">4. Data Sharing and Disclosure</h2>
                            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
                            <ul>
                                <li><strong>Service Providers:</strong> With trusted third-party service providers who assist in operating our platform</li>
                                <li><strong>Legal Requirements:</strong> When required by law or to protect our rights and safety</li>
                                <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                                <li><strong>With Your Consent:</strong> When you have given explicit consent to share information</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">5. Data Security</h2>
                            <p>
                                We implement appropriate technical and organizational security measures to protect your personal information 
                                against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over 
                                the Internet is 100% secure, and we cannot guarantee absolute security.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">6. Your Rights</h2>
                            <p>You have the right to:</p>
                            <ul>
                                <li>Access and receive a copy of your personal data</li>
                                <li>Rectify inaccurate or incomplete information</li>
                                <li>Request deletion of your personal data</li>
                                <li>Object to processing of your personal data</li>
                                <li>Request restriction of processing</li>
                                <li>Data portability</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">7. Data Retention</h2>
                            <p>
                                We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, 
                                unless a longer retention period is required or permitted by law.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">8. Cookies and Tracking Technologies</h2>
                            <p>
                                We use cookies and similar tracking technologies to track activity on our platform and store certain information. 
                                You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">9. Children's Privacy</h2>
                            <p>
                                Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information 
                                from children. If you believe we have collected information from a child, please contact us immediately.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">10. Changes to This Privacy Policy</h2>
                            <p>
                                We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy 
                                on this page and updating the "Last updated" date.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 mb-3">11. Contact Us</h2>
                            <p>If you have any questions about this Privacy Policy, please contact us:</p>
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



