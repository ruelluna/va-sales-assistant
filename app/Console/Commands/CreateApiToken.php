<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateApiToken extends Command
{
    protected $signature = 'passport:create-token {email : The email of the user} {--name=API Token : The name of the token}';

    protected $description = 'Create a Sanctum API token for a user by email';

    public function handle(): int
    {
        $email = $this->argument('email');
        $tokenName = $this->option('name');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email '{$email}' not found.");

            return self::FAILURE;
        }

        $token = $user->createToken($tokenName);

        $this->info("API token created successfully for {$user->email}!");
        $this->line('');
        $this->line("Token Name: {$tokenName}");
        $this->line("Token: {$token->plainTextToken}");
        $this->line('');
        $this->warn('Make sure to copy this token now. You won\'t be able to see it again!');

        return self::SUCCESS;
    }
}
