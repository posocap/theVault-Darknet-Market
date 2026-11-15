<?php

namespace App\Console\Commands;

use App\Admin;
use App\Marketplace\Encryption\Keypair;
use App\Marketplace\Utility\Mnemonic;
use App\User;
use Defuse\Crypto\Crypto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates (or resets) an admin account the same way the sign-up form builds a user.
 * Used by deploy/provision.sh; prints "MNEMONIC: ..." once for a newly created account.
 */
class CreateAdmin extends Command
{
    protected $signature = 'vault:create-admin {username} {--password-env=ADMIN_PASS : Env var holding the password}';
    protected $description = 'Create an admin user (idempotent: an existing user is promoted and its password reset)';

    public function handle()
    {
        $username = $this->argument('username');
        $password = getenv($this->option('password-env'));
        if (!$password) {
            $this->error('Password env var is empty.');
            return 1;
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            $keyPair = new Keypair();
            $mnemonic = (new Mnemonic())->generate(config('marketplace.mnemonic_length'));
            $user = new User();
            $user->username = $username;
            $user->mnemonic = bcrypt(hash('sha256', $mnemonic));
            $user->referral_code = strtoupper(Str::random(6));
            $user->msg_public_key = encrypt($keyPair->getPublicKey());
            $user->msg_private_key = Crypto::encryptWithPassword($keyPair->getPrivateKey(), $password);
            $this->line('MNEMONIC: ' . $mnemonic);
        }
        $user->password = bcrypt($password);
        $user->save();

        if (!DB::table('admins')->where('id', $user->id)->exists()) {
            DB::table('admins')->insert(['id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        }
        try {
            $user->generateDepositAddresses();
        } catch (\Throwable $e) {
            // simulation mode: ignore
        }
        $this->info("Admin '{$username}' ready.");
        return 0;
    }
}
