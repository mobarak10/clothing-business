<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentBlockType;
use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    /**
     * The homepage, section by section.
     *
     * Each payload matches the shape its Vue component expects. Image paths
     * point at placeholders under frontend/public/ -- swap them for real
     * campaign photography via the admin.
     */
    public function run(): void
    {
        $blocks = [
            [
                'type' => ContentBlockType::Hero,
                'admin_label' => 'Fall/Winter campaign',
                'payload' => [
                    'eyebrow' => 'Fall / Winter 2026',
                    'heading' => 'Weight, and the absence of it',
                    'body' => 'Double-faced coats, brushed flannel, and cashmere knitted fine enough to wear under a jacket.',
                    'cta_label' => 'View the collection',
                    'cta_url' => '/clothing',
                    'image' => '/images/placeholders/hero-fw26.jpg',
                    'image_mobile' => '/images/placeholders/hero-fw26-mobile.jpg',
                    'alignment' => 'left',
                    'theme' => 'light',
                ],
            ],
            [
                'type' => ContentBlockType::CategoryTiles,
                'admin_label' => 'Shop by category',
                'payload' => [
                    'heading' => 'The Collection',
                    'tiles' => [
                        ['label' => 'Suits', 'url' => '/clothing/suits', 'image' => '/images/placeholders/tile-suits.jpg'],
                        ['label' => 'Outerwear', 'url' => '/clothing/coats', 'image' => '/images/placeholders/tile-coats.jpg'],
                        ['label' => 'Knitwear', 'url' => '/clothing/knitwear', 'image' => '/images/placeholders/tile-knitwear.jpg'],
                        ['label' => 'Shoes', 'url' => '/shoes', 'image' => '/images/placeholders/tile-shoes.jpg'],
                    ],
                ],
            ],
            [
                'type' => ContentBlockType::ProductCarousel,
                'admin_label' => 'New arrivals strip',
                'payload' => [
                    'heading' => 'New Arrivals',
                    'subheading' => 'Just added to the collection.',
                    'source' => 'new_arrivals',
                    'category_path' => null,
                    'limit' => 12,
                    'cta_label' => 'See everything new',
                    'cta_url' => '/new-arrivals',
                ],
            ],
            [
                'type' => ContentBlockType::LookGrid,
                'admin_label' => 'Autumn looks',
                'payload' => [
                    'heading' => 'Three Ways to Wear It',
                    'looks' => [
                        [
                            'title' => 'The Soft Suit',
                            'caption' => 'Unstructured blazer, merino rollneck, suede loafers.',
                            'image' => '/images/placeholders/look-01.jpg',
                            'url' => '/clothing/blazers',
                        ],
                        [
                            'title' => 'Weekend Layers',
                            'caption' => 'Overshirt over a tee, chinos, leather sneakers.',
                            'image' => '/images/placeholders/look-02.jpg',
                            'url' => '/clothing/shirts/casual-shirts',
                        ],
                        [
                            'title' => 'Evening',
                            'caption' => 'Peak lapel tuxedo, grosgrain bow, polished oxfords.',
                            'image' => '/images/placeholders/look-03.jpg',
                            'url' => '/clothing/suits/formal-wear',
                        ],
                    ],
                ],
            ],
            [
                'type' => ContentBlockType::EditorialSplit,
                'admin_label' => 'Made to Measure',
                'payload' => [
                    'eyebrow' => 'Made to Measure',
                    'heading' => 'Cut to your measurements',
                    'body' => 'Choose the cloth, the cut and the details. A fitting takes about an hour; the finished suit follows in four weeks.',
                    'cta_label' => 'Book an appointment',
                    'cta_url' => '/made-to-measure',
                    'image' => '/images/placeholders/editorial-mtm.jpg',
                    'image_position' => 'right',
                ],
            ],
            [
                'type' => ContentBlockType::StoreFinder,
                'admin_label' => 'Find a boutique',
                'payload' => [
                    'heading' => 'Find a Boutique',
                    'body' => 'Five boutiques across Bangladesh, three offering Made to Measure.',
                    'cta_label' => 'Store finder',
                    'cta_url' => '/store-finder',
                    'image' => '/images/placeholders/editorial-store.jpg',
                ],
            ],
        ];

        foreach ($blocks as $position => $block) {
            ContentBlock::updateOrCreate(
                [
                    'placement' => 'homepage',
                    'admin_label' => $block['admin_label'],
                ],
                [
                    'type' => $block['type'],
                    'payload' => $block['payload'],
                    'position' => $position,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('  Content: '.count($blocks).' homepage blocks');
    }
}
