<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('app_settings')->insertOrIgnore([
            [
                'key'        => 'terms_title',
                'label'      => 'Terms & Conditions Title',
                'group'      => 'legal',
                'value'      => 'Terms & Conditions',
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'terms_content',
                'label'      => 'Terms & Conditions Content',
                'group'      => 'legal',
                'value'      => $this->termsContent(),
                'type'       => 'html',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'privacy_title',
                'label'      => 'Privacy Policy Title',
                'group'      => 'legal',
                'value'      => 'Privacy Policy',
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'privacy_content',
                'label'      => 'Privacy Policy Content',
                'group'      => 'legal',
                'value'      => $this->privacyContent(),
                'type'       => 'html',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'contact_email',
                'label'      => 'Contact Email Address',
                'group'      => 'contact',
                'value'      => '',
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'contact_phone',
                'label'      => 'Contact Phone Number',
                'group'      => 'contact',
                'value'      => '',
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function termsContent(): string
    {
        return <<<'HTML'
<h2>1. Acceptance of Terms</h2>
<p>By accessing and using ElifLammeem ("the Platform"), you agree to be bound by these Terms &amp; Conditions. If you do not agree to these terms, please discontinue use of the Platform immediately. These terms apply to all users, including school administrators, staff members, branch managers, and any individuals accessing the Platform through an access code.</p>

<h2>2. Use of the Platform</h2>
<p>ElifLammeem is a multi-tenant school issue-reporting and tracking platform. Access is granted on a school-by-school basis. Each school (tenant) is responsible for managing its own users, data, and access codes. You agree to use the Platform only for lawful purposes and in a manner consistent with all applicable laws and regulations. You must not misuse the Platform, attempt to gain unauthorised access to any part of it, or disrupt its services in any way.</p>

<h2>3. Data Ownership &amp; Responsibility</h2>
<p>Each school retains ownership of its data submitted through the Platform. ElifLammeem acts as a data processor on behalf of the school (the data controller). Schools are responsible for ensuring that their use of the Platform complies with all applicable data protection laws, including obtaining necessary consents from parents and staff where required. ElifLammeem will process personal data only in accordance with documented instructions from the school and applicable law.</p>

<h2>4. Service Availability &amp; Limitations</h2>
<p>We strive to provide a reliable, continuously available service; however, we do not guarantee uninterrupted access. Scheduled maintenance, updates, or circumstances beyond our control may occasionally result in downtime. ElifLammeem shall not be liable for any loss or damage arising from temporary unavailability of the Platform. Features available to each school depend on the subscription plan selected at the time of provisioning.</p>

<h2>5. Intellectual Property</h2>
<p>All software, design, and content forming the ElifLammeem Platform remain the intellectual property of ElifLammeem and its licensors. Schools are granted a limited, non-exclusive, non-transferable licence to use the Platform for the duration of their active subscription. You may not copy, modify, distribute, or create derivative works from any part of the Platform without prior written consent.</p>

<h2>6. Termination</h2>
<p>ElifLammeem reserves the right to suspend or terminate access to the Platform for any school that violates these Terms &amp; Conditions, fails to maintain an active subscription, or engages in conduct that is harmful to other users or the integrity of the Platform. Upon termination, schools may request an export of their data within 30 days, after which data may be permanently deleted.</p>
HTML;
    }

    private function privacyContent(): string
    {
        return <<<'HTML'
<h2>1. Who We Are</h2>
<p>ElifLammeem operates a cloud-based school issue-reporting and tracking platform. In the context of data protection law, ElifLammeem acts as a <strong>data processor</strong> on behalf of the schools (tenants) that use our Platform. Each school is the <strong>data controller</strong> for the personal data of their staff, parents, and students. This Privacy Policy explains how we collect, use, store, and protect personal data processed through the Platform.</p>

<h2>2. Data We Collect</h2>
<p>We collect and process the following categories of personal data: contact information (name, email address, phone number) for staff and roster contacts; issue content submitted by parents or teachers, including any attachments; usage and activity logs within the Platform; IP addresses and device metadata for security and fraud prevention purposes; and, where applicable, AI-generated analysis of issue content (sentiment, urgency, and themes). We do not collect sensitive categories of personal data (such as health or financial information) intentionally — schools are responsible for ensuring that submissions do not contain unnecessary sensitive data.</p>

<h2>3. How We Use Personal Data</h2>
<p>Personal data is used solely to deliver and improve the Platform services contracted by each school. Specifically, data is used to: route and track issue reports between contacts and school staff; send email notifications relating to issue status and activity; generate anonymised analytics and trend reports for school administrators; and perform AI-based sentiment and urgency analysis to assist staff in prioritising responses. We do not sell, rent, or share personal data with third parties for marketing purposes.</p>

<h2>4. Your Rights (GDPR &amp; Applicable Law)</h2>
<p>If you are located in the European Economic Area (EEA) or the United Kingdom, you have rights under the General Data Protection Regulation (GDPR) or UK GDPR, including: the right to access your personal data; the right to rectification of inaccurate data; the right to erasure ("right to be forgotten") in certain circumstances; the right to restrict or object to processing; and the right to data portability. To exercise any of these rights, please contact your school administrator in the first instance, as the school is the data controller. For matters relating to ElifLammeem's processing activities, you may contact us at the address below.</p>

<h2>5. Data Retention &amp; Security</h2>
<p>Personal data is retained for the duration of the school's active subscription plus an additional 30-day period following termination, after which it is permanently deleted. We implement appropriate technical and organisational measures to protect personal data against unauthorised access, alteration, disclosure, or destruction. These measures include encrypted data transmission (TLS), access controls, tenant-level data isolation, and regular security reviews. Despite these measures, no system is completely secure — we encourage schools to use strong passwords and report any suspected security incidents to us promptly.</p>

<h2>6. Cookies &amp; Tracking</h2>
<p>The Platform uses session cookies necessary for authentication and security. We do not use third-party advertising or tracking cookies. The public marketing website (this site) may use minimal analytics to understand visitor behaviour in aggregate — no personally identifiable information is collected for marketing purposes. You can control cookie settings through your browser at any time.</p>
HTML;
    }
}
