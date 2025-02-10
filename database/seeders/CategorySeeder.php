<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Fiction',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Horror',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Comedy',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Thriller',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Romance',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Adventure',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        Category::insert($data);
    }
}
