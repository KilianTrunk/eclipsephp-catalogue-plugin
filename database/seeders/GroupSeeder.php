<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // Create default groups for each site
        $sites = \Eclipse\Core\Models\Site::all();

        foreach ($sites as $site) {
            Group::create([
                'site_id' => $site->id,
                'code' => 'featured',
                'name' => 'Featured Products',
                'is_active' => true,
                'is_browsable' => true,
            ]);

            Group::create([
                'site_id' => $site->id,
                'code' => 'new-arrivals',
                'name' => 'New Arrivals',
                'is_active' => true,
                'is_browsable' => true,
            ]);

            Group::create([
                'site_id' => $site->id,
                'code' => 'best-sellers',
                'name' => 'Best Sellers',
                'is_active' => true,
                'is_browsable' => true,
            ]);

            Group::create([
                'site_id' => $site->id,
                'code' => 'sale',
                'name' => 'Sale Items',
                'is_active' => true,
                'is_browsable' => false,
            ]);
        }
    }
}
