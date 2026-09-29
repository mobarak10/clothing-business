<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\OptionValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Size runs by garment type. Referenced by the `sizes` key below so a
     * jacket never gets offered in collar sizes.
     */
    private const SIZE_RUNS = [
        'tailoring' => ['46', '48', '50', '52', '54', '56'],
        'knitwear' => ['S', 'M', 'L', 'XL', 'XXL'],
        'shirt' => ['38', '39', '40', '41', '42', '43'],
        'pants' => ['W30', 'W32', 'W34', 'W36', 'W38'],
        'shoes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44', 'EU 45'],
        'one' => ['One Size'],
    ];

    private Collection $optionValues;

    public function run(): void
    {
        // slug => id, looked up once instead of per variant.
        $this->optionValues = OptionValue::with('optionType')->get()->keyBy(
            fn (OptionValue $value) => $value->optionType->slug.':'.$value->slug
        );

        $productCount = 0;
        $variantCount = 0;

        foreach ($this->catalog() as $index => $definition) {
            $product = $this->createProduct($definition, $index);
            $variantCount += $this->createVariants($product, $definition);
            $this->attachCategories($product, $definition);
            $productCount++;
        }

        $this->command?->info("  Products: {$productCount} with {$variantCount} variants");
    }

    private function createProduct(array $definition, int $index): Product
    {
        $skuRoot = sprintf('%s%05d', strtoupper(substr($definition['sku_prefix'], 0, 3)), 10_000 + $index);

        $product = Product::updateOrCreate(
            ['sku_root' => $skuRoot],
            [
                'name' => $definition['name'],
                'slug' => Str::slug($definition['name'].' '.$skuRoot),
                'subtitle' => $definition['subtitle'] ?? null,
                'description' => $definition['description'],
                'composition' => $definition['composition'],
                'care_instructions' => $definition['care'] ?? 'Dry clean only. Cool iron if needed.',
                'fit' => $definition['fit'] ?? null,
                'base_price' => $definition['price'] * 100,
                'compare_at_price' => isset($definition['was']) ? $definition['was'] * 100 : null,
                'is_active' => true,
                'is_new_arrival' => $definition['new'] ?? false,
                'published_at' => now()->subDays(random_int(1, 120)),
                'meta_title' => $definition['name'],
                'meta_description' => Str::limit($definition['description'], 155),
            ],
        );

        return $product;
    }

    /** Builds the colour x size grid for one product. */
    private function createVariants(Product $product, array $definition): int
    {
        $sizes = self::SIZE_RUNS[$definition['sizes']];
        $colors = $definition['colors'];

        $created = 0;
        $position = 0;

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $sku = sprintf(
                    '%s-%s-%s',
                    $product->sku_root,
                    strtoupper(substr(Str::slug($color), 0, 3)),
                    strtoupper(str_replace(' ', '', $size)),
                );

                $variant = ProductVariant::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'product_id' => $product->id,
                        'price' => null,
                        'stock_quantity' => $this->stockFor($size, $sizes),
                        'weight_grams' => $definition['weight'] ?? 800,
                        'position' => $position++,
                        'is_active' => true,
                    ],
                );

                $variant->optionValues()->sync(array_filter([
                    $this->optionValueId('color', $color),
                    $this->optionValueId('size', $size),
                ]));

                $created++;
            }
        }

        return $created;
    }

    /**
     * Middle sizes sell through and get restocked; the extremes run thin.
     * Seeding this way means the storefront's out-of-stock and low-stock
     * states are visible without hand-editing rows.
     */
    private function stockFor(string $size, array $run): int
    {
        $index = array_search($size, $run, true);
        $middle = (count($run) - 1) / 2;
        $distance = abs($index - $middle);

        return match (true) {
            $distance <= 1 => random_int(8, 20),
            $distance <= 2 => random_int(2, 8),
            default => random_int(0, 3),
        };
    }

    private function attachCategories(Product $product, array $definition): void
    {
        $paths = (array) $definition['categories'];

        if ($definition['new'] ?? false) {
            $paths[] = 'new-arrivals';
        }

        $ids = Category::whereIn('path', $paths)->pluck('id');

        $product->categories()->sync(
            $ids->mapWithKeys(fn ($id) => [$id => ['position' => 0]])->all()
        );
    }

    private function optionValueId(string $type, string $name): ?int
    {
        return $this->optionValues->get($type.':'.Str::slug($name))?->id;
    }

    /**
     * The catalog. Prices are whole taka; the seeder converts to minor units.
     *
     * Category values are `path` strings, matching CategorySeeder's tree.
     */
    private function catalog(): array
    {
        return [
            // ---------------------------------------------------------------
            // Suits
            // ---------------------------------------------------------------
            [
                'name' => 'Impeccabile Wool Suit',
                'sku_prefix' => 'SUI',
                'subtitle' => 'Two-button, natural shoulder',
                'description' => 'A year-round suit cut from a crease-resistant wool that holds its line through long days. The jacket is half-lined for lightness, with a soft shoulder that follows the body rather than shaping it.',
                'composition' => '100% Virgin Wool',
                'fit' => 'Slim Fit',
                'price' => 189000,
                'sizes' => 'tailoring',
                'colors' => ['Navy', 'Charcoal', 'Midnight Blue'],
                'categories' => ['clothing/suits', 'clothing/suits/blue-suits'],
                'new' => true,
                'weight' => 1400,
            ],
            [
                'name' => 'Siena Grey Flannel Suit',
                'sku_prefix' => 'SUI',
                'subtitle' => 'Brushed flannel, winter weight',
                'description' => 'Cut from a soft brushed flannel milled in Biella, this suit trades sheen for depth. Best worn from autumn through the cold months, with a knitted tie and suede shoes.',
                'composition' => '100% Virgin Wool',
                'fit' => 'Regular Fit',
                'price' => 205000,
                'sizes' => 'tailoring',
                'colors' => ['Grey', 'Charcoal'],
                'categories' => ['clothing/suits', 'clothing/suits/grey-suits'],
                'weight' => 1500,
            ],
            [
                'name' => 'Nero Peak Lapel Tuxedo',
                'sku_prefix' => 'SUI',
                'subtitle' => 'Grosgrain facings',
                'description' => 'An evening suit with peak lapels faced in grosgrain silk and a matching stripe down the trouser. Cut close, lined in black cupro.',
                'composition' => '98% Virgin Wool, 2% Silk',
                'fit' => 'Slim Fit',
                'price' => 268000,
                'sizes' => 'tailoring',
                'colors' => ['Black', 'Midnight Blue'],
                'categories' => ['clothing/suits', 'clothing/suits/formal-wear'],
                'weight' => 1600,
            ],

            // ---------------------------------------------------------------
            // Blazers & Jackets
            // ---------------------------------------------------------------
            [
                'name' => 'Kei Unstructured Blazer',
                'sku_prefix' => 'BLZ',
                'subtitle' => 'Unlined, patch pockets',
                'description' => 'Built without canvas or lining so it moves like a cardigan and packs like one too. Patch pockets and a three-roll-two front keep it firmly informal.',
                'composition' => '70% Wool, 30% Silk',
                'fit' => 'Slim Fit',
                'price' => 132000,
                'sizes' => 'tailoring',
                'colors' => ['Navy', 'Olive', 'Sand'],
                'categories' => ['clothing/blazers'],
                'new' => true,
                'weight' => 900,
            ],
            [
                'name' => 'Doppio Double-Breasted Blazer',
                'sku_prefix' => 'BLZ',
                'description' => 'A six-on-two double-breasted jacket in a hopsack weave, with a sharp peak lapel and functioning cuffs.',
                'composition' => '100% Virgin Wool',
                'fit' => 'Regular Fit',
                'price' => 156000,
                'sizes' => 'tailoring',
                'colors' => ['Navy', 'Charcoal'],
                'categories' => ['clothing/blazers'],
                'weight' => 1100,
            ],
            [
                'name' => 'Aviatore Suede Bomber',
                'sku_prefix' => 'JKT',
                'subtitle' => 'Lambskin suede',
                'description' => 'A blouson in whisper-soft lambskin suede with a ribbed collar, cuffs and hem. Unlined at the body so it sits close without bulk.',
                'composition' => '100% Lambskin Suede',
                'fit' => 'Regular Fit',
                'price' => 285000,
                'sizes' => 'knitwear',
                'colors' => ['Tobacco', 'Navy', 'Chocolate'],
                'categories' => ['clothing/jackets'],
                'weight' => 1200,
            ],
            [
                'name' => 'Vento Field Jacket',
                'sku_prefix' => 'JKT',
                'description' => 'A four-pocket field jacket in water-repellent cotton twill, with a drawcord waist and a detachable hood.',
                'composition' => '97% Cotton, 3% Elastane',
                'fit' => 'Relaxed Fit',
                'price' => 98000,
                'was' => 138000,
                'sizes' => 'knitwear',
                'colors' => ['Olive', 'Navy', 'Sand'],
                'categories' => ['clothing/jackets'],
                'weight' => 1000,
            ],

            // ---------------------------------------------------------------
            // Coats
            // ---------------------------------------------------------------
            [
                'name' => 'Inverno Double-Faced Coat',
                'sku_prefix' => 'COA',
                'subtitle' => 'Hand-finished edges',
                'description' => 'A double-faced wool and cashmere coat with no lining -- the two cloths are bonded and the edges closed by hand. Warm, light, and quietly expensive.',
                'composition' => '70% Wool, 30% Cashmere',
                'fit' => 'Regular Fit',
                'price' => 342000,
                'sizes' => 'tailoring',
                'colors' => ['Camel', 'Charcoal', 'Navy'],
                'categories' => ['clothing/coats'],
                'new' => true,
                'weight' => 2000,
            ],
            [
                'name' => 'Porto Belted Trench',
                'sku_prefix' => 'COA',
                'description' => 'A trench in tightly woven cotton gabardine with storm flap, epaulettes and a self-belt. Rain-ready and cut long.',
                'composition' => '100% Cotton',
                'fit' => 'Regular Fit',
                'price' => 178000,
                'sizes' => 'knitwear',
                'colors' => ['Sand', 'Navy'],
                'categories' => ['clothing/coats'],
                'weight' => 1600,
            ],

            // ---------------------------------------------------------------
            // Knitwear
            // ---------------------------------------------------------------
            [
                'name' => 'Filo Cashmere Crewneck',
                'sku_prefix' => 'KNT',
                'subtitle' => 'Twelve-gauge',
                'description' => 'A fine-gauge crewneck in pure Mongolian cashmere, knitted to sit under a jacket without adding bulk.',
                'composition' => '100% Cashmere',
                'fit' => 'Slim Fit',
                'price' => 96000,
                'sizes' => 'knitwear',
                'colors' => ['Navy', 'Charcoal', 'Camel', 'Burgundy', 'Cream'],
                'categories' => ['clothing/knitwear', 'clothing/knitwear/crewnecks'],
                'new' => true,
                'weight' => 400,
            ],
            [
                'name' => 'Costa Merino Rollneck',
                'sku_prefix' => 'KNT',
                'description' => 'An extra-fine merino rollneck with a generous collar that holds its shape wash after wash.',
                'composition' => '100% Extra-Fine Merino Wool',
                'fit' => 'Slim Fit',
                'price' => 62000,
                'sizes' => 'knitwear',
                'colors' => ['Black', 'Charcoal', 'Navy', 'Cream'],
                'categories' => ['clothing/knitwear'],
                'weight' => 450,
            ],
            [
                'name' => 'Palazzo Shawl Cardigan',
                'sku_prefix' => 'KNT',
                'description' => 'A heavier shawl-collar cardigan in a wool and cashmere blend, with horn buttons and ribbed side panels.',
                'composition' => '85% Wool, 15% Cashmere',
                'fit' => 'Regular Fit',
                'price' => 118000,
                'sizes' => 'knitwear',
                'colors' => ['Charcoal', 'Camel', 'Navy'],
                'categories' => ['clothing/knitwear', 'clothing/knitwear/cardigans'],
                'weight' => 700,
            ],
            [
                'name' => 'Ombra Zip Cardigan',
                'sku_prefix' => 'KNT',
                'description' => 'A full-zip cardigan in a compact cotton knit, cut to work over a shirt or on its own.',
                'composition' => '100% Cotton',
                'fit' => 'Regular Fit',
                'price' => 54000,
                'sizes' => 'knitwear',
                'colors' => ['Navy', 'Grey', 'Olive'],
                'categories' => ['clothing/knitwear', 'clothing/knitwear/cardigans'],
                'weight' => 550,
            ],

            // ---------------------------------------------------------------
            // Shirts
            // ---------------------------------------------------------------
            [
                'name' => 'Milano Poplin Dress Shirt',
                'sku_prefix' => 'SHT',
                'subtitle' => 'Two-fold 120s cotton',
                'description' => 'A crisp poplin shirt with a semi-cutaway collar and mother-of-pearl buttons. Cut close through the body with a shaped back.',
                'composition' => '100% Cotton',
                'fit' => 'Slim Fit',
                'price' => 34000,
                'sizes' => 'shirt',
                'colors' => ['White', 'Light Grey', 'Navy'],
                'categories' => ['clothing/shirts', 'clothing/shirts/dress-shirts'],
                'new' => true,
                'weight' => 250,
            ],
            [
                'name' => 'Rialto Twill Dress Shirt',
                'sku_prefix' => 'SHT',
                'description' => 'A softly lustrous twill with a spread collar and French cuffs, for evening or the sharper end of the working week.',
                'composition' => '100% Cotton',
                'fit' => 'Slim Fit',
                'price' => 38000,
                'sizes' => 'shirt',
                'colors' => ['White', 'Midnight Blue'],
                'categories' => ['clothing/shirts', 'clothing/shirts/dress-shirts'],
                'weight' => 260,
            ],
            [
                'name' => 'Lido Linen Shirt',
                'sku_prefix' => 'SHT',
                'description' => 'A washed linen shirt with a one-piece collar, cut loose through the body. Creases are the point.',
                'composition' => '100% Linen',
                'fit' => 'Relaxed Fit',
                'price' => 31000,
                'sizes' => 'shirt',
                'colors' => ['White', 'Sand', 'Olive'],
                'categories' => ['clothing/shirts', 'clothing/shirts/casual-shirts'],
                'weight' => 240,
            ],
            [
                'name' => 'Brera Brushed Cotton Overshirt',
                'sku_prefix' => 'SHT',
                'description' => 'A shirt with outerwear intentions: brushed cotton, chest pockets, and enough room to layer over a tee.',
                'composition' => '100% Brushed Cotton',
                'fit' => 'Relaxed Fit',
                'price' => 45000,
                'was' => 62000,
                'sizes' => 'knitwear',
                'colors' => ['Olive', 'Chocolate', 'Charcoal'],
                'categories' => ['clothing/shirts', 'clothing/shirts/casual-shirts'],
                'weight' => 500,
            ],

            // ---------------------------------------------------------------
            // Pants & Jeans
            // ---------------------------------------------------------------
            [
                'name' => 'Ferrata Wool Dress Pants',
                'sku_prefix' => 'PNT',
                'description' => 'Flat-front trousers in a fine wool, with a clean waistband and a gentle taper to the ankle.',
                'composition' => '100% Virgin Wool',
                'fit' => 'Slim Fit',
                'price' => 58000,
                'sizes' => 'pants',
                'colors' => ['Charcoal', 'Navy', 'Grey'],
                'categories' => ['clothing/pants', 'clothing/pants/dress-pants'],
                'weight' => 600,
            ],
            [
                'name' => 'Aria Pleated Wool Pants',
                'sku_prefix' => 'PNT',
                'description' => 'A single forward pleat gives room through the seat and thigh, falling to a wider leg.',
                'composition' => '100% Virgin Wool',
                'fit' => 'Relaxed Fit',
                'price' => 64000,
                'sizes' => 'pants',
                'colors' => ['Grey', 'Navy', 'Chocolate'],
                'categories' => ['clothing/pants', 'clothing/pants/dress-pants'],
                'weight' => 650,
            ],
            [
                'name' => 'Sabbia Cotton Chino',
                'sku_prefix' => 'PNT',
                'description' => 'A garment-dyed chino in a stretch cotton twill that softens with every wash.',
                'composition' => '97% Cotton, 3% Elastane',
                'fit' => 'Slim Fit',
                'price' => 39000,
                'sizes' => 'pants',
                'colors' => ['Sand', 'Olive', 'Navy', 'Chocolate'],
                'categories' => ['clothing/pants', 'clothing/pants/casual-pants'],
                'weight' => 550,
            ],
            [
                'name' => 'Cinque Stretch Denim Jeans',
                'sku_prefix' => 'JNS',
                'description' => 'Five-pocket jeans in an Italian stretch denim, rinsed to a clean indigo with minimal fading.',
                'composition' => '98% Cotton, 2% Elastane',
                'fit' => 'Slim Fit',
                'price' => 47000,
                'sizes' => 'pants',
                'colors' => ['Midnight Blue', 'Black'],
                'categories' => ['clothing/jeans'],
                'new' => true,
                'weight' => 700,
            ],

            // ---------------------------------------------------------------
            // Polos & T-Shirts
            // ---------------------------------------------------------------
            [
                'name' => 'Riva Mercerised Polo',
                'sku_prefix' => 'POL',
                'description' => 'A short-sleeved polo in mercerised cotton with a soft, unlined collar that stays flat.',
                'composition' => '100% Mercerised Cotton',
                'fit' => 'Slim Fit',
                'price' => 32000,
                'sizes' => 'knitwear',
                'colors' => ['Navy', 'White', 'Olive', 'Burgundy'],
                'categories' => ['clothing/polos-t-shirts'],
                'weight' => 300,
            ],
            [
                'name' => 'Base Pima Cotton T-Shirt',
                'sku_prefix' => 'TSH',
                'description' => 'A plain crewneck tee in long-staple Pima cotton, heavy enough to hold its shape.',
                'composition' => '100% Pima Cotton',
                'fit' => 'Regular Fit',
                'price' => 18000,
                'sizes' => 'knitwear',
                'colors' => ['White', 'Black', 'Navy', 'Grey'],
                'categories' => ['clothing/polos-t-shirts'],
                'weight' => 220,
            ],

            // ---------------------------------------------------------------
            // Shoes
            // ---------------------------------------------------------------
            [
                'name' => 'Firenze Calfskin Oxford',
                'sku_prefix' => 'SHO',
                'subtitle' => 'Blake-stitched',
                'description' => 'A closed-lacing oxford on a slim last, blake-stitched for flexibility and finished with a leather sole.',
                'composition' => '100% Calfskin',
                'price' => 148000,
                'sizes' => 'shoes',
                'colors' => ['Black', 'Chocolate'],
                'categories' => ['shoes/dress-casual-shoes'],
                'weight' => 1100,
            ],
            [
                'name' => 'Amalfi Suede Loafer',
                'sku_prefix' => 'SHO',
                'description' => 'An unlined penny loafer in soft suede, built to be worn without socks and to crease where your foot bends.',
                'composition' => '100% Suede',
                'price' => 126000,
                'sizes' => 'shoes',
                'colors' => ['Tobacco', 'Navy', 'Olive'],
                'categories' => ['shoes/loafers'],
                'new' => true,
                'weight' => 900,
            ],
            [
                'name' => 'Corsa Leather Sneaker',
                'sku_prefix' => 'SHO',
                'description' => 'A low-profile sneaker in full-grain calfskin on a cupsole, with tonal stitching and no visible branding.',
                'composition' => '100% Calfskin',
                'price' => 112000,
                'sizes' => 'shoes',
                'colors' => ['White', 'Black', 'Navy'],
                'categories' => ['shoes/sneakers'],
                'new' => true,
                'weight' => 950,
            ],
            [
                'name' => 'Monza Suede Runner',
                'sku_prefix' => 'SHO',
                'description' => 'A running-inspired silhouette in suede and technical mesh, on a lightweight moulded sole.',
                'composition' => '70% Suede, 30% Textile',
                'price' => 98000,
                'was' => 132000,
                'sizes' => 'shoes',
                'colors' => ['Grey', 'Navy', 'Sand'],
                'categories' => ['shoes/sneakers'],
                'weight' => 850,
            ],
            [
                'name' => 'Trento Chelsea Boot',
                'sku_prefix' => 'SHO',
                'description' => 'A clean Chelsea boot in polished calfskin with elasticated side panels and a pull tab at the heel.',
                'composition' => '100% Calfskin',
                'price' => 168000,
                'sizes' => 'shoes',
                'colors' => ['Black', 'Chocolate'],
                'categories' => ['shoes/dress-casual-shoes'],
                'weight' => 1300,
            ],

            // ---------------------------------------------------------------
            // Accessories
            // ---------------------------------------------------------------
            [
                'name' => 'Ponte Leather Briefcase',
                'sku_prefix' => 'BAG',
                'description' => 'A structured briefcase in vegetable-tanned calfskin, with a padded laptop sleeve and solid brass hardware.',
                'composition' => '100% Calfskin',
                'price' => 224000,
                'sizes' => 'one',
                'colors' => ['Chocolate', 'Black'],
                'categories' => ['accessories/bags'],
                'weight' => 1800,
            ],
            [
                'name' => 'Viaggio Weekend Holdall',
                'sku_prefix' => 'BAG',
                'description' => 'A soft holdall in grained leather, sized for a long weekend and for the overhead locker.',
                'composition' => '100% Grained Leather',
                'price' => 258000,
                'sizes' => 'one',
                'colors' => ['Chocolate', 'Navy'],
                'categories' => ['accessories/bags'],
                'weight' => 2200,
            ],
            [
                'name' => 'Doppio Reversible Belt',
                'sku_prefix' => 'BLT',
                'description' => 'One belt, two finishes: a rotating buckle turns saffiano to smooth calfskin.',
                'composition' => '100% Calfskin',
                'price' => 42000,
                'sizes' => 'one',
                'colors' => ['Black', 'Chocolate'],
                'categories' => ['accessories/belts'],
                'weight' => 300,
            ],
            [
                'name' => 'Seta Woven Silk Tie',
                'sku_prefix' => 'TIE',
                'description' => 'A seven-fold tie in heavy woven silk, hand-rolled at the edges and untipped.',
                'composition' => '100% Silk',
                'price' => 22000,
                'sizes' => 'one',
                'colors' => ['Navy', 'Burgundy', 'Charcoal', 'Olive'],
                'categories' => ['accessories/ties'],
                'weight' => 90,
            ],
            [
                'name' => 'Sera Grosgrain Bow Tie',
                'sku_prefix' => 'BOW',
                'description' => 'A self-tie bow in grosgrain silk, adjustable at the neck.',
                'composition' => '100% Silk',
                'price' => 16000,
                'sizes' => 'one',
                'colors' => ['Black', 'Midnight Blue'],
                'categories' => ['accessories/bow-ties'],
                'weight' => 60,
            ],
            [
                'name' => 'Quadro Printed Pocket Square',
                'sku_prefix' => 'PSQ',
                'description' => 'A hand-rolled silk pocket square with a printed border, sized to fold without bulking the chest pocket.',
                'composition' => '100% Silk',
                'price' => 12000,
                'sizes' => 'one',
                'colors' => ['White', 'Burgundy', 'Navy'],
                'categories' => ['accessories/pocket-squares'],
                'weight' => 40,
            ],
            [
                'name' => 'Alpi Cashmere Scarf',
                'sku_prefix' => 'SCF',
                'description' => 'A generously sized scarf in brushed cashmere, with hand-knotted fringing.',
                'composition' => '100% Cashmere',
                'price' => 68000,
                'sizes' => 'one',
                'colors' => ['Charcoal', 'Camel', 'Navy', 'Burgundy'],
                'categories' => ['accessories/scarves'],
                'weight' => 250,
            ],
            [
                'name' => 'Guanto Cashmere-Lined Gloves',
                'sku_prefix' => 'GLV',
                'description' => 'Nappa leather gloves lined in cashmere, with a hand-stitched three-point back.',
                'composition' => '100% Nappa Leather',
                'price' => 44000,
                'sizes' => 'knitwear',
                'colors' => ['Black', 'Chocolate'],
                'categories' => ['accessories/gloves'],
                'weight' => 150,
            ],
            [
                'name' => 'Vetta Wool Beanie',
                'sku_prefix' => 'HAT',
                'description' => 'A ribbed beanie in a wool and cashmere blend, with a turned-back cuff.',
                'composition' => '90% Wool, 10% Cashmere',
                'price' => 19000,
                'sizes' => 'one',
                'colors' => ['Charcoal', 'Navy', 'Camel'],
                'categories' => ['accessories/hats'],
                'weight' => 120,
            ],
            [
                'name' => 'Panama Straw Hat',
                'sku_prefix' => 'HAT',
                'description' => 'A hand-woven straw hat with a grosgrain band, packable and shaped by hand.',
                'composition' => '100% Toquilla Straw',
                'price' => 36000,
                'sizes' => 'knitwear',
                'colors' => ['Cream', 'Sand'],
                'categories' => ['accessories/hats'],
                'weight' => 180,
            ],
        ];
    }
}
