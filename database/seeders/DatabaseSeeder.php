<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Page;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\SchoolProgram;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Default
        $admin = User::firstOrCreate(
            ['email' => 'admin@sekolah.id'],
            [
                'name' => 'Administrator Sekolah',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $guru = User::firstOrCreate(
            ['email' => 'guru@sekolah.id'],
            [
                'name' => 'Budi Santoso, S.Pd.',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]
        );

        // 2. Data Jenjang / Tingkat Kelas
        $gradesData = [
            ['name' => 'Kelas 1 SD', 'level' => 'SD', 'order' => 1],
            ['name' => 'Kelas 2 SD', 'level' => 'SD', 'order' => 2],
            ['name' => 'Kelas 3 SD', 'level' => 'SD', 'order' => 3],
            ['name' => 'Kelas 4 SD', 'level' => 'SD', 'order' => 4],
            ['name' => 'Kelas 5 SD', 'level' => 'SD', 'order' => 5],
            ['name' => 'Kelas 6 SD', 'level' => 'SD', 'order' => 6],
            ['name' => 'Kelas 7 SMP', 'level' => 'SMP', 'order' => 7],
            ['name' => 'Kelas 8 SMP', 'level' => 'SMP', 'order' => 8],
            ['name' => 'Kelas 9 SMP', 'level' => 'SMP', 'order' => 9],
            ['name' => 'Kelas 10 SMA', 'level' => 'SMA', 'order' => 10],
            ['name' => 'Kelas 11 SMA', 'level' => 'SMA', 'order' => 11],
            ['name' => 'Kelas 12 SMA', 'level' => 'SMA', 'order' => 12],
        ];

        $grades = [];
        foreach ($gradesData as $g) {
            $grades[$g['name']] = Grade::firstOrCreate(['name' => $g['name']], $g);
        }

        // 2.5 Data Program Sekolah (Peminatan / Jalur Belajar)
        $programsData = [
            [
                'name' => 'Program Reguler',
                'code' => 'REG',
                'level' => 'ALL',
                'icon' => '🏫',
                'color' => '#3b82f6',
                'description' => 'Program kurikulum standar nasional terpadu.',
                'is_active' => true,
            ],
            [
                'name' => 'Program Bilingual (Cambridge)',
                'code' => 'BIL',
                'level' => 'ALL',
                'icon' => '🌍',
                'color' => '#0284c7',
                'description' => 'Program dwibahasa (Inggris & Indonesia) dengan standar kurikulum internasional Cambridge.',
                'is_active' => true,
            ],
            [
                'name' => 'Program Tahfizh Al-Qur\'an',
                'code' => 'THF',
                'level' => 'ALL',
                'icon' => '🕌',
                'color' => '#059669',
                'description' => 'Program intensif hafalan Al-Qur\'an, tajwid, dan adab islami terstruktur.',
                'is_active' => true,
            ],
            [
                'name' => 'Program Sains & Riset (STEM)',
                'code' => 'STEM',
                'level' => 'ALL',
                'icon' => '🔬',
                'color' => '#7c3aed',
                'description' => 'Program pengayaan sains terapan, teknologi, rekayasa, dan matematika berbasis riset.',
                'is_active' => true,
            ],
        ];

        $programs = [];
        foreach ($programsData as $p) {
            $programs[$p['code']] = SchoolProgram::firstOrCreate(['code' => $p['code']], $p);
        }

        // Akun Siswa (Kelas 4 SD - Bilingual)
        $siswa = User::firstOrCreate(
            ['email' => 'siswa@sekolah.id'],
            [
                'name' => 'Ananda Putri',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $grades['Kelas 4 SD']->id,
                'program_id' => $programs['BIL']->id,
            ]
        );
        $siswa->update([
            'grade_id' => $grades['Kelas 4 SD']->id,
            'program_id' => $programs['BIL']->id,
        ]);

        // Akun Siswa Tahfizh (Kelas 4 SD - Tahfizh)
        $siswaTahfizh = User::firstOrCreate(
            ['email' => 'siswa.tahfizh@sekolah.id'],
            [
                'name' => 'Ahmad Fauzan',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $grades['Kelas 4 SD']->id,
                'program_id' => $programs['THF']->id,
            ]
        );

        // Akun Siswa Reguler (Kelas 4 SD - Reguler)
        $siswaReguler = User::firstOrCreate(
            ['email' => 'siswa.reguler@sekolah.id'],
            [
                'name' => 'Siti Sarah',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $grades['Kelas 4 SD']->id,
                'program_id' => $programs['REG']->id,
            ]
        );

        // 3. Mata Pelajaran (Umum & Spesifik Program)
        $subjectsData = [
            // Mapel Umum (Bisa diakses semua program)
            ['name' => 'Ilmu Pengetahuan Alam (IPA)', 'code' => 'IPA', 'level' => 'ALL', 'program_id' => null, 'color' => '#10b981', 'description' => 'Mempelajari alam, sains, biologi, dan fisika dasar.'],
            ['name' => 'Matematika', 'code' => 'MTK', 'level' => 'ALL', 'program_id' => null, 'color' => '#3b82f6', 'description' => 'Aritmatika, geometri, logika angka.'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'level' => 'ALL', 'program_id' => null, 'color' => '#f59e0b', 'description' => 'Literasi, tata bahasa, dan cerita interaktif.'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN', 'level' => 'ALL', 'program_id' => null, 'color' => '#ef4444', 'description' => 'Kewarganegaraan dan nilai-nilai luhur bangsa.'],
            ['name' => 'Bahasa Inggris', 'code' => 'ENG', 'level' => 'ALL', 'program_id' => null, 'color' => '#8b5cf6', 'description' => 'English for kids and teens.'],
            // Mapel Khusus Program Bilingual
            ['name' => 'Cambridge Primary Science', 'code' => 'CAM-SCI', 'level' => 'SD', 'program_id' => $programs['BIL']->id, 'color' => '#0284c7', 'description' => 'Cambridge curriculum primary science in English.'],
            ['name' => 'English Language Arts', 'code' => 'ELA', 'level' => 'SD', 'program_id' => $programs['BIL']->id, 'color' => '#06b6d4', 'description' => 'Reading comprehension, creative writing, and literature.'],
            // Mapel Khusus Program Tahfizh
            ['name' => 'Tahfizh & Tahsin Al-Qur\'an', 'code' => 'THF-QUR', 'level' => 'ALL', 'program_id' => $programs['THF']->id, 'color' => '#059669', 'description' => 'Hafalan Juz 30 dan hukum tajwid makharijul huruf.'],
            ['name' => 'Bahasa Arab Dasar', 'code' => 'ARB', 'level' => 'ALL', 'program_id' => $programs['THF']->id, 'color' => '#10b981', 'description' => 'Kosakata dan percakapan bahasa Arab sehari-hari.'],
            // Mapel Khusus Program Sains & Riset (STEM)
            ['name' => 'Robotics & Coding Junior', 'code' => 'ROBO', 'level' => 'SD', 'program_id' => $programs['STEM']->id, 'color' => '#7c3aed', 'description' => 'Logika pemrograman visual dan eksperimen robotika sederhana.'],
        ];

        $subjects = [];
        foreach ($subjectsData as $s) {
            $subjects[$s['name']] = Subject::firstOrCreate(['name' => $s['name']], $s);
        }

        // 4. Kategori Buku
        $categoriesData = [
            ['name' => 'Buku Teks Kurikulum', 'slug' => 'buku-teks-kurikulum', 'description' => 'Buku pegangan utama pembelajaran.'],
            ['name' => 'Buku Pengayaan Sains & Teknologi', 'slug' => 'pengayaan-sains', 'description' => 'Materi pendukung eksplorasi ilmu pengetahuan.'],
            ['name' => 'Cerita Bergambar Interaktif', 'slug' => 'cerita-bergambar', 'description' => 'Dongeng dan kisah edukatif kaya ilustrasi.'],
            ['name' => 'Ensiklopedia Anak', 'slug' => 'ensiklopedia-anak', 'description' => 'Fakta seru dunia flora, fauna, dan luar angkasa.'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::firstOrCreate(['slug' => $c['slug']], $c);
        }

        // 5. Buku Contoh Interaktif
        $book = Book::firstOrCreate(
            ['slug' => 'petualangan-sains-mengenal-tata-surya'],
            [
                'title' => 'Petualangan Sains: Menguak Rahasia Tata Surya',
                'author' => 'Tim Literasi Sains Sekolah',
                'publisher' => 'Pustaka Digital Edukasi',
                'publication_year' => 2026,
                'isbn' => '978-602-1234-56-7',
                'description' => 'Buku digital interaktif seru untuk mempelajari matahari, 8 planet, dan keajaiban tata surya kita dengan ilustrasi visual dan kuis menyenangkan.',
                'grade_id' => $grades['Kelas 4 SD']->id,
                'subject_id' => $subjects['Ilmu Pengetahuan Alam (IPA)']->id,
                'category_id' => $categories['Buku Pengayaan Sains & Teknologi']->id,
                'is_published' => true,
                'estimated_read_time' => 20,
                'view_count' => 45,
            ]
        );

        // Bab 1
        $chapter1 = Chapter::firstOrCreate(
            ['book_id' => $book->id, 'chapter_number' => 1],
            [
                'title' => 'Bab 1: Sang Raja Tata Surya dan Planet Terestrial',
                'order' => 1,
                'summary' => 'Mengenal Matahari sebagai pusat tata surya serta 4 planet bagian dalam yang berbatu: Merkurius, Venus, Bumi, dan Mars.',
            ]
        );

        // Halaman 1 Bab 1
        Page::firstOrCreate(
            ['chapter_id' => $chapter1->id, 'page_number' => 1],
            [
                'title' => 'Matahari: Sang Bintang Raksasa Bercahaya',
                'content' => '<h2>Pusat dari Segala Gravitasi</h2><p>Tahukah kamu bahwa <strong>Matahari</strong> bukanlah planet, melainkan sebuah bintang raksasa? Matahari terletak di pusat tata surya kita dan memiliki gaya gravitasi yang sangat kuat, menjaga semua planet agar tetap beredar di orbitnya masing-masing.</p><p>Suhu di permukaan matahari mencapai sekitar 5.500 derajat Celsius, sedangkan di bagian intinya bisa mencapai 15 juta derajat Celsius! Tanpa sinar matahari, tidak akan ada kehidupan di planet Bumi kita.</p><blockquote><strong>Fakta Unik:</strong> Cahaya matahari membutuhkan waktu sekitar 8 menit dan 20 detik untuk sampai ke permukaan Bumi!</blockquote>',
            ]
        );

        // Halaman 2 Bab 1
        Page::firstOrCreate(
            ['chapter_id' => $chapter1->id, 'page_number' => 2],
            [
                'title' => 'Merkurius & Venus: Dua Sahabat Terdekat Sang Mentari',
                'content' => '<h2>Merkurius: Si Kecil yang Lincah</h2><p><strong>Merkurius</strong> adalah planet terkecil di tata surya dan yang paling dekat dengan Matahari. Karena hampir tidak memiliki atmosfer untuk menahan panas, suhunya luar biasa ekstrem: bisa mencapai 430°C di siang hari dan turun drastis hingga -180°C di malam hari!</p><h2>Venus: Planet Terpanas dan Paling Terang</h2><p>Meskipun bukan yang terdekat dengan Matahari, <strong>Venus</strong> justru adalah planet paling panas dengan suhu permukaan mencapai 465°C! Mengapa? Karena atmosfer Venus diselimuti awan tebal gas karbon dioksida yang menyebabkan efek rumah kaca dahsyat.</p><blockquote><strong>Tahukah Kamu?</strong> Venus sering dijuluki sebagai "Bintang Fajar" atau "Bintang Kejora" karena tampak bersinar sangat terang di langit subuh dan senja.</blockquote>',
            ]
        );

        // Halaman 3 Bab 1
        Page::firstOrCreate(
            ['chapter_id' => $chapter1->id, 'page_number' => 3],
            [
                'title' => 'Bumi: Rumah Biru Kehidupan Kita',
                'content' => '<h2>Satu-Satunya Surga Kehidupan</h2><p><strong>Bumi</strong> adalah planet ketiga dari Matahari dan rumah tempat tinggal kita semua. Sekitar 71% permukaan Bumi ditutupi oleh air laut cair yang kaya akan mineral dan kehidupan.</p><p>Bumi memiliki perisai magnetik dan lapisan atmosfer pelindung yang kaya oksigen serta nitrogen, melindungi makhluk hidup dari radiasi sinar kosmik dan hantaman meteorit luar angkasa.</p><blockquote><strong>Satelit Alami:</strong> Bumi memiliki satu satelit alami setia, yaitu <em>Bulan</em>, yang menyebabkan terjadinya pasang surut air laut dan menstabilkan kemiringan sumbu putar Bumi.</blockquote>',
            ]
        );

        // Halaman 4 Bab 1
        Page::firstOrCreate(
            ['chapter_id' => $chapter1->id, 'page_number' => 4],
            [
                'title' => 'Mars: Sang Planet Merah yang Penuh Misteri',
                'content' => '<h2>Gurun Karat di Angkasa Luar</h2><p>Di sebelah orbit Bumi, terdapat planet <strong>Mars</strong>. Mars dijuluki <em>Planet Merah</em> karena batuannya mengandung banyak mineral oksida besi (karat) yang memantulkan semburat merah keemasan.</p><p>Mars memiliki gunung berapi terbesar dan tertinggi di seluruh tata surya bernama <strong>Olympus Mons</strong>, yang tingginya hampir 3 kali lipat Gunung Everest! Para ilmuwan di Bumi kini aktif mengirimkan robot penjelajah (rover) seperti Curiosity dan Perseverance untuk mencari jejak sumber air purba di Mars.</p>',
            ]
        );

        // Bab 2
        $chapter2 = Chapter::firstOrCreate(
            ['book_id' => $book->id, 'chapter_number' => 2],
            [
                'title' => 'Bab 2: Para Raksasa Gas dan Tepi Tata Surya',
                'order' => 2,
                'summary' => 'Menjelajahi Jupiter sang raksasa badai, cincin indah Saturnus, serta Uranus dan Neptunus si raksasa es.',
            ]
        );

        // Halaman 1 Bab 2
        Page::firstOrCreate(
            ['chapter_id' => $chapter2->id, 'page_number' => 1],
            [
                'title' => 'Jupiter: Raja Raksasa Gas Pelindung Bumi',
                'content' => '<h2>Planet Terbesar di Tata Surya</h2><p><strong>Jupiter</strong> adalah planet terbesar di tata surya kita. Jika semua planet lain digabungkan menjadi satu, ukurannya masih belum bisa menandingi kebesaran Jupiter! Jupiter tidak memiliki permukaan padat, melainkan tersusun dari gas hidrogen dan helium tebal.</p><p>Ciri khas Jupiter yang paling legendaris adalah <strong>Bintik Merah Raksasa (Great Red Spot)</strong>, yaitu badai angin topan raksasa yang ukurannya lebih besar dari Bumi dan telah berkecamuk tanpa henti selama ratusan tahun!</p><blockquote><strong>Pahlawan Penjaga:</strong> Gravitasi dahsyat Jupiter menarik banyak komet dan asteroid liar, sehingga melindungi planet-planet dalam seperti Bumi dari tabrakan mematikan.</blockquote>',
            ]
        );

        // Halaman 2 Bab 2
        Page::firstOrCreate(
            ['chapter_id' => $chapter2->id, 'page_number' => 2],
            [
                'title' => 'Saturnus: Sang Permata Bercincin Megah',
                'content' => '<h2>Keanggunan Cincin Es dan Debu</h2><p><strong>Saturnus</strong> adalah planet keenam dari Matahari yang terkenal dengan sistem cincinnya yang luar biasa memesona. Cincin Saturnus terbuat dari miliaran pecahan es, batu kerikil, dan debu kosmik yang berputar bersamaan.</p><p>Meskipun sangat besar, Saturnus adalah planet dengan massa jenis paling ringan di tata surya. Kepadatan Saturnus bahkan lebih rendah daripada air! Artinya, jika ada kolam renang yang cukup besar untuk menampungnya, planet Saturnus akan mengapung!</p><blockquote><strong>Bulan Titan:</strong> Saturnus memiliki bulan raksasa bernama Titan yang memiliki atmosfer tebal dan danau-danau cairan metana alami.</blockquote>',
            ]
        );

        // Halaman 3 Bab 2
        Page::firstOrCreate(
            ['chapter_id' => $chapter2->id, 'page_number' => 3],
            [
                'title' => 'Uranus & Neptunus: Raksasa Es di Ujung Tata Surya',
                'content' => '<h2>Uranus: Planet yang Menggelinding</h2><p><strong>Uranus</strong> memiliki keunikan luar biasa karena sumbu rotasinya miring hampir 98 derajat! Planet ini tampak berputar miring atau seperti menggelinding mengelilingi Matahari. Warna biru kehijauannya berasal dari kandungan gas metana di atmosfernya.</p><h2>Neptunus: Si Biru Berangin Dahsyat</h2><p><strong>Neptunus</strong> adalah planet terjauh dari Matahari yang memerlukan waktu 165 tahun bumi untuk menyelesaikan satu kali orbit! Di Neptunus bertiup badai angin supersonik tercepat di tata surya, mencapai kecepatan lebih dari 2.100 km/jam.</p>',
            ]
        );

        // Kuis Bab 1
        $quiz = Quiz::firstOrCreate(
            ['chapter_id' => $chapter1->id],
            [
                'title' => 'Kuis Interaktif: Uji Pemahaman Tata Surya',
                'description' => 'Jawab 3 pertanyaan seru berikut untuk menguji pemahamanmu tentang Bab 1!',
                'passing_score' => 70,
                'time_limit_minutes' => 5,
                'is_active' => true,
            ]
        );

        // Soal 1
        $q1 = QuizQuestion::firstOrCreate(
            ['quiz_id' => $quiz->id, 'order' => 1],
            [
                'question_text' => 'Mengapa Mars sering disebut sebagai "Planet Merah"?',
                'question_type' => 'multiple_choice',
                'score_weight' => 35,
                'explanation' => 'Mars terlihat merah karena permukaannya kaya akan oksida besi (karat).',
            ]
        );

        QuizOption::firstOrCreate(['quiz_question_id' => $q1->id, 'option_text' => 'Karena suhunya sangat panas mendidih', 'is_correct' => false, 'order' => 1]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q1->id, 'option_text' => 'Karena mengandung banyak oksida besi (karat)', 'is_correct' => true, 'order' => 2]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q1->id, 'option_text' => 'Karena sering terjadi kebakaran hutan', 'is_correct' => false, 'order' => 3]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q1->id, 'option_text' => 'Karena dipenuhi lampu merah dari luar angkasa', 'is_correct' => false, 'order' => 4]);

        // Soal 2
        $q2 = QuizQuestion::firstOrCreate(
            ['quiz_id' => $quiz->id, 'order' => 2],
            [
                'question_text' => 'Berapa perkiraan waktu yang dibutuhkan cahaya Matahari untuk mencapai permukaan Bumi?',
                'question_type' => 'multiple_choice',
                'score_weight' => 35,
                'explanation' => 'Cahaya matahari melintasi jarak sekitar 150 juta km dalam waktu kurang lebih 8 menit 20 detik.',
            ]
        );

        QuizOption::firstOrCreate(['quiz_question_id' => $q2->id, 'option_text' => 'Sekitar 8 menit 20 detik', 'is_correct' => true, 'order' => 1]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q2->id, 'option_text' => '1 detik saja', 'is_correct' => false, 'order' => 2]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q2->id, 'option_text' => '24 jam penuh', 'is_correct' => false, 'order' => 3]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q2->id, 'option_text' => '1 jam 30 menit', 'is_correct' => false, 'order' => 4]);

        // Soal 3
        $q3 = QuizQuestion::firstOrCreate(
            ['quiz_id' => $quiz->id, 'order' => 3],
            [
                'question_text' => 'Matahari adalah sebuah planet terbesar di tata surya.',
                'question_type' => 'multiple_choice',
                'score_weight' => 30,
                'explanation' => 'Salah! Matahari adalah bintang, bukan planet.',
            ]
        );

        QuizOption::firstOrCreate(['quiz_question_id' => $q3->id, 'option_text' => 'Benar', 'is_correct' => false, 'order' => 1]);
        QuizOption::firstOrCreate(['quiz_question_id' => $q3->id, 'option_text' => 'Salah (Matahari adalah Bintang)', 'is_correct' => true, 'order' => 2]);

        // 6. Inisialisasi Lencana Literasi (Badges)
        \App\Services\GamificationService::seedBadges();

        // 7. Data Siswa Demo Tambahan untuk Papan Peringkat (Leaderboard)
        $kelas4Id = $grades['Kelas 4 SD']->id;
        $moreStudents = [
            [
                'email' => 'cantika@sekolah.id',
                'name' => 'Cantika Dewi',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $kelas4Id,
                'reading_streak_days' => 4,
                'longest_streak_days' => 6,
                'last_read_date' => now(),
                'literacy_points' => 390,
            ],
            [
                'email' => 'bima@sekolah.id',
                'name' => 'Bima Wicaksono',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $kelas4Id,
                'reading_streak_days' => 3,
                'longest_streak_days' => 5,
                'last_read_date' => now(),
                'literacy_points' => 320,
            ],
            [
                'email' => 'dimas@sekolah.id',
                'name' => 'Dimas Pratama',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $kelas4Id,
                'reading_streak_days' => 2,
                'longest_streak_days' => 3,
                'last_read_date' => now(),
                'literacy_points' => 210,
            ],
            [
                'email' => 'elis@sekolah.id',
                'name' => 'Elis Rahmawati',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_id' => $kelas4Id,
                'reading_streak_days' => 1,
                'longest_streak_days' => 2,
                'last_read_date' => now(),
                'literacy_points' => 120,
            ],
        ];

        foreach ($moreStudents as $stData) {
            User::firstOrCreate(['email' => $stData['email']], $stData);
        }

        // Berikan progres & lencana awal untuk siswa demo Ananda Putri
        $ananda = User::where('email', 'siswa@sekolah.id')->first();
        if ($ananda) {
            $ananda->update([
                'reading_streak_days' => 5,
                'longest_streak_days' => 5,
                'last_read_date' => now(),
                'literacy_points' => 450,
            ]);

            // Berikan lencana awal
            $kutuBuku = \App\Models\Badge::where('slug', 'kutu-buku-pemula')->first();
            $streak3 = \App\Models\Badge::where('slug', 'semangat-3-hari')->first();
            $streak5 = \App\Models\Badge::where('slug', 'membaca-5-hari-berturut-turut')->first();

            if ($kutuBuku && ! $ananda->hasBadge($kutuBuku->slug)) {
                $ananda->badges()->attach($kutuBuku->id, ['awarded_at' => now()->subDays(4)]);
            }
            if ($streak3 && ! $ananda->hasBadge($streak3->slug)) {
                $ananda->badges()->attach($streak3->id, ['awarded_at' => now()->subDays(2)]);
            }
            if ($streak5 && ! $ananda->hasBadge($streak5->slug)) {
                $ananda->badges()->attach($streak5->id, ['awarded_at' => now()]);
            }
        }
    }
}
