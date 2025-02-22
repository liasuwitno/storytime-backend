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
                'id' => '76fa7e2317beb564fca0dcdd5fc025e9',
                'username' => 'mokurolia',
                'email' => 'mokurolia@me.com',
                'fullname' => 'Lia Sigma Papale',
                'password' => bcrypt('mokurolia'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '76fa7e2317beb564fca0dcdd5fc02510',
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
