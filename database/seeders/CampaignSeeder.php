<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        $campaigns = [
            [
                'name' => 'Q1 CRM Campaign',
                'product_id' => $products->where('name', 'SaaS CRM Platform')->first()->id,
                'script' => 'Focus on pain points: data scattered across tools, missing follow-ups, lack of sales visibility.',
                'ai_prompt_context' => 'This campaign targets businesses with 10-50 employees. Emphasize ROI and time savings.',
                'success_definition' => 'Booked demo or expressed strong interest in learning more',
                'status' => 'active'
            ],
            [
                'name' => 'Marketing Automation Spring Push',
                'product_id' => $products->where('name', 'Marketing Automation Tool')->first()->id,
                'script' => 'Highlight automation benefits: save 10+ hours per week, nurture leads automatically, track ROI.',
                'ai_prompt_context' => 'Target marketing managers and business owners. Focus on scalability and efficiency.',
                'success_definition' => 'Qualified lead or demo scheduled',
                'status' => 'active'
            ],
            [
                'name' => 'Project Management Beta',
                'product_id' => $products->where('name', 'Project Management Software')->first()->id,
                'script' => 'Emphasize team collaboration, deadline tracking, and visibility into project status.',
                'ai_prompt_context' => 'Target project managers and team leads. Address common concerns about adoption and complexity.',
                'success_definition' => 'Demo scheduled or trial signup',
                'status' => 'draft'
            ],
            [
                'name' => 'CRM Enterprise Outreach',
                'product_id' => $products->where('name', 'SaaS CRM Platform')->first()->id,
                'script' => 'Focus on enterprise features: advanced reporting, API access, dedicated support.',
                'ai_prompt_context' => 'Target larger companies (50+ employees). Emphasize scalability and enterprise support.',
                'success_definition' => 'Enterprise demo or sales conversation',
                'status' => 'active'
            ]
        ];

        foreach ($campaigns as $campaignData) {
            Campaign::create($campaignData);
        }
    }
}
