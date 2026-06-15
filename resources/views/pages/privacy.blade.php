@extends('layouts.page')

@section('title', 'Privacy Policy')
@section('meta_description', 'Learn how uSchool protects your personal data. Our privacy policy covers data collection, usage, security, and your rights as a school, parent, or staff member in Uganda.')

@section('content')
<h1>Privacy Policy</h1>
<p class="page-meta">Last updated: {{ date('F j, Y') }}</p>

<p>uSchool ("we", "our", or "the platform") is committed to protecting the privacy and security of all personal data entrusted to us by schools, students, parents, guardians, and staff members.</p>

<h2>1. Information We Collect</h2>
<p>We collect the following types of information when schools use our platform:</p>
<ul>
    <li><strong>Student Information:</strong> Names, date of birth, gender, class placement, academic records, attendance records, health information, and photographs.</li>
    <li><strong>Parent / Guardian Information:</strong> Names, phone numbers (including WhatsApp), email addresses, and relationship to the student.</li>
    <li><strong>Staff Information:</strong> Names, contact details, qualifications, role and subject assignments.</li>
    <li><strong>Financial Information:</strong> Fee structures, payment records, invoice history, and mobile money transaction references.</li>
    <li><strong>Account Information:</strong> Login credentials (email and encrypted password) for platform access.</li>
    <li><strong>Usage Data:</strong> Browser type, IP address, pages visited, and timestamps for security and analytics.</li>
</ul>

<h2>2. How We Use Your Information</h2>
<p>We use the collected information to:</p>
<ul>
    <li>Provide and maintain the school management platform and its features.</li>
    <li>Manage student records, attendance, grades, and fee collection.</li>
    <li>Send SMS or WhatsApp notifications to parents about attendance, fees, and school announcements.</li>
    <li>Generate report cards and academic reports.</li>
    <li>Provide dashboards and analytics to school administrators.</li>
    <li>Respond to support requests and improve our services.</li>
</ul>

<h2>3. Data Storage &amp; Security</h2>
<p>All data is stored on secure servers. We implement industry-standard security measures including:</p>
<ul>
    <li>Encrypted data transmission (HTTPS / TLS).</li>
    <li>Hashed and salted password storage.</li>
    <li>Role-based access control to limit data visibility.</li>
    <li>Regular backups to prevent data loss.</li>
    <li>Audit logging of data access and modifications.</li>
</ul>
<p>Your school's data stays in Uganda and is never shared with third parties for advertising or marketing purposes.</p>

<h2>4. Data Sharing</h2>
<p>We do not sell or rent personal information. Data may only be shared in the following situations:</p>
<ul>
    <li>With authorized school staff who have appropriate permissions within the platform.</li>
    <li>With parents/guardians to access their child's information via the parent portal.</li>
    <li>When required by Ugandan law or a valid legal order.</li>
    <li>With service providers who assist in platform operations (e.g., SMS gateway providers), under strict data processing agreements.</li>
</ul>

<h2>5. Data Retention</h2>
<p>We retain school data for as long as the school maintains an active account. Upon account termination, data is retained for up to 12 months before permanent deletion, unless the school requests earlier removal.</p>

<h2>6. Your Rights</h2>
<p>Schools and individuals have the right to:</p>
<ul>
    <li>Access personal data held about them on the platform.</li>
    <li>Request correction of inaccurate information.</li>
    <li>Request deletion of data, subject to legal retention requirements.</li>
    <li>Export their data in a standard format.</li>
</ul>

<h2>7. Children's Privacy</h2>
<p>Our platform stores information about minors as part of school management. This data is collected and managed by the school and is accessible only to authorized school personnel and the student's registered parents or guardians.</p>

<h2>8. Changes to This Policy</h2>
<p>We may update this Privacy Policy periodically. Schools will be notified of significant changes via the platform dashboard or email. Continued use of the platform constitutes acceptance of the updated policy.</p>

<h2>9. Contact Us</h2>
<p>If you have questions about this Privacy Policy, please contact us at:</p>
<ul>
    <li><strong>Email:</strong> info@schoolsystem.techmarketug.com</li>
    <li><strong>Phone / WhatsApp:</strong> +256 776 121 422</li>
    <li><strong>Address:</strong> Kampala, Uganda</li>
</ul>
@endsection