<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TokensManageCommand extends Command
{
    protected $signature = 'tokens:manage {action} {userId=1}';

    protected $description = 'Manage access tokens';

    public function handle(): void
    {
        $user = User::find($this->argument('userId'));
        if ($this->argument('action') == 'create') {
            $token = $user->createToken('main');
            $this->info($token->plainTextToken);
        } elseif ($this->argument('action') == 'revoke') {
            $user->tokens()->delete();
            $this->info('Tokens revoked');
        }
    }
}
