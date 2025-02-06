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
                'name' => 'Fiction'
            ],
            [
                'name' => 'Horror'
            ],
            [
                'name' => 'Comedy'
            ],
            [
                'name' => 'Thriller'
            ],
            [
                'name' => 'Romance'
            ],
            [
                'name' => 'Adventure'
            ],
        ];

        Category::insert($data);
    }
}
