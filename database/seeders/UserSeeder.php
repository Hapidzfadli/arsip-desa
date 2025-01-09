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
        // Create Kepala Desa account
        $kepalaDesa = User::create([
            'username' => 'kepaladesa',
            'password' => Hash::make('desa123'), // You should change this password
            'nama_lengkap' => 'Kepala Desa',
            'email' => 'kepaladesa@desa.id',
            'alamat' => 'Alamat Desa',
            'telp' => '08123456789',
            'pengalaman' => 'Kepala Desa Periode 2024-2029',
            'level' => 'admin', // Using admin level for broader access
            'status' => 'aktif',
            'tgl_daftar' => now()->format('d-m-Y H:i:s')
        ]);

        // Create department for Kepala Desa
        Bagian::create([
            'nama_bagian' => 'kades',
            'user_id' => $kepalaDesa->id
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
