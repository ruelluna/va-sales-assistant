<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'SaaS CRM Platform',
                'description' => 'A comprehensive customer relationship management platform designed for growing businesses.',
                'features' => [
                    'Contact Management',
                    'Sales Pipeline Tracking',
                    'Email Integration',
                    'Analytics Dashboard',
                    'Mobile App Access'
                ],
                'pricing_info' => 'Starter: $29/month, Professional: $79/month, Enterprise: $199/month',
                'ai_prompt_context' => 'This is a CRM platform targeting small to medium businesses. Common pain points include managing customer data across multiple tools, losing track of sales opportunities, and lack of visibility into sales performance. The platform offers a unified solution with easy migration from spreadsheets or other CRMs.',
                'common_objections' => [
                    [
                        'type' => 'price',
                        'objection' => 'Too expensive for our budget',
                        'response' => 'I understand budget is a concern. Let me show you how our platform can actually save you money by consolidating multiple tools. Many customers find they\'re paying more for separate tools than our all-in-one solution.'
                    ],
                    [
                        'type' => 'time',
                        'objection' => 'We don\'t have time to migrate',
                        'response' => 'I completely understand. That\'s why we offer free migration assistance. Our team handles the heavy lifting, and most migrations take less than a week with minimal disruption to your workflow.'
                    ],
                    [
                        'type' => 'need',
                        'objection' => 'We\'re happy with our current system',
                        'response' => 'That\'s great to hear! Many of our customers felt the same way initially. Would you be open to a quick 15-minute demo to see how we might complement or improve upon what you\'re currently using?'
                    ]
                ],
                'cold_call_script_template' => "Hi [Name], this is [Your Name] from [Company]. I'm reaching out because I noticed [Company Name] is in the [Industry] space, and we've been helping similar companies streamline their customer management.\n\nAre you currently using any CRM system to track your customer interactions?\n\n[If yes] Great! What's working well with your current system, and what challenges are you facing?\n\n[If no] I'd love to show you how a CRM could help you stay organized and never miss a follow-up. Would you be open to a quick 10-minute conversation this week?",
                'success_definition' => 'Booked a demo call or qualified lead interested in learning more',
                'status' => 'active'
            ],
            [
                'name' => 'Marketing Automation Tool',
                'description' => 'Automate your marketing campaigns with email workflows, lead scoring, and behavioral tracking.',
                'features' => [
                    'Email Campaign Automation',
                    'Lead Scoring',
                    'Behavioral Tracking',
                    'A/B Testing',
                    'ROI Analytics'
                ],
                'pricing_info' => 'Basic: $49/month, Pro: $149/month, Agency: $399/month',
                'ai_prompt_context' => 'Marketing automation tool for businesses looking to scale their marketing efforts. Targets marketing managers and business owners who are manually sending emails or using basic email tools. Key benefits include time savings, better lead nurturing, and measurable ROI.',
                'common_objections' => [
                    [
                        'type' => 'complexity',
                        'objection' => 'Seems too complicated to set up',
                        'response' => 'I hear that a lot! Actually, our platform is designed to be intuitive. We provide step-by-step onboarding, and most customers are up and running within a day. Plus, we offer free setup assistance.'
                    ],
                    [
                        'type' => 'price',
                        'objection' => 'We\'re a small team, can\'t justify the cost',
                        'response' => 'That\'s exactly why we created our Basic plan. It\'s designed for small teams and starts at just $49/month. Many small teams find they save more than that in time alone. Would you like to see how it could work for your team size?'
                    ]
                ],
                'cold_call_script_template' => "Hi [Name], this is [Your Name] from [Company]. I help businesses automate their marketing to save time and generate more leads.\n\nAre you currently doing any email marketing or lead nurturing?\n\n[If yes] How much time do you spend on that each week?\n\n[If no] Would you be interested in learning how automation could help you stay in touch with prospects without the manual work?",
                'success_definition' => 'Qualified lead interested in automation or booked a demo',
                'status' => 'active'
            ],
            [
                'name' => 'Project Management Software',
                'description' => 'Collaborative project management tool for teams to plan, track, and deliver projects on time.',
                'features' => [
                    'Task Management',
                    'Team Collaboration',
                    'Gantt Charts',
                    'Time Tracking',
                    'Resource Planning'
                ],
                'pricing_info' => 'Team: $10/user/month, Business: $20/user/month, Enterprise: Custom pricing',
                'ai_prompt_context' => 'Project management software for teams struggling with coordination, missed deadlines, and lack of visibility. Appeals to project managers, team leads, and business owners managing multiple projects.',
                'common_objections' => [
                    [
                        'type' => 'adoption',
                        'objection' => 'Our team won\'t use it',
                        'response' => 'That\'s a valid concern. We\'ve found that adoption is highest when the tool actually makes people\'s lives easier, not harder. Our platform integrates with tools your team already uses, so there\'s minimal learning curve. We also provide training and support to ensure smooth adoption.'
                    ],
                    [
                        'type' => 'price',
                        'objection' => 'Per-user pricing gets expensive',
                        'response' => 'I understand. Let\'s look at the cost of not having visibility - missed deadlines, rework, and team confusion. Many teams find our platform pays for itself by preventing just one missed deadline. Plus, we offer volume discounts for larger teams.'
                    ]
                ],
                'cold_call_script_template' => "Hi [Name], this is [Your Name] from [Company]. I help teams stay organized and deliver projects on time.\n\nHow does your team currently manage projects and deadlines?\n\n[If using tools] What's working well, and what challenges are you facing?\n\n[If not using tools] Would you be interested in seeing how a project management tool could help your team stay aligned and meet deadlines?",
                'success_definition' => 'Qualified lead or demo scheduled',
                'status' => 'active'
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
