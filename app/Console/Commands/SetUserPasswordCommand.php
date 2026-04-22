<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Sets a user's password (hashed via {@see User} casts). Use when Filament-created
 * accounts need a known password or credentials were lost.
 */
class SetUserPasswordCommand extends Command
{
    protected $signature = 'user:set-password
                            {email : User email (case-insensitive match)}
                            {password? : Plain password; omit to generate one}
                            {--force : Required in production environment}';

    protected $description = 'Set a user password (development / recovery). In production, pass --force after confirming intent.';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to change a password in production without --force.');

            return self::FAILURE;
        }

        $needle = strtolower(trim((string) $this->argument('email')));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$needle])
            ->first();

        if ($user === null) {
            $this->error('No user found for email matching: '.$needle);

            return self::FAILURE;
        }

        $plain = $this->argument('password');
        if ($plain === null || $plain === '') {
            $plain = Str::password(24);
            $this->warn('No password argument — generated one (copy now):');
            $this->line($plain);
        } else {
            $this->info('Password updated from CLI argument.');
        }

        $user->password = $plain;
        $user->save();

        $this->info(sprintf('Updated password for %s <%s> (role: %s).', $user->name, $user->email, $user->role));

        return self::SUCCESS;
    }
}
