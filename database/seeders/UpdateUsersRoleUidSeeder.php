<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateUsersRoleUidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if (!$user->role_uid) {
                $prefix = match(true) {
                    $user->hasRole('member')    => 'MBR',
                    $user->hasRole('admin')     => 'ADM',
                    $user->hasRole('employee')  => 'EMP',
                    $user->hasRole('moderator') => 'MOD',
                    default                     => 'USR',
                };

                $user->role_uid = $prefix . '-' . strtoupper(uniqid());
                $user->save();
            }
        }
    }
}
