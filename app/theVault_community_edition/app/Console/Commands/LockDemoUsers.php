<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The demo dump ships three fictional accounts with plaintext placeholder passwords.
 * Keep them (they own the demo listings) but make them impossible to log into.
 */
class LockDemoUsers extends Command
{
    protected $signature = 'vault:lock-demo-users';
    protected $description = 'Replace demo users\' placeholder passwords/mnemonics with random bcrypt hashes';

    public function handle()
    {
        $n = 0;
        foreach (['celebdna', 'celebdna2', 'celebdna3'] as $name) {
            $n += DB::table('users')->where('username', $name)->update([
                'password' => bcrypt(Str::random(48)),
                'mnemonic' => bcrypt(Str::random(48)),
            ]);
        }
        $this->info("Locked {$n} demo users.");
        return 0;
    }
}
