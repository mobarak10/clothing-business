<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Editorial and legal pages. Body copy is deliberately short -- these are
     * placeholders for the client's own words, not finished text.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'The House',
                'excerpt' => 'Three generations of tailoring, made for a modern wardrobe.',
                'body' => "<p>Every garment begins on the cutting table, where a pattern is drawn to a measurement rather than a size chart. What follows is slower than it needs to be, and that is the point.</p><p>Replace this copy with the brand's own story before launch.</p>",
            ],
            [
                'slug' => 'made-to-measure',
                'title' => 'Made to Measure',
                'excerpt' => 'A suit cut to your measurements, finished in four weeks.',
                'body' => "<p>Book an appointment at a boutique offering Made to Measure. A fitting takes about an hour: choose the cloth, the cut and the details, and we take the measurements that shape the pattern.</p>",
            ],
            [
                'slug' => 'care',
                'title' => 'Garment Care',
                'excerpt' => 'How to keep tailoring, knitwear and leather at their best.',
                'body' => "<p>Rest a suit for a day between wearings and hang it on a shaped hanger. Brush rather than dry clean where you can -- cleaning is hard on cloth and should be occasional.</p>",
            ],
            [
                'slug' => 'shipping-and-returns',
                'title' => 'Shipping & Returns',
                'excerpt' => 'Delivery times, costs and how to return an order.',
                'body' => "<p>Standard delivery arrives in three to five business days and is complimentary above ৳5,000. Returns are accepted within 14 days, unworn and with tags attached.</p>",
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'excerpt' => 'The terms governing use of this site.',
                'body' => '<p>Placeholder. Replace with terms reviewed by a legal advisor before launch.</p>',
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'excerpt' => 'How personal data is collected and used.',
                'body' => '<p>Placeholder. Replace with a privacy policy reviewed by a legal advisor before launch.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page + [
                    'is_published' => true,
                    'meta_title' => $page['title'],
                    'meta_description' => $page['excerpt'],
                ],
            );
        }

        $this->command?->info('  Pages: '.count($pages).' published');
    }
}
