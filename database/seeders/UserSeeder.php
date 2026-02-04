<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $pembina = User::create([
            'name' => 'Pembina 1',
            'username' => 'pembina1',
            'password' => Hash::make('password'),
        ]);
        $pembina->assignRole('pembina');

        for ($i = 1; $i <= 5; $i++) {
            $siswa = User::create([
                'name' => 'Siswa '.$i,
                'nisn' => '1000'.$i,
                'kelas' => '10A',
                'password' => Hash::make('password'),
            ]);
            $siswa->assignRole('siswa');
        }
    }
}
