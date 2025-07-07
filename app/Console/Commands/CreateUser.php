<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Gunakan: php artisan make:user
     * Atau:    php artisan make:user --name=John --email=john@example.com --password=secret
     */
    protected $signature = 'make:user 
                            {--name= : The name of the user} 
                            {--email= : The email address} 
                            {--password= : The password}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new user account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        // Validasi sederhana
        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists.');
            return;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now()
        ]);

        $this->info("User created successfully:");
        $this->line("Name: $user->name");
        $this->line("Email: $user->email");
    }
}
