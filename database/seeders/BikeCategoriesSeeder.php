<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BikeCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer les catégories existantes (optionnel en dev)
        \DB::table('categories')->delete();

        // Catégories principales et leurs enfants
        $categories = [
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'VTT',
                'slug' => 'vtt',
                'description' => 'Vélos Tout-Terrain',
                'is_default' => true,
                'display_order' => 1,
                'parent_id' => null,
                'children' => [
                    ['name' => 'XC', 'slug' => 'xc', 'description' => 'Cross-Country', 'display_order' => 1],
                    ['name' => 'DH', 'slug' => 'dh', 'description' => 'Descente / Downhill', 'display_order' => 2],
                    ['name' => 'Enduro', 'slug' => 'enduro', 'description' => 'Enduro / All-Mountain', 'display_order' => 3],
                    ['name' => 'Trail', 'slug' => 'trail', 'description' => 'Trail', 'display_order' => 4],
                ],
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Route',
                'slug' => 'route',
                'description' => 'Vélos de Route',
                'is_default' => true,
                'display_order' => 2,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Route classique', 'slug' => 'route-classique', 'description' => 'Vélo de route classique', 'display_order' => 1],
                    ['name' => 'Contre la montre', 'slug' => 'clm', 'description' => 'Contre-la-montre / Triathlon', 'display_order' => 2],
                    ['name' => 'Aéro', 'slug' => 'aero', 'description' => 'Vélo aérodynamique', 'display_order' => 3],
                ],
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Gravel / CX',
                'slug' => 'gravel-cx',
                'description' => 'Gravel et Cyclo-cross',
                'is_default' => true,
                'display_order' => 3,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Gravel', 'slug' => 'gravel', 'description' => 'Gravel', 'display_order' => 1],
                    ['name' => 'CX', 'slug' => 'cx', 'description' => 'Cyclo-cross', 'display_order' => 2],
                ],
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Électrique',
                'slug' => 'electrique',
                'description' => 'Vélos à assistance électrique',
                'is_default' => true,
                'display_order' => 4,
                'parent_id' => null,
                'children' => [
                    ['name' => 'VTT électrique', 'slug' => 'vttae', 'description' => 'E-MTB / VTTAE', 'display_order' => 1],
                    ['name' => 'VAE urbain', 'slug' => 'vae-urbain', 'description' => 'Vélo électrique urbain', 'display_order' => 2],
                    ['name' => 'Gravel électrique', 'slug' => 'gravel-electrique', 'description' => 'E-Gravel', 'display_order' => 3],
                ],
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Urbain',
                'slug' => 'urbain',
                'description' => 'Vélos urbains et de ville',
                'is_default' => true,
                'display_order' => 5,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Ville', 'slug' => 'ville', 'description' => 'Vélo de ville', 'display_order' => 1],
                    ['name' => 'Pliant', 'slug' => 'pliant', 'description' => 'Vélo pliant', 'display_order' => 2],
                    ['name' => 'Cargo', 'slug' => 'cargo', 'description' => 'Vélo cargo', 'display_order' => 3],
                ],
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Piste / BMX',
                'slug' => 'piste-bmx',
                'description' => 'Vélos de piste et BMX',
                'is_default' => true,
                'display_order' => 6,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Piste', 'slug' => 'piste', 'description' => 'Vélo de piste', 'display_order' => 1],
                    ['name' => 'BMX Race', 'slug' => 'bmx-race', 'description' => 'BMX de course', 'display_order' => 2],
                    ['name' => 'BMX Freestyle', 'slug' => 'bmx-freestyle', 'description' => 'BMX freestyle', 'display_order' => 3],
                ],
            ],
        ];

        foreach ($categories as $mainCat) {
            $children = $mainCat['children'] ?? [];
            unset($mainCat['children']);

            // Insérer la catégorie principale
            \DB::table('categories')->insert([
                ...$mainCat,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insérer les sous-catégories
            foreach ($children as $child) {
                \DB::table('categories')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'name' => $child['name'],
                    'slug' => $child['slug'],
                    'description' => $child['description'] ?? null,
                    'is_default' => true,
                    'display_order' => $child['display_order'],
                    'parent_id' => $mainCat['id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
