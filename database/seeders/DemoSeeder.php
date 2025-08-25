<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Estudiante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@uth.edu.mx'],
            ['name' => 'Admin UTH','role' => 'ADMIN','password' => Hash::make('password')]
        );

        $caja = User::updateOrCreate(
            ['email' => 'caja@uth.edu.mx'],
            ['name' => 'Caja UTH','role' => 'CAJA','password' => Hash::make('password')]
        );

        $estu = User::updateOrCreate(
            ['email' => '3522110216@uth.edu.mx'],
            ['name' => 'Jafet Hernández Chantes','role' => 'ESTUDIANTE','password' => Hash::make('password')]
        );

        Estudiante::updateOrCreate(
            ['user_id' => $estu->id],
            [
                'matricula' => '3522110216',
                'nombre' => 'Jafet',
                'apellido_paterno' => 'Hernández',
                'apellido_materno' => 'Chantes',
                'correo_institucional' => '3522110216@uth.edu.mx',
                'correo_personal' => 'afertchantes@gmail.com',
                'telefono_movil' => '2223312918',
                'telefono_padre' => '2222991351',
                'telefono_madre' => '2211413749',
                'direccion' => 'Calle Agustín de Iturbide 42, San Miguel Xoxtla, Puebla, CP 72620',
                'tutor_trato' => 'Sr.',
                'tutor_nombre' => 'Guilevaldo',
                'tutor_apellido_paterno' => 'Hernández',
                'tutor_apellido_materno' => 'Romero'
            ]
        );
    }
}
