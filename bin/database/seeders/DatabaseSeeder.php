<?php

namespace Database\Seeders;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'role' => 1,
            'password' => bcrypt('123456'),
        ]);

        User::factory()->create([
            'name' => 'Conexión 1',
            'email' => 'conexion1@example.com',
            'role' => 3,
            'password' => bcrypt('123456'),
        ]);

        Connection::factory()->create([
            "status"=>"DISCONNECTED",
            "number"=>"",
            "name"=>"CONEXIÓN ADMIN",
            "qr_code"=>"PENDING",
            "greeting_message"=>"Hola bienvenido a SEFIL S.A.",
            "farewell_message"=>"Muchas gracias. Esperamos verte pronto",
            "user_id"=>1
        ]);

        Connection::factory()->create([
            "status"=>"DISCONNECTED",
            "number"=>"",
            "name"=>"CONEXIÓN GESTOR",
            "qr_code"=>"PENDING",
            "greeting_message"=>"Hola bienvenido a SEFIL S.A.",
            "farewell_message"=>"Muchas gracias. Esperamos verte pronto",
            "user_id"=>2
        ]);
    }
}
