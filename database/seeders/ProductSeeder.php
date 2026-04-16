<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Cms\Category;
use App\Models\Cms\CmsProduct as Product;
use App\Models\Cms\ProductImage;
use App\Models\Cms\ProductSpec;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate tables to avoid duplicates
        Schema::disableForeignKeyConstraints();
        ProductSpec::truncate();
        ProductImage::truncate();
        Product::truncate();
        Category::truncate();
        Schema::enableForeignKeyConstraints();

        // Create Categories
        $categories = [
            'MRI' => 'Magnetic Resonance Imaging',
            'CT' => 'Computed Tomography',
            'Ultrasound' => 'Ultrasound Systems',
            'X-Ray' => 'X-Ray Systems',
            'Patient Monitoring' => 'Patient Monitoring Solutions',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $desc) {
            $categoryModels[$name] = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $desc,
                'is_active' => true,
            ]);
        }

        // Create Products (Data from constants.ts)
        $products = [
            [
                'name' => 'SIGNA™ Premier MRI',
                'category' => 'MRI',
                'image' => 'https://picsum.photos/seed/mri/600/400',
                'description' => 'The SIGNA™ Premier is a wide bore 3.0T MRI system designed to deliver high-performance imaging with exceptional patient comfort.',
                'features' => ['HyperBand', 'AIR™ Recon DL', 'SuperG Gradient', '70cm Wide Bore']
            ],
            [
                'name' => 'Revolution™ Apex CT',
                'category' => 'CT',
                'image' => 'https://picsum.photos/seed/ct/600/400',
                'description' => 'Revolution™ Apex platform provides uncompromised image quality and clinical capabilities for the most challenging cases.',
                'features' => ['TrueFidelity™ Image', 'Quantix™ 160 Tube', '0.28s Rotation', '160mm Detector']
            ],
            [
                'name' => 'LOGIQ™ E10 Ultrasound',
                'category' => 'Ultrasound',
                'image' => 'https://picsum.photos/seed/us/600/400',
                'description' => 'The LOGIQ™ E10 Series is our leadership ultrasound system for general imaging, delivering confident diagnosis and tools.',
                'features' => ['cSound™ Architecture', 'XDclear™ Probes', 'AI Tools', 'Volume Navigation']
            ],
            [
                'name' => 'Definium™ 656 HD X-Ray',
                'category' => 'X-Ray',
                'image' => 'https://picsum.photos/seed/xray/600/400',
                'description' => 'The Definium™ 656 HD is a versatile, digital radiographic system powered by Helix™ 2.0 advanced image processing.',
                'features' => ['Helix™ 2.0 Processing', 'Auto Image Paste', 'Volume Rad', 'Motorized Assist']
            ],
            [
                'name' => 'CARESCAPE™ B650 Monitor',
                'category' => 'Patient Monitoring',
                'image' => 'https://picsum.photos/seed/monitor/600/400',
                'description' => 'CARESCAPE™ B650 monitor helps you manage patient flow by providing the right clinical information, when and where you need it.',
                'features' => ['Clinical Decision Support', 'Mobility', 'Connectivity', 'Touchscreen']
            ]
        ];

        foreach ($products as $pData) {
            $product = Product::create([
                'name' => $pData['name'],
                'slug' => Str::slug($pData['name']),
                'sku' => strtoupper(Str::random(8)),
                'category_id' => $categoryModels[$pData['category']]->id,
                'short_description' => Str::limit($pData['description'], 150),
                'description' => $pData['description'],
                'is_active' => true,
                'is_featured' => true,
            ]);

            // Add Image
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $pData['image'],
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            // Add Specs/Features
            foreach ($pData['features'] as $feature) {
                ProductSpec::create([
                    'product_id' => $product->id,
                    'spec_key' => 'Feature',
                    'spec_value' => $feature,
                ]);
            }
        }
    }
}
