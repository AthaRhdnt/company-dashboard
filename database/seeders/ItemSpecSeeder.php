<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemSpecSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $websiteDevId = DB::table('items')->where('item_name', 'Website Development')->first()->id;
        $adCampaignId = DB::table('items')->where('item_name', 'Advertising Campaign')->first()->id;

        DB::table('item_specs')->insert([
            ['item_id' => $websiteDevId, 'item_description' => 'Custom WordPress theme build.'],
            ['item_id' => $websiteDevId, 'item_description' => 'React single-page application.'],
            ['item_id' => $adCampaignId, 'item_description' => 'Google Ads management (per month).'],
        ]);
    }
}
