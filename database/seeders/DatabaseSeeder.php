<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;
use App\Support\ClientPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
 public function run()
 {
   $clientRole=Role::firstOrCreate(['name'=>'Admin','guard_name'=>'web']);
   Role::firstOrCreate(['name'=>'Staff','guard_name'=>'web']);
   Role::firstOrCreate(['name'=>'Telecaller','guard_name'=>'web']);
   $clientRole->syncPermissions(collect(ClientPermissions::keys())->map(fn($k)=>Permission::firstOrCreate(['name'=>$k,'guard_name'=>'web'])));

   $client=Client::firstOrCreate(['email'=>'admin@example.com'],[
      'client_code'=>'CL-'.strtoupper(Str::random(8)),
      'company_name'=>'Admin',
      'owner_name'=>'Admin',
      'status'=>'active',
   ]);

   $user=User::firstOrCreate(['email'=>'admin@example.com'],[
      'name'=>'Admin',
      'client_id'=>$client->id,
      'password'=>Hash::make('password')
   ]);
   $user->assignRole($clientRole);
 }
}