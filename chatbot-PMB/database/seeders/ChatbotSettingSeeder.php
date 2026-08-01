<?php

namespace Database\Seeders;

use App\Models\ChatbotSetting;
use Illuminate\Database\Seeder;

class ChatbotSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatbotSetting::current();
    }
}
