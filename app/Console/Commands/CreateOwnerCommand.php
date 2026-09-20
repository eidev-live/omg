<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateOwnerCommand extends Command
{
    protected $signature = 'app:create-owner
                            {--name= : Nama pemilik}
                            {--email= : Email pemilik}
                            {--password= : Password (minimal 8 karakter)}';

    protected $description = 'Membuat atau memperbarui akun pemilik aplikasi';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama pemilik');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (minimal 8 karakter)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', Password::min(8)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        $this->info("Akun pemilik siap: {$user->email}");

        return self::SUCCESS;
    }
}
