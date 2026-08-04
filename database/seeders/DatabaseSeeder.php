<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProjectSeeder::class,
            SkillSeeder::class,
            ExperienceSeeder::class,
            SiteSettingSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
