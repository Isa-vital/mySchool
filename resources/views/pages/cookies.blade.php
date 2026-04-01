@extends('layouts.page')

@section('title', 'Cookie Policy')

@section('content')
<h1>Cookie Policy</h1>
<p class="page-meta">Last updated: {{ date('F j, Y') }}</p>

<p>This Cookie Policy explains how mySchool UG ("we", "our", "the platform") uses cookies and similar technologies when you access our platform.</p>

<h2>1. What Are Cookies?</h2>
<p>Cookies are small text files stored on your device (computer, tablet, or phone) when you visit a website. They help websites remember your preferences and improve your experience.</p>

<h2>2. Cookies We Use</h2>

<p><strong>Essential Cookies (Required)</strong></p>
<p>These are necessary for the platform to function and cannot be disabled. They include:</p>
<ul>
    <li><strong>Session Cookie:</strong> Keeps you logged in while you use the platform. Expires when you close your browser or after the session timeout.</li>
    <li><strong>CSRF Token:</strong> Protects against cross-site request forgery attacks. Required for all form submissions.</li>
    <li><strong>Remember Me:</strong> If you choose "Remember Me" at login, a cookie stores an encrypted token so you stay logged in across browser sessions.</li>
</ul>

<p><strong>Preference Cookies</strong></p>
<p>These cookies remember your choices and settings:</p>
<ul>
    <li><strong>Language/Locale:</strong> Remembers your preferred language setting.</li>
    <li><strong>Theme:</strong> Stores your display preferences (if applicable).</li>
</ul>

<h2>3. Cookies We Do Not Use</h2>
<p>mySchool UG does <strong>not</strong> use:</p>
<ul>
    <li>Third-party advertising or tracking cookies.</li>
    <li>Social media tracking pixels.</li>
    <li>Analytics cookies from third-party providers (e.g., Google Analytics).</li>
</ul>
<p>We do not share cookie data with any third parties for marketing or advertising purposes.</p>

<h2>4. Managing Cookies</h2>
<p>You can control cookies through your browser settings. Most browsers allow you to:</p>
<ul>
    <li>View what cookies are stored and delete them individually.</li>
    <li>Block third-party cookies.</li>
    <li>Block all cookies from specific sites.</li>
    <li>Clear all cookies when you close your browser.</li>
</ul>
<p><strong>Note:</strong> Disabling essential cookies will prevent you from logging in and using the platform.</p>

<h2>5. Data Stored in Cookies</h2>
<p>Our cookies contain only:</p>
<ul>
    <li>An encrypted session identifier (no personal information).</li>
    <li>A security token for form submissions.</li>
    <li>An encrypted authentication token (if "Remember Me" is used).</li>
</ul>
<p>No student data, financial information, or personal details are ever stored in cookies.</p>

<h2>6. Changes to This Policy</h2>
<p>We may update this Cookie Policy if we introduce new features that require additional cookies. Any changes will be reflected on this page with an updated date.</p>

<h2>7. Contact Us</h2>
<p>If you have questions about our use of cookies, please contact us at:</p>
<ul>
    <li><strong>Email:</strong> hello@myschool.ug</li>
    <li><strong>Phone / WhatsApp:</strong> +256 700 000 000</li>
    <li><strong>Address:</strong> Kampala, Uganda</li>
</ul>
@endsection