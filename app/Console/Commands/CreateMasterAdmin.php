<?php

namespace App\Console\Commands;

use App\Models\MasterAdmin;
use Illuminate\Console\Command;

class CreateMasterAdmin extends Command
{
    protected $signature = 'master:create-admin
        {--name= : Name}
        {--email= : Login email}
        {--password= : Login password}';

    protected $description = 'Create (or update) the Master Panel login used to manage white-label tenant domains';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        if (! $name || ! $email || ! $password) {
            $this->error('Name, email aur password teeno chahiye.');

            return self::FAILURE;
        }

        $admin = MasterAdmin::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt($password)]
        );

        $this->info("Master Panel login ready: {$admin->email}");
        $this->comment('Ab /master/login pe jaake login karo.');

        return self::SUCCESS;
    }
}
