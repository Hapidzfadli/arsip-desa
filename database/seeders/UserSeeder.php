<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Bagian;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Sekretaris Desa account (main admin)
        $sekdes = User::create([
            'username' => 'sekdes',
            'password' => Hash::make('sekdes123'), // You should change this password
            'nama_lengkap' => 'Sekretaris Desa',
            'email' => 'sekdes@desa.id',
            'alamat' => 'Alamat Desa',
            'telp' => '08198765432',
            'pengalaman' => 'Sekretaris Desa Periode 2024-2029',
            'level' => 'admin',
            'status' => 'aktif',
            'tgl_daftar' => now()->format('d-m-Y H:i:s')
        ]);

        // Create department for Sekdes
        Bagian::create([
            'nama_bagian' => 'sekdes',
            'user_id' => $sekdes->id
        ]);

        // Create Admin Desa account
        $adminDesa = User::create([
            'username' => 'admindesa',
            'password' => Hash::make('admin123'), // You should change this password
            'nama_lengkap' => 'Admin Desa',
            'email' => 'admin@desa.id',
            'alamat' => 'Alamat Desa',
            'telp' => '08198765432',
            'pengalaman' => 'Admin Sistem Desa',
            'level' => 'admin',
            'status' => 'aktif',
            'tgl_daftar' => now()->format('d-m-Y H:i:s')
        ]);

        // Create department for Admin
        Bagian::create([
            'nama_bagian' => 'sekdes',
            'user_id' => $adminDesa->id
        ]);
    }
}
