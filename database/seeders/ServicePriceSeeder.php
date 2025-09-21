<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\VehicleType;
use App\Models\ServicePrice;

class ServicePriceSeeder extends Seeder
{
    public function run()
    {
        $serviceOptions = [
            'Lavado y Secado' => [
                ['label' => 'Pelo Corto', 'price' => 450],
                ['label' => 'Pelo Largo', 'price' => 550],
            ],
            'Lavado con línea' => [
                ['label' => 'Regular', 'price' => 1000],
            ],
            'Corte' => [
                ['label' => 'Puntas', 'price' => 800],
                ['label' => 'Elaborado', 'price' => 2000],
            ],
            'Retoque de tinte' => [
                ['label' => 'Simple', 'price' => 1500],
                ['label' => 'Elaborado', 'price' => 2000],
            ],
            'Aplicación Tinte' => [
                ['label' => 'Pelo Corto', 'price' => 3000],
                ['label' => 'Pelo Largo', 'price' => 4000],
            ],
            'Highlights' => [
                ['label' => 'Pelo Corto', 'price' => 5000],
                ['label' => 'Pelo Largo', 'price' => 8000],
            ],
            'Retoque de Mechas' => [
                ['label' => 'Regular', 'price' => 5000],
            ],
            'Balayage + matizado + tratamiento' => [
                ['label' => 'Pelo Corto', 'price' => 10000],
                ['label' => 'Pelo Largo', 'price' => 15000],
            ],
            'Colocar Leave in' => [
                ['label' => 'Regular', 'price' => 100],
            ],
            'Ondas' => [
                ['label' => 'Pelo Corto', 'price' => 200],
                ['label' => 'Pelo Largo', 'price' => 300],
            ],
            'Manos y Pies' => [
                ['label' => 'Manos - Pintura Normal', 'price' => 250],
                ['label' => 'Pies - Pintura Normal', 'price' => 250],
                ['label' => 'Gel Mano y Pie', 'price' => 450],
                ['label' => 'Manicure Ruso', 'price' => 500],
                ['label' => 'Pedicure Ruso', 'price' => 500],
                ['label' => 'Retiro de gel', 'price' => 150],
            ],
            'Pedicure kit' => [
                ['label' => 'Regular', 'price' => 450],
            ],
            'Retoque' => [
                ['label' => 'Acrílico', 'price' => 600],
                ['label' => 'Acrílico Full', 'price' => 950],
            ],
        ];

        foreach ($serviceOptions as $serviceName => $options) {
            /** @var Service|null $service */
            $service = Service::where('name', $serviceName)->first();

            if (!$service) {
                continue;
            }

            $labels = collect($options)->pluck('label')->all();

            ServicePrice::where('service_id', $service->id)
                ->whereNotIn('label', $labels)
                ->get()
                ->each(function (ServicePrice $price) {
                    $vehicleType = $price->vehicleType;
                    $price->delete();

                    if ($vehicleType && $vehicleType->servicePrices()->count() === 0 && $vehicleType->vehicles()->count() === 0) {
                        $vehicleType->delete();
                    }
                });

            foreach ($options as $option) {
                $vehicleType = VehicleType::firstOrCreate(['name' => $option['label']]);

                ServicePrice::updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'vehicle_type_id' => $vehicleType->id,
                    ],
                    [
                        'label' => $option['label'],
                        'price' => $option['price'],
                    ]
                );
            }
        }
    }
}

