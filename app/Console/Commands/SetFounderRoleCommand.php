<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;

/**
 * Production / staging: run {@see Migrator} first so
 * {@code founder_work_cards} exists before founders use the inbox.
 */
class SetFounderRoleCommand extends Command
{
    protected $signature = 'ops:set-founder-role
                            {email : User email (must exist)}
                            {--dry-run : Show the change without saving}
                            {--force : Required when the user is currently Admin_PM}';

    protected $description = 'Assign the Founder role to a user (thin Ops inbox only). Use --force when demoting from Admin_PM.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $user = User::query()->where('email', $email)->first();
        if ($user === null) {
            $this->error('No user found for email: '.$email);

            return self::FAILURE;
        }

        $this->line('User: '.$user->name.' <'.$user->email.'> (current role: '.$user->role.')');

        if ($user->role === 'Founder') {
            $this->info('Already Founder. Nothing to do.');
            $this->comment('Reminder: run `php artisan migrate --force` on this environment if founder_work_cards is missing.');

            return self::SUCCESS;
        }

        if ($user->role === 'Admin_PM' && ! $this->option('force')) {
            $this->error('This user is Admin_PM. Re-run with --force to switch them to Founder (they will lose full builder access).');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->warn('[dry-run] Would set role to Founder.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($user): void {
            $user->role = 'Founder';
            $user->save();
        });

        $this->info('Role updated to Founder.');
        $this->comment('Reminder: run `php artisan migrate --force` on this environment if founder_work_cards is missing.');

        return self::SUCCESS;
    }
}
