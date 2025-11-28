<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::all();

        if ($campaigns->isEmpty()) {
            $this->command->warn('No campaigns found. Please run CampaignSeeder first.');
            return;
        }

        $contacts = [
            // CRM Campaign Contacts
            ['first_name' => 'John', 'last_name' => 'Smith', 'phone' => '+15551234567', 'email' => 'john.smith@example.com', 'company' => 'Tech Solutions Inc', 'campaign_id' => $campaigns->where('name', 'Q1 CRM Campaign')->first()->id, 'tags' => ['qualified', 'decision_maker']],
            ['first_name' => 'Sarah', 'last_name' => 'Johnson', 'phone' => '+15551234568', 'email' => 'sarah.j@example.com', 'company' => 'Digital Marketing Pro', 'campaign_id' => $campaigns->where('name', 'Q1 CRM Campaign')->first()->id, 'tags' => ['interested']],
            ['first_name' => 'Michael', 'last_name' => 'Brown', 'phone' => '+15551234569', 'email' => 'm.brown@example.com', 'company' => 'Brown & Associates', 'campaign_id' => $campaigns->where('name', 'Q1 CRM Campaign')->first()->id, 'tags' => ['cold_lead']],
            ['first_name' => 'Emily', 'last_name' => 'Davis', 'phone' => '+15551234570', 'email' => 'emily.davis@example.com', 'company' => 'Davis Consulting', 'campaign_id' => $campaigns->where('name', 'Q1 CRM Campaign')->first()->id, 'tags' => ['qualified']],
            ['first_name' => 'David', 'last_name' => 'Wilson', 'phone' => '+15551234571', 'email' => 'd.wilson@example.com', 'company' => 'Wilson Enterprises', 'campaign_id' => $campaigns->where('name', 'Q1 CRM Campaign')->first()->id, 'tags' => ['decision_maker']],

            // Marketing Automation Contacts
            ['first_name' => 'Lisa', 'last_name' => 'Anderson', 'phone' => '+15551234572', 'email' => 'lisa.a@example.com', 'company' => 'Anderson Marketing', 'campaign_id' => $campaigns->where('name', 'Marketing Automation Spring Push')->first()->id, 'tags' => ['qualified', 'interested']],
            ['first_name' => 'Robert', 'last_name' => 'Taylor', 'phone' => '+15551234573', 'email' => 'r.taylor@example.com', 'company' => 'Taylor Media Group', 'campaign_id' => $campaigns->where('name', 'Marketing Automation Spring Push')->first()->id, 'tags' => ['decision_maker']],
            ['first_name' => 'Jennifer', 'last_name' => 'Martinez', 'phone' => '+15551234574', 'email' => 'j.martinez@example.com', 'company' => 'Martinez Creative', 'campaign_id' => $campaigns->where('name', 'Marketing Automation Spring Push')->first()->id, 'tags' => ['interested']],
            ['first_name' => 'James', 'last_name' => 'Garcia', 'phone' => '+15551234575', 'email' => 'j.garcia@example.com', 'company' => 'Garcia Solutions', 'campaign_id' => $campaigns->where('name', 'Marketing Automation Spring Push')->first()->id, 'tags' => ['cold_lead']],
            ['first_name' => 'Patricia', 'last_name' => 'Rodriguez', 'phone' => '+15551234576', 'email' => 'p.rodriguez@example.com', 'company' => 'Rodriguez Agency', 'campaign_id' => $campaigns->where('name', 'Marketing Automation Spring Push')->first()->id, 'tags' => ['qualified']],

            // Project Management Contacts
            ['first_name' => 'William', 'last_name' => 'Lee', 'phone' => '+15551234577', 'email' => 'w.lee@example.com', 'company' => 'Lee Development', 'campaign_id' => $campaigns->where('name', 'Project Management Beta')->first()->id, 'tags' => ['interested']],
            ['first_name' => 'Linda', 'last_name' => 'White', 'phone' => '+15551234578', 'email' => 'l.white@example.com', 'company' => 'White Tech', 'campaign_id' => $campaigns->where('name', 'Project Management Beta')->first()->id, 'tags' => ['decision_maker']],
            ['first_name' => 'Richard', 'last_name' => 'Harris', 'phone' => '+15551234579', 'email' => 'r.harris@example.com', 'company' => 'Harris Industries', 'campaign_id' => $campaigns->where('name', 'Project Management Beta')->first()->id, 'tags' => ['qualified']],
            ['first_name' => 'Susan', 'last_name' => 'Clark', 'phone' => '+15551234580', 'email' => 's.clark@example.com', 'company' => 'Clark Systems', 'campaign_id' => $campaigns->where('name', 'Project Management Beta')->first()->id, 'tags' => ['cold_lead']],
            ['first_name' => 'Joseph', 'last_name' => 'Lewis', 'phone' => '+15551234581', 'email' => 'j.lewis@example.com', 'company' => 'Lewis Group', 'campaign_id' => $campaigns->where('name', 'Project Management Beta')->first()->id, 'tags' => ['interested']],

            // Enterprise CRM Contacts
            ['first_name' => 'Thomas', 'last_name' => 'Walker', 'phone' => '+15551234582', 'email' => 't.walker@example.com', 'company' => 'Walker Corp', 'campaign_id' => $campaigns->where('name', 'CRM Enterprise Outreach')->first()->id, 'tags' => ['enterprise', 'decision_maker']],
            ['first_name' => 'Jessica', 'last_name' => 'Hall', 'phone' => '+15551234583', 'email' => 'j.hall@example.com', 'company' => 'Hall Enterprises', 'campaign_id' => $campaigns->where('name', 'CRM Enterprise Outreach')->first()->id, 'tags' => ['enterprise', 'qualified']],
            ['first_name' => 'Charles', 'last_name' => 'Allen', 'phone' => '+15551234584', 'email' => 'c.allen@example.com', 'company' => 'Allen Holdings', 'campaign_id' => $campaigns->where('name', 'CRM Enterprise Outreach')->first()->id, 'tags' => ['enterprise']],
            ['first_name' => 'Karen', 'last_name' => 'Young', 'phone' => '+15551234585', 'email' => 'k.young@example.com', 'company' => 'Young Industries', 'campaign_id' => $campaigns->where('name', 'CRM Enterprise Outreach')->first()->id, 'tags' => ['enterprise', 'interested']],
            ['first_name' => 'Daniel', 'last_name' => 'King', 'phone' => '+15551234586', 'email' => 'd.king@example.com', 'company' => 'King Solutions', 'campaign_id' => $campaigns->where('name', 'CRM Enterprise Outreach')->first()->id, 'tags' => ['enterprise', 'decision_maker']],
        ];

        foreach ($contacts as $contactData) {
            Contact::create($contactData);
        }
    }
}
