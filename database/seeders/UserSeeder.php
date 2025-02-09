<?php

namespace Database\Seeders;

use App\Models\User;
use CaliCastle\Cuid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'unique_id' => Cuid::make(),
                'username' => 'mokurolia',
                'email' => 'mokurolia@me.com',
                'fullname' => 'Lia Sigma Papale',
                'password' => bcrypt('mokurolia'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'unique_id' => Cuid::make(),
                'username' => 'kuroneko',
                'email' => 'kuroneko@me.com',
                'fullname' => 'Kucing Hitam',
                'password' => bcrypt('kuroneko'),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        User::insert($data);
    }
}
