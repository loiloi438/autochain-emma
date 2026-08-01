<?php

namespace App\Console\Commands;

use App\Services\BlockchainIndexer;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\DB;

class SyncBlockchainRoles extends Command
{
    protected $signature = 'autochain:sync-roles';

    protected $description = 'Synchronise les roles definis on-chain avec les utilisateurs (wallet_address)';

    public function handle(BlockchainIndexer $indexer): int
    {
        $roles = $indexer->roles();

        DB::transaction(function () use ($roles) {
            foreach ($roles as $roleName => $address) {
                $roleNameNorm = strtolower($roleName);
                SpatieRole::firstOrCreate(['name' => $roleNameNorm]);

                $user = User::where('wallet_address', strtolower($address))->first();
                if ($user) {
                    $user->assignRole($roleNameNorm);
                    $this->info("Assigned role {$roleNameNorm} to user {$user->id}");
                } else {
                    $this->warn("No local user with wallet {$address} for role {$roleNameNorm}");
                }
            }
        });

        $this->info('Roles synchronisés.');

        return self::SUCCESS;
    }
}
