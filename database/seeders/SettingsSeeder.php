<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'school_name', 'value' => 'My School', 'type' => 'text', 'group' => 'general', 'label' => 'School Name', 'description' => 'The official name of the school', 'sort_order' => 1],
            ['key' => 'school_motto', 'value' => 'Excellence in Education', 'type' => 'text', 'group' => 'general', 'label' => 'School Motto', 'description' => 'The school motto or tagline', 'sort_order' => 2],
            ['key' => 'school_address', 'value' => '', 'type' => 'textarea', 'group' => 'general', 'label' => 'School Address', 'description' => 'Physical address of the school', 'sort_order' => 3],
            ['key' => 'school_phone', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Phone Number', 'description' => 'Main contact phone number', 'sort_order' => 4],
            ['key' => 'school_email', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Email Address', 'description' => 'Main contact email address', 'sort_order' => 5],
            ['key' => 'school_website', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Website', 'description' => 'School website URL', 'sort_order' => 6],
            ['key' => 'school_registration_number', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Registration Number', 'description' => 'Official registration or license number', 'sort_order' => 7],
            ['key' => 'school_emis_number', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'EMIS Number', 'description' => 'School EMIS number assigned by the Ministry of Education and Sports', 'sort_order' => 8],
            ['key' => 'uneb_centre_number', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'UNEB Centre Number', 'description' => 'UNEB examination centre number for the school', 'sort_order' => 9],
            ['key' => 'timezone', 'value' => 'Africa/Kampala', 'type' => 'text', 'group' => 'general', 'label' => 'Timezone', 'description' => 'System timezone', 'sort_order' => 10],

            // Branding
            ['key' => 'school_logo', 'value' => '', 'type' => 'image', 'group' => 'branding', 'label' => 'School Logo', 'description' => 'Main school logo (recommended: 200x200px)', 'sort_order' => 1],
            ['key' => 'school_badge', 'value' => '', 'type' => 'image', 'group' => 'branding', 'label' => 'School Badge', 'description' => 'School badge/crest for reports and certificates', 'sort_order' => 2],
            ['key' => 'school_favicon', 'value' => '', 'type' => 'image', 'group' => 'branding', 'label' => 'Favicon', 'description' => 'Browser tab icon (recommended: 32x32px)', 'sort_order' => 3],
            ['key' => 'primary_color', 'value' => '#1e40af', 'type' => 'color', 'group' => 'branding', 'label' => 'Primary Color', 'description' => 'Main brand color used across the system', 'sort_order' => 4],
            ['key' => 'secondary_color', 'value' => '#7c3aed', 'type' => 'color', 'group' => 'branding', 'label' => 'Secondary Color', 'description' => 'Secondary brand color', 'sort_order' => 5],
            ['key' => 'login_background', 'value' => '', 'type' => 'image', 'group' => 'branding', 'label' => 'Login Background', 'description' => 'Background image for the login page', 'sort_order' => 6],

            // Academic
            ['key' => 'grading_system', 'value' => 'percentage', 'type' => 'select', 'group' => 'academic', 'label' => 'Grading System', 'description' => 'Default grading system', 'options' => '["percentage","letter","gpa"]', 'sort_order' => 1],
            ['key' => 'attendance_type', 'value' => 'daily', 'type' => 'select', 'group' => 'academic', 'label' => 'Attendance Type', 'description' => 'How attendance is tracked', 'options' => '["daily","per_subject"]', 'sort_order' => 2],
            ['key' => 'academic_year_format', 'value' => 'calendar', 'type' => 'select', 'group' => 'academic', 'label' => 'Academic Year Format', 'description' => 'Calendar year (Jan-Dec) or custom', 'options' => '["calendar","custom"]', 'sort_order' => 3],
            ['key' => 'terms_per_year', 'value' => '3', 'type' => 'number', 'group' => 'academic', 'label' => 'Terms Per Year', 'description' => 'Number of terms/semesters per academic year', 'sort_order' => 4],
            ['key' => 'admission_number_prefix', 'value' => 'ADM', 'type' => 'text', 'group' => 'academic', 'label' => 'Admission Number Prefix', 'description' => 'Prefix for auto-generated admission numbers', 'sort_order' => 5],
            ['key' => 'admission_number_auto', 'value' => '1', 'type' => 'boolean', 'group' => 'academic', 'label' => 'Auto-Generate Admission Numbers', 'description' => 'Automatically generate admission numbers', 'sort_order' => 6],

            // Finance
            ['key' => 'currency', 'value' => 'UGX', 'type' => 'text', 'group' => 'finance', 'label' => 'Currency', 'description' => 'Default currency code (e.g., UGX, USD, KES)', 'sort_order' => 1],
            ['key' => 'currency_symbol', 'value' => 'UGX', 'type' => 'text', 'group' => 'finance', 'label' => 'Currency Symbol', 'description' => 'Currency display symbol', 'sort_order' => 2],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'text', 'group' => 'finance', 'label' => 'Invoice Prefix', 'description' => 'Prefix for invoice numbers', 'sort_order' => 3],
            ['key' => 'receipt_prefix', 'value' => 'RCT', 'type' => 'text', 'group' => 'finance', 'label' => 'Receipt Prefix', 'description' => 'Prefix for receipt numbers', 'sort_order' => 4],
            ['key' => 'payment_methods', 'value' => 'cash,bank,mobile_money,cheque', 'type' => 'text', 'group' => 'finance', 'label' => 'Payment Methods', 'description' => 'Comma-separated list of accepted payment methods', 'sort_order' => 5],

            // Communication
            ['key' => 'sms_provider', 'value' => '', 'type' => 'select', 'group' => 'communication', 'label' => 'SMS Provider', 'description' => 'SMS gateway provider', 'options' => '["","africas_talking","twilio"]', 'sort_order' => 1],
            ['key' => 'sms_api_key', 'value' => '', 'type' => 'text', 'group' => 'communication', 'label' => 'SMS API Key', 'description' => 'API key for SMS provider', 'sort_order' => 2],
            ['key' => 'sms_sender_id', 'value' => '', 'type' => 'text', 'group' => 'communication', 'label' => 'SMS Sender ID', 'description' => 'Sender name for outgoing SMS', 'sort_order' => 3],
            ['key' => 'email_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'communication', 'label' => 'Enable Email Notifications', 'description' => 'Send email notifications to users', 'sort_order' => 4],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
