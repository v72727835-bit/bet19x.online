<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Naya admin panel account banayein';

    public function handle(): int
    {
        $name = $this->ask('Admin ka naam');
        $email = $this->ask('Admin ka email');
        $password = $this->secret('Password (kam se kam 8 characters)');

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin account ban gaya: {$email}");
        return self::SUCCESS;
    }
}
