<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * The storefront's navigation tree.
     *
     * Depth here is what the mega-menu renders: top level becomes a menu
     * heading, second level becomes its columns, third level becomes the
     * refinement links inside a listing page.
     */
    public function run(): void
    {
        $tree = [
            [
                'name' => 'New Arrivals',
                'featured' => true,
                'description' => 'The latest pieces to enter the collection.',
            ],
            [
                'name' => 'Clothing',
                'description' => 'Tailoring, knitwear and outerwear built to last.',
                'children' => [
                    ['name' => 'Suits', 'children' => [
                        ['name' => 'Blue Suits'],
                        ['name' => 'Grey Suits'],
                        ['name' => 'Formal Wear'],
                    ]],
                    ['name' => 'Blazers'],
                    ['name' => 'Jackets'],
                    ['name' => 'Coats'],
                    ['name' => 'Knitwear', 'children' => [
                        ['name' => 'Crewnecks'],
                        ['name' => 'Cardigans'],
                    ]],
                    ['name' => 'Shirts', 'featured' => true, 'children' => [
                        ['name' => 'Dress Shirts'],
                        ['name' => 'Casual Shirts'],
                    ]],
                    ['name' => 'Pants', 'children' => [
                        ['name' => 'Dress Pants'],
                        ['name' => 'Casual Pants'],
                    ]],
                    ['name' => 'Polos & T-Shirts'],
                    ['name' => 'Jeans'],
                ],
            ],
            [
                'name' => 'Shoes',
                'description' => 'Hand-finished footwear from Italian workshops.',
                'children' => [
                    ['name' => 'Dress & Casual Shoes'],
                    ['name' => 'Loafers'],
                    ['name' => 'Sneakers', 'featured' => true],
                ],
            ],
            [
                'name' => 'Accessories',
                'description' => 'The details that finish a wardrobe.',
                'children' => [
                    ['name' => 'Bags'],
                    ['name' => 'Belts'],
                    ['name' => 'Ties'],
                    ['name' => 'Bow Ties'],
                    ['name' => 'Pocket Squares'],
                    ['name' => 'Scarves'],
                    ['name' => 'Gloves'],
                    ['name' => 'Hats'],
                ],
            ],
        ];

        $created = $this->createBranch($tree);

        $this->command?->info("  Categories: {$created} created");
    }

    /**
     * Walks the definition depth-first. Category's saving hook derives `path`
     * from the parent, so parents must exist before their children -- which
     * recursion gives us for free.
     */
    private function createBranch(array $nodes, ?Category $parent = null): int
    {
        $count = 0;
        $position = 0;

        foreach ($nodes as $node) {
            $category = Category::updateOrCreate(
                [
                    'parent_id' => $parent?->id,
                    'slug' => Str::slug($node['name']),
                ],
                [
                    'name' => $node['name'],
                    'description' => $node['description'] ?? null,
                    'position' => $position++,
                    'is_active' => true,
                    'is_featured' => $node['featured'] ?? false,
                    'meta_title' => $node['name'],
                    'meta_description' => $node['description'] ?? null,
                ],
            );

            $count++;

            if (! empty($node['children'])) {
                $count += $this->createBranch($node['children'], $category);
            }
        }

        return $count;
    }
}
