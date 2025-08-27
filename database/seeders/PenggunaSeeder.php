<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // make user peran admin with username admin and password admin123
        DB::table('pengguna')->insert([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'peran' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
