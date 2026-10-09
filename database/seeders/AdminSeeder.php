<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hamatetsu.com'],
            [
                'name' => 'Admin QC',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Admin berhasil dibuat:');
        $this->command->info('   Email: admin@hamatetsu.com');
        $this->command->info('   Password: admin123');
    }
}