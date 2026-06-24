<?php
namespace Database\Seeders;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'base_rate' => '4.5',
            'airport_base' => '1500',
            'airport_rate' => '5.5',
            'support_email' => 'hello@gmrides.net',
            'brand_name' => 'GM Rides',
            'website' => 'gmrides.net',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
