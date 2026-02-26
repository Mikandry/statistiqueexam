<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        User::updateOrCreate(
            ['email' => 'mikandry7@gmail.com'],
            [
                'name' => 'Administrateur',
                'role' => 'admin',
                'password' => Hash::make('motdepasse'),
            ]
        );
    }

    public function down(): void
    {
        User::query()->where('email', 'mikandry7@gmail.com')->delete();
    }
};
