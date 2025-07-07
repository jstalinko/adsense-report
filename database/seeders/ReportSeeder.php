<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            'example1.com', 'example2.com', 'example3.net', 'adsense-site.id',
            'marketinghub.co', 'devproject.io', 'techblog.dev', 'localbiz.id',
            'startupzone.org', 'webservice.co.id', 'newlaunch.io'
        ];

        $data = [];

        for ($i = 0; $i < 50; $i++) {
            $domain = $domains[array_rand($domains)];
            $modal = rand(200000, 1000000);
            $revenue = rand($modal + 100000, $modal * 3); // revenue selalu lebih besar dari modal
            $taxRate = [5, 11,10, 0, 3][array_rand([0,1,2,3,4])]; // random tax %
            
            // created_at bulan ini atau bulan lalu
            $createdAt = fake()->dateTimeBetween('-1 month', 'now');

            $data[] = [
                'user_id'   => [1,2][array_rand([0,1])],
                'domain'     => $domain,
                'modal'      => $modal,
                'revenue'    => $revenue,
                'tax'        => $taxRate, // stored as percentage (e.g. 0.1 = 10%)
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        DB::table('reports')->insert($data);
    }
}
