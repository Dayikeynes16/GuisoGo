<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::first();

        if (! $restaurant) {
            $this->command->warn('No restaurant found. Please create a restaurant first.');

            return;
        }

        $restaurantId = $restaurant->id;

        $categoriesData = [
            [
                'name' => 'Desayunos',
                'description' => 'Para empezar el día con toda la energía.',
                'image_keyword' => 'breakfast,eggs',
                'products' => [
                    [
                        'name' => 'Chilaquiles Tradicionales',
                        'description' => 'Chilaquiles verdes o rojos acompañados de frijoles refritos.',
                        'price' => 90.00,
                        'keyword' => 'chilaquiles,breakfast',
                        'modifiers' => [
                            [
                                'name' => 'Tipo de Salsa',
                                'selection_type' => 'single',
                                'is_required' => true,
                                'options' => [
                                    ['name' => 'Salsa Verde', 'price_adjustment' => 0],
                                    ['name' => 'Salsa Roja', 'price_adjustment' => 0],
                                    ['name' => 'Salsa Suiza (Gratinados)', 'price_adjustment' => 25.00],
                                ],
                            ],
                            [
                                'name' => 'Proteína Extra',
                                'selection_type' => 'single',
                                'is_required' => false,
                                'options' => [
                                    ['name' => 'Pollo deshebrado', 'price_adjustment' => 30.00],
                                    ['name' => '2 Huevos estrellados', 'price_adjustment' => 25.00],
                                    ['name' => 'Arrachera (150g)', 'price_adjustment' => 60.00],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Huevos al Gusto',
                        'description' => 'Dos huevos preparados a tu elección, con guarnición de frijoles.',
                        'price' => 75.00,
                        'keyword' => 'huevos,eggs,breakfast',
                        'modifiers' => [
                            [
                                'name' => 'Preparación',
                                'selection_type' => 'single',
                                'is_required' => true,
                                'options' => [
                                    ['name' => 'Revueltos con Jamón', 'price_adjustment' => 0],
                                    ['name' => 'Revueltos con Chorizo', 'price_adjustment' => 10.00],
                                    ['name' => 'Estrellados', 'price_adjustment' => 0],
                                    ['name' => 'A la Mexicana', 'price_adjustment' => 0],
                                ],
                            ],
                        ],
                    ],
                    ['name' => 'Hot Cakes Clásicos (3)', 'description' => 'Esponjosos hot cakes con mantequilla y miel (maple o abeja).', 'price' => 85.00, 'keyword' => 'pancakes,syrup'],
                    ['name' => 'Omelette Vegetariano', 'description' => 'Omelette relleno de espinacas, champiñones y queso panela.', 'price' => 95.00, 'keyword' => 'omelette,spinach'],
                ],
            ],
            [
                'name' => 'Entradas y Antojitos',
                'description' => 'Para empezar a abrir el apetito.',
                'image_keyword' => 'appetizer,tacos',
                'products' => [
                    ['name' => 'Guacamole con Totopos', 'description' => 'Guacamole fresco preparado al momento acompañado de totopos caseros.', 'price' => 85.00, 'keyword' => 'guacamole,nachos'],
                    ['name' => 'Queso Fundido con Chorizo', 'description' => 'Mezcla de quesos fundidos con chorizo artesanal.', 'price' => 120.00, 'keyword' => 'melted,cheese,chorizo'],
                    [
                        'name' => 'Sopes Sencillos (3)',
                        'description' => 'Sopes con frijoles, salsa, lechuga, crema y queso.',
                        'price' => 60.00,
                        'keyword' => 'sopes,mexican',
                        'modifiers' => [
                            [
                                'name' => 'Guarnición Adicional',
                                'selection_type' => 'multiple',
                                'is_required' => false,
                                'options' => [
                                    ['name' => 'Agg Pollo', 'price_adjustment' => 20.00],
                                    ['name' => 'Agg Carne Asada', 'price_adjustment' => 30.00],
                                ],
                            ],
                        ],
                    ],
                    ['name' => 'Orden de Tacos Dorados (4)', 'description' => 'Tacos dorados de pollo bañados en salsa verde, crema y queso.', 'price' => 95.00, 'keyword' => 'flautas,tacos'],
                    ['name' => 'Empanadas de Carne (3)', 'description' => 'Empanadas rellenas de carne molida preparada.', 'price' => 110.00, 'keyword' => 'empanadas,meat'],
                ],
            ],
            [
                'name' => 'Platos Fuertes',
                'description' => 'Los protagonistas de nuestra cocina.',
                'image_keyword' => 'steak,meat,meal',
                'products' => [
                    ['name' => 'Enchiladas Suizas', 'description' => 'Tradicionales enchiladas suizas rellenas de pollo con abundante queso gratinado.', 'price' => 145.00, 'keyword' => 'enchiladas,cheese'],
                    [
                        'name' => 'Tampiqueña',
                        'description' => 'Corte de res acompañado de guacamole, frijoles refritos y una enchilada.',
                        'price' => 195.00,
                        'keyword' => 'tampiquena,steak,mexican',
                        'modifiers' => [
                            [
                                'name' => 'Término de la carne',
                                'selection_type' => 'single',
                                'is_required' => true,
                                'options' => [
                                    ['name' => 'Rojo (Inglés)', 'price_adjustment' => 0],
                                    ['name' => 'Medio', 'price_adjustment' => 0],
                                    ['name' => 'Tres cuartos', 'price_adjustment' => 0],
                                    ['name' => 'Bien cocido', 'price_adjustment' => 0],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Hamburguesa Especial',
                        'description' => 'Carne artesanal, queso, tocino, aros de cebolla y papas a la francesa.',
                        'price' => 165.00,
                        'keyword' => 'burger,fries',
                        'modifiers' => [
                            [
                                'name' => 'Ingredientes Extra',
                                'selection_type' => 'multiple',
                                'is_required' => false,
                                'options' => [
                                    ['name' => 'Doble Carne', 'price_adjustment' => 45.00],
                                    ['name' => 'Extra Tocino', 'price_adjustment' => 20.00],
                                    ['name' => 'Extra Queso', 'price_adjustment' => 15.00],
                                ],
                            ],
                        ],
                    ],
                    ['name' => 'Milanesa de Pollo', 'description' => 'Pechuga de pollo empanizada acompañada de arroz y ensalada.', 'price' => 130.00, 'keyword' => 'breaded,chicken'],
                    ['name' => 'Chiles Rellenos', 'description' => 'Chiles poblanos rellenos de queso bañados en salsa de tomate tradicional.', 'price' => 140.00, 'keyword' => 'stuffed,peppers,mexican'],
                    ['name' => 'Fajitas de Pollo y Res', 'description' => 'Tiras de pollo y res asadas con pimientos y cebolla.', 'price' => 180.00, 'keyword' => 'fajitas,meat'],
                ],
            ],
            [
                'name' => 'Bebidas',
                'description' => 'Refrescantes y para todo gusto.',
                'image_keyword' => 'drinks,soda,juice',
                'products' => [
                    ['name' => 'Agua de Horchata (1L)', 'description' => 'Tradicional agua de horchata con un toque de canela.', 'price' => 45.00, 'keyword' => 'horchata,drink'],
                    ['name' => 'Agua de Jamaica (1L)', 'description' => 'Agua fresca de flor de jamaica.', 'price' => 45.00, 'keyword' => 'jamaica,drink'],
                    ['name' => 'Refresco de Cola (355ml)', 'description' => 'Refresco clásico helado.', 'price' => 35.00, 'keyword' => 'cola,soda'],
                    ['name' => 'Limonada Mineral', 'description' => 'Limonada preparada con agua mineral y un toque de sal.', 'price' => 50.00, 'keyword' => 'lemonade,sparkling'],
                    ['name' => 'Cerveza Nacional', 'description' => 'Cerveza clara u oscura bien fría.', 'price' => 60.00, 'keyword' => 'beer,bottle'],
                ],
            ],
            [
                'name' => 'Postres',
                'description' => 'El toque dulce perfecto para cerrar.',
                'image_keyword' => 'dessert,cake,sweet',
                'products' => [
                    ['name' => 'Flan Napolitano', 'description' => 'Delicioso flan casero tradicional con caramelo.', 'price' => 55.00, 'keyword' => 'flan,dessert'],
                    ['name' => 'Pastel 3 Leches', 'description' => 'Rebanada de suave pastel de tres leches.', 'price' => 65.00, 'keyword' => 'tres,leches,cake'],
                    ['name' => 'Churros con Chocolate', 'description' => 'Orden de 4 churros bañados en chocolate o cajeta.', 'price' => 70.00, 'keyword' => 'churros,chocolate'],
                    ['name' => 'Helado de Vainilla', 'description' => 'Dos bolas de helado artesanal de vainilla.', 'price' => 45.00, 'keyword' => 'vanilla,icecream'],
                ],
            ],
        ];

        $disk = Storage::disk('public');
        $catOrder = 1;

        foreach ($categoriesData as $catData) {
            $this->command->info("Creating category: {$catData['name']}");

            // Generate a random image for the category
            $catImagePath = 'categories/sample_'.Str::slug($catData['name']).'.jpg';
            if (! $disk->exists($catImagePath)) {
                $imageContent = @file_get_contents("https://loremflickr.com/600/400/{$catData['image_keyword']}");
                if ($imageContent) {
                    $disk->put($catImagePath, $imageContent);
                } else {
                    $catImagePath = null;
                }
            }

            $category = Category::create([
                'restaurant_id' => $restaurantId,
                'name' => $catData['name'],
                'description' => $catData['description'],
                'sort_order' => $catOrder++,
                'is_active' => true,
                'image_path' => $catImagePath,
            ]);

            $prodOrder = 1;

            foreach ($catData['products'] as $prodData) {
                // Generate a random image for the product
                $prodImagePath = 'products/sample_'.Str::slug($prodData['name']).'.jpg';
                if (! $disk->exists($prodImagePath)) {
                    $imageContent = @file_get_contents("https://loremflickr.com/600/400/{$prodData['keyword']}");
                    if ($imageContent) {
                        $disk->put($prodImagePath, $imageContent);
                    } else {
                        $prodImagePath = null;
                    }
                }

                $product = Product::create([
                    'restaurant_id' => $restaurantId,
                    'category_id' => $category->id,
                    'name' => $prodData['name'],
                    'description' => $prodData['description'],
                    'price' => $prodData['price'],
                    'production_cost' => $prodData['price'] * 0.4, // simple calculation
                    'sort_order' => $prodOrder++,
                    'is_active' => true,
                    'image_path' => $prodImagePath,
                ]);

                // Create modifiers if present
                if (isset($prodData['modifiers'])) {
                    $modGroupOrder = 1;
                    foreach ($prodData['modifiers'] as $modGroupData) {
                        $modGroup = \App\Models\ModifierGroup::create([
                            'restaurant_id' => $restaurantId,
                            'product_id' => $product->id,
                            'name' => $modGroupData['name'],
                            'selection_type' => $modGroupData['selection_type'],
                            'is_required' => $modGroupData['is_required'],
                            'sort_order' => $modGroupOrder++,
                        ]);

                        $optOrder = 1;
                        foreach ($modGroupData['options'] as $optData) {
                            \App\Models\ModifierOption::create([
                                'modifier_group_id' => $modGroup->id,
                                'name' => $optData['name'],
                                'price_adjustment' => $optData['price_adjustment'],
                                'production_cost' => $optData['price_adjustment'] * 0.4,
                                'sort_order' => $optOrder++,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('20 Dishes successfully seeded with images!');
    }
}
