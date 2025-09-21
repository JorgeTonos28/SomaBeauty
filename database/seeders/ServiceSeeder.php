<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            ['name' => 'Lavado y Secado', 'description' => 'Lavado y secado profesional adaptado a cada tipo de cabello.'],
            ['name' => 'Lavado con línea', 'description' => 'Lavado con definición de línea y peinado base.'],
            ['name' => 'Corte', 'description' => 'Cortes de cabello y estilos personalizados.'],
            ['name' => 'Retoque de tinte', 'description' => 'Retoque para mantener el color del cabello vibrante.'],
            ['name' => 'Aplicación Tinte', 'description' => 'Aplicación completa de tintes y tonos para el cabello.'],
            ['name' => 'Highlights', 'description' => 'Iluminaciones y mechas para resaltar el cabello.'],
            ['name' => 'Retoque de Mechas', 'description' => 'Mantenimiento de mechas existentes con un acabado profesional.'],
            ['name' => 'Balayage + matizado + tratamiento', 'description' => 'Servicio completo de balayage con matizado y tratamiento.'],
            ['name' => 'Colocar Leave in', 'description' => 'Aplicación de tratamientos leave-in para el cuidado diario.'],
            ['name' => 'Ondas', 'description' => 'Ondas y peinados con calor para eventos y ocasiones especiales.'],
            ['name' => 'Manos y Pies', 'description' => 'Servicios de manicura y pedicura con opciones especializadas.'],
            ['name' => 'Pedicure kit', 'description' => 'Pedicure con kit individual para máxima higiene.'],
            ['name' => 'Retoque', 'description' => 'Servicios de retoque para uñas acrílicas.'],
        ];

        foreach ($services as $serviceData) {
            Service::updateOrCreate(
                ['name' => $serviceData['name']],
                [
                    'description' => $serviceData['description'],
                    'active' => true,
                ]
            );
        }
    }
}

