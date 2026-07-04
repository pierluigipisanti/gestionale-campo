<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

// Crea o aggiorna un utente amministratore con credenziali scelte.
//   php artisan crea:admin
class CreaAdmin extends Command
{
    protected $signature = 'crea:admin';

    protected $description = 'Crea (o aggiorna) un utente amministratore';

    public function handle(): int
    {
        $email = $this->ask('Email');
        $password = $this->secret('Password (min 6 caratteri)');
        $name = $this->ask('Nome', 'Amministratore');

        $errori = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', 'min:6']]
        )->errors();

        if ($errori->isNotEmpty()) {
            foreach ($errori->all() as $msg) {
                $this->error($msg);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password, 'ruolo' => 'admin']
        );

        $this->info("Amministratore pronto: {$user->email}");

        return self::SUCCESS;
    }
}
