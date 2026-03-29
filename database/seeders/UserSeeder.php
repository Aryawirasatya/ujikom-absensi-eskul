<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentAcademic;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = SchoolYear::where('is_active', true)->first();
        $oldYear     = SchoolYear::where('is_active', false)->first();

        // =========================================
        // ADMIN
        // =========================================
        $admin = User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@sekolah.sch.id',
            'password'  => Hash::make('password'),
            'nisn'      => null,
            'gender'    => 'L',
            'is_active' => 1,
        ]);
        $admin->assignRole('admin');

        // =========================================
        // PEMBINA (6 orang)
        // =========================================
        $pembinaData = [
            ['name' => 'Budi Santoso, S.Pd',  'email' => 'budi@sekolah.sch.id',  'gender' => 'L'],
            ['name' => 'Siti Rahayu, S.Pd',   'email' => 'siti@sekolah.sch.id',  'gender' => 'P'],
            ['name' => 'Ahmad Fauzi, S.Pd',   'email' => 'ahmad@sekolah.sch.id', 'gender' => 'L'],
            ['name' => 'Dewi Lestari, S.Pd',  'email' => 'dewi@sekolah.sch.id',  'gender' => 'P'],
            ['name' => 'Rizky Pratama, S.Pd', 'email' => 'rizky@sekolah.sch.id', 'gender' => 'L'],
            ['name' => 'Nur Hidayah, S.Pd',   'email' => 'nur@sekolah.sch.id',   'gender' => 'P'],
        ];

        foreach ($pembinaData as $p) {
            $user = User::create([
                'name'      => $p['name'],
                'email'     => $p['email'],
                'password'  => Hash::make('password'),
                'nisn'      => null,
                'gender'    => $p['gender'],
                'is_active' => 1,
            ]);
            $user->assignRole('pembina');
        }

        // =========================================
        // SISWA
        // Kelas 7, 8, 9 — 11 kelas per angkatan (A–K) — 20 siswa per kelas
        // 10 laki-laki + 10 perempuan per kelas
        // Total: 3 × 11 × 20 = 660 siswa
        //
        // Siswa TIDAK memiliki email.
        // Password default = NISN (sesuai middleware ForceStudentChangePassword).
        // Login pertama kali pakai NISN + password NISN → diarahkan paksa ganti password.
        // =========================================
        $grades      = [7, 8, 9];
        $classLabels = ['A','B','C','D','E','F','G','H','I','J','K'];
        $nisnBase    = 2000000001;
        $nisnCounter = 0;
        $totalSiswa  = 0;

        $malePool   = $this->getMaleNames();   // 110 nama
        $femalePool = $this->getFemaleNames(); // 110 nama

        foreach ($grades as $gradeIndex => $grade) {
            foreach ($classLabels as $classIndex => $label) {
                for ($seat = 1; $seat <= 20; $seat++) {
                    $isMale       = ($seat <= 10);
                    $gender       = $isMale ? 'L' : 'P';
                    $pool         = $isMale ? $malePool : $femalePool;
                    $seatInGender = $isMale ? ($seat - 1) : ($seat - 11); // 0–9

                    // Index dirotasi agar nama tidak berulang antar kelas
                    $poolIndex = ($gradeIndex * 110 + $classIndex * 10 + $seatInGender) % count($pool);
                    $name      = $pool[$poolIndex];

                    $nisn = (string)($nisnBase + $nisnCounter);

                    $user = User::create([
                        'name'      => $name,
                        'email'     => null,            // siswa tidak pakai email
                        'password'  => Hash::make($nisn), // password default = NISN
                        'nisn'      => $nisn,
                        'gender'    => $gender,
                        'is_active' => 1,
                    ]);
                    $user->assignRole('siswa');

                    // Akademik tahun aktif
                    StudentAcademic::create([
                        'user_id'         => $user->id,
                        'school_year_id'  => $currentYear->id,
                        'grade'           => $grade,
                        'class_label'     => $label,
                        'academic_status' => 'active',
                    ]);

                    // Kelas 8 & 9 → punya data akademik tahun lalu (sudah naik kelas)
                    if ($grade >= 8 && $oldYear) {
                        StudentAcademic::create([
                            'user_id'         => $user->id,
                            'school_year_id'  => $oldYear->id,
                            'grade'           => $grade - 1,
                            'class_label'     => $label,
                            'academic_status' => 'active',
                        ]);
                    }

                    $nisnCounter++;
                    $totalSiswa++;
                }
            }
        }

        $this->command->info("✅ Users seeded: 1 admin, 6 pembina, {$totalSiswa} siswa.");
        $this->command->info("   Struktur: Kelas 7–9 | 11 kelas (A–K) per angkatan | 20 siswa/kelas.");
        $this->command->info("   Login siswa: NISN sebagai username, NISN sebagai password default.");
    }

    // =========================================
    // POOL NAMA LAKI-LAKI — 110 nama
    // =========================================
    private function getMaleNames(): array
    {
        return [
            'Achmad Fauzan',      'Ade Irfan',           'Aditya Nugraha',     'Agung Prasetyo',    'Ahmad Zaki',
            'Aldi Firmansyah',    'Alfarizi Putra',       'Alif Maulana',       'Alvan Darmawan',    'Andika Saputra',
            'Bagas Kurniawan',    'Bagus Setiawan',       'Bayu Aji',           'Bima Ardiansyah',   'Bintang Erlangga',
            'Bobby Santosa',      'Bramantyo Wibowo',     'Brian Kusuma',       'Budi Arjuna',       'Byan Pratama',
            'Cahya Ramadhan',     'Calvin Pradipta',      'Chandra Wijaya',     'Claudio Putra',     'Crisna Aditama',
            'Dafa Ananda',        'Daffa Hidayat',        'Dandi Saputra',      'Danu Prasetya',     'Dava Ramadhan',
            'Davin Pratama',      'Dennis Wirawan',       'Deva Kurniadi',      'Dicky Firmansyah',  'Dimas Ardianto',
            'Eko Prasetyo',       'Elang Permana',        'Erlan Saputra',      'Evan Nugroho',      'Ezra Pratama',
            'Fabian Kurniawan',   'Fachri Maulana',       'Fahri Setiawan',     'Faiz Ramadhan',     'Farhan Aditya',
            'Faris Ananda',       'Farrel Wibisono',      'Fathan Nugroho',     'Fikri Andrean',     'Firman Hadi',
            'Galang Nugroho',     'Galih Saputra',        'Gemilang Putra',     'Gilang Ramadhan',   'Guntur Prakoso',
            'Hafidz Maulana',     'Haikal Rizky',         'Hanif Kurniawan',    'Haris Wibowo',      'Hendra Pratama',
            'Herdi Saputra',      'Hidayat Nugraha',      'Hilman Fauzi',       'Husen Abadi',       'Husni Fajar',
            'Ibnu Hasan',         'Ilham Maulana',        'Ilyas Firmansyah',   'Imam Prasetyo',     'Ivan Kurniawan',
            'Jaka Purnama',       'Jalu Wicaksono',       'Jeremy Santosa',     'Jonathan Putra',    'Joshua Permana',
            'Kafi Nugroho',       'Kamal Fauzan',         'Karel Santoso',      'Kevin Aditya',      'Khairul Anwar',
            'Lathif Ramadhan',    'Leo Prasetyo',         'Lutfi Hidayat',      'Luqman Ardiansyah', 'Luthfi Maulana',
            'Maulana Yusuf',      'Michael Saputra',      'Mirza Fauzan',       'Muhammad Iqbal',    'Muhammad Rafi',
            'Muhammad Rizki',     'Mulyadi Santoso',      'Munawir Hadi',       'Mustofa Aji',       'Nanda Arjuna',
            'Nathan Wijaya',      'Naufal Hidayat',       'Novan Setiawan',     'Nur Cahyo',         'Oscar Wibowo',
            'Panji Kusuma',       'Raffi Ahmad',          'Raga Pratama',       'Raka Wibisono',     'Rama Dhuha',
            'Randy Firmansyah',   'Rangga Saputra',       'Ravi Kurniawan',     'Rayhan Fadhil',     'Satria Nugraha',
        ];
    }

    // =========================================
    // POOL NAMA PEREMPUAN — 110 nama
    // =========================================
    private function getFemaleNames(): array
    {
        return [
            'Adelia Putri',       'Adellia Ramadhani',   'Adinda Sari',        'Afifah Nurul',      'Agnesia Dewi',
            'Ainun Jariah',       'Ajeng Puspita',        'Alya Fadillah',      'Amanda Aulia',      'Amelia Safitri',
            'Bella Aurellia',     'Bella Permata',        'Bening Cahaya',      'Berliana Sari',     'Bunga Aprilia',
            'Cahaya Insani',      'Cantika Maharani',     'Celine Aurel',       'Chika Aprilia',     'Citra Lestari',
            'Dea Amalia',         'Della Rahayu',         'Desi Wulandari',     'Dessy Nuraini',     'Devina Salsabila',
            'Dian Pertiwi',       'Dinda Aulia',          'Dinda Maharani',     'Dwi Rahayu',        'Dyah Puspita',
            'Elena Putri',        'Elisa Ramadhani',      'Elsa Nurmala',       'Elvira Sari',       'Ema Surya',
            'Erna Wati',          'Evelin Cahya',         'Evelyn Putri',       'Evy Rahayu',        'Eza Amalia',
            'Fadhila Sari',       'Farah Aulia',          'Farah Nisa',         'Faridah Hanim',     'Fatimah Azzahra',
            'Feby Ramadhani',     'Felicia Putri',        'Felistia Ananda',    'Feni Rahayu',       'Fira Aprilia',
            'Ghina Salsabila',    'Gina Aulia',           'Hana Apriliani',     'Hana Nurfadillah',  'Hasna Azzahra',
            'Haura Salma',        'Hesti Wulandari',      'Hikmah Sari',        'Husna Ramadhani',   'Husnul Khatimah',
            'Inara Putri',        'Indah Kurnia',         'Indri Lestari',      'Inka Ramadhani',    'Isna Wati',
            'Istiqomah Sari',     'Jasmine Aulia',        'Jessica Putri',      'Jihan Nazla',       'Julia Pertiwi',
            'Kaila Putri',        'Kamila Ramadhani',     'Keysha Aulia',       'Khaira Nisa',       'Khofifah Indar',
            'Laila Nurul',        'Laras Wulandari',      'Latifah Sari',       'Lilis Suryani',     'Lina Marlina',
            'Maharani Putri',     'Marisya Aulia',        'Marwa Salsabila',    'Maulidya Sari',     'Maylani Putri',
            'Melani Rahayu',      'Mia Ramadhani',        'Milda Sari',         'Mira Andriyani',    'Mutiara Dewi',
            'Nabila Azzahra',     'Nadia Ramadhani',      'Nafisa Putri',       'Naila Salsabila',   'Najwa Aulia',
            'Nanda Permata',      'Naysilla Putri',       'Nesa Ramadhani',     'Nila Sari',         'Nurul Fadillah',
            'Nurul Hikmah',       'Oktavia Sari',         'Pita Rahayu',        'Putri Ananda',      'Rahma Aulia',
            'Rara Anjani',        'Reva Aulia',           'Rima Wulandari',     'Rina Pertiwi',      'Rizky Amalia',
        ];
    }
}