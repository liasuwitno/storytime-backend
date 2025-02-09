<?php

namespace Database\Seeders;

use App\Models\MultipleImage;
use App\Models\Story;
use CaliCastle\Cuid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PhpParser\Node\Expr\AssignOp\Mul;

class StorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $story = [
            [
                'unique_id' => Cuid::make(),
                'title' => "Backend: Master of Data",
                'slug' => 'backend-master-of-data',
                'body' => "Frontend: 'Kenapa selalu error kalau aku minta data?' Backend: 'Ya, karena data itu seperti makanan yang harus disiapkan dengan hati-hati, nggak bisa sembarangan dikirim.' Frontend: 'Oh, jadi aku cuma bisa terima kalau sudah ada restoran bintang lima?' Backend: 'Iya, tepat. Tapi kadang-kadang, kamu harus bersyukur kalau aku nggak cuma ngirim 'food delivery' sambil pakai sendok plastik.'",
                'user_id' => 2,
                'category_id' => rand(1, 5),
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unique_id' => Cuid::make(),
                'title' => "Palakau tak pukul backend",
                'slug' => 'palakau-tak-pukul-backend',
                'body' => "Frontend: 'Kenapa setiap request data seperti harus menunggu pakai aplikasi TikTok?' Backend: 'Itu karena data itu harus divalidasi, divakumkan, dan diberi tanda tangan digital dari pengacara.' Frontend: 'Tunggu, kamu ngirim data dengan pengacara?' Backend: 'Jelas, kalau nggak gitu, nanti ada masalah, dan kamu bakal dapet *404 Error* di pengadilan.'",
                'user_id' => 2,
                'category_id' => rand(1, 5),
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unique_id' => Cuid::make(),
                'title' => "Jika ada error, langsung salahkan backendmu",
                'slug' => 'jika-ada-error-langsung-salahkan-backendmu',
                'body' => "Frontend: 'Duh, aku ngirim request tapi kenapa kok seringkali hasilnya error?' Backend: 'Kalau kamu request data dengan attitude yang jelek, ya pasti error!' Frontend: 'Aku salah apa coba?' Backend: 'Kamu tuh ngirim data kayak ngirim chat ke mantan. Banyak harapan, tapi kosong isinya.'",
                'user_id' => 2,
                'category_id' => rand(1, 5),
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unique_id' => Cuid::make(),
                'title' => "Frontend selalu benar",
                'slug' => 'frontend-selalu-benar',
                'body' => "Frontend: 'Kenapa data yang kamu kirim ke aku kayak paket S.E.O. ya? Full optimisasi tapi tetap ga nyampe ke tujuan.' Backend: 'Itu karena data yang aku kirim harus melalui banyak filter, kayak pilihan jodoh yang baik.' Frontend: 'Jadi kalau aku minta data, apa aku harus bayar pake cryptocurrency?' Backend: 'Kalau kamu gak sabar, ya siap-siap aja kena 'transaction fee'.'",
                'user_id' => 2,
                'category_id' => rand(1, 5),
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unique_id' => Cuid::make(),
                'title' => "Backend huruf 'B' nya adalah BABI",
                'slug' => 'backend-huruf-b-nya-adalah-babi',
                'body' => "Frontend: 'Kenapa request data selalu lambat banget?' Backend: 'Soalnya, aku lagi nunggu server yang lain. Mereka juga nunggu server lain.' Frontend: 'Jadi kita semua cuma kayak 'waiting room' di rumah sakit?' Backend: 'Betul, dan kadang yang masuk itu malah pasien *404*.'",
                'user_id' => 2,
                'category_id' => rand(1, 5),
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        $multipleImage = [
            [
                'related_unique_id' => $story[0]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[0]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[0]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[1]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[1]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[1]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[1]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[2]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[2]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[2]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[2]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[3]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[3]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[4]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'related_unique_id' => $story[4]['unique_id'],
                'related_type' => Story::class,
                'image_url' => 'https://i.pinimg.com/736x/4b/41/63/4b41635e842d0aa012fae4309d68e3e6.jpg',
                'identifier' => 'story',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Story::insert($story);
        MultipleImage::insert($multipleImage);
    }
}
