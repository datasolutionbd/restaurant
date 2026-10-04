<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Country;
use App\Models\OrderType;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class RestaurantSettingSeeder extends Seeder
{
    /**
     * Branch locations used for every seeded restaurant (Dhaka first, then Delhi).
     *
     * @return array<int, array{name: string, address: string, lat: float, lng: float}>
     */
    private function branchLocations(): array
    {
        return [
            [
                'name' => 'Dhaka',
                'address' => '45, MI Road, C Scheme, Dhaka, Bangladesh 1205, Bangladesh',
                'lat' => 23.810332,
                'lng' => 90.412518,
            ],
            [
                'name' => 'Rajbari',
                'address' => '12, Binodpur, Rajbari Sadar, Bangladesh 7700, Bangladesh',
                'lat' => 23.7572,
                'lng' => 89.6461,
            ],
        ];
    }

    private function seedBranch(Restaurant $restaurant, array $location): Branch
    {
        $branch = new Branch();
        $branch->restaurant_id = $restaurant->id;
        $branch->name = $location['name'];
        $branch->address = $location['address'];
        $branch->lat = $location['lat'];
        $branch->lng = $location['lng'];
        $branch->save();

        $this->call(OnboardingSeeder::class, false, ['branch' => $branch]);
        $branch->generateQrCode();
        $this->addKotPlaces($branch);
        $this->addOrderTypes($branch);
        $branch->generateKotSetting();

        return $branch;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::where('countries_code', 'IN')->first()
            ?? Country::where('countries_code', 'US')->first();

        $count = App::environment('codecanyon') ? 1 : 3;

        $restaurantNames = [
            'Masala Magic',
            'Spice Symphony',
            'Bombay Delight',
            'Curry Leaf',
            'Tandoor Treats',
            'The Royal Biryani',
            'Saffron House',
            'Chaat Corner',
            'Flavors of India',
            'Mughlai Masala',
            'Desi Dhaba',
            'Naan Nirvana',
            'Spice Garden',
            'Punjabi Junction',
            'The Curry Pot',
            'Rasoi Royale',
            'Biryani Bliss',
            'The Tikka Table',
            'Korma Kitchen',
            'Utsav Eatery',
        ];

        $branches = $this->branchLocations();
        $dhaka = $branches[0];

        for ($i = 0; $i < $count; $i++) {
            $this->command->info('Seeding Restaurant: ' . ($i + 1));

            $companyName = $i === 0 ? 'Smart Restaurant' : ($restaurantNames[$i] ?? fake()->company());

            $setting = new Restaurant();
            $setting->name = $companyName;
            $setting->address = $i === 0 ? $dhaka['address'] : fake()->address();
            $setting->phone_number = fake()->e164PhoneNumber();
            $setting->timezone = $country?->countries_code === 'IN' ? 'Asia/Dhaka' : 'America/New_York';
            $setting->theme_hex = '#A78BFA';
            $setting->theme_rgb = '167, 139, 250';
            $setting->email = str()->slug($companyName, '.') . '@example.com';
            $setting->country_id = $country->id;
            $setting->package_id = 1;
            $setting->package_type = 'annual';
            $setting->about_us = Restaurant::ABOUT_US_DEFAULT_TEXT;
            $setting->facebook_link = 'https://www.facebook.com/';
            $setting->instagram_link = 'https://www.instagram.com/';
            $setting->twitter_link = 'https://www.twitter.com/';
            $setting->google_business_link = 'https://business.google.com/';
            $setting->customer_site_language = 'en';
            $setting->save();

            foreach ($branches as $location) {
                $this->seedBranch($setting, $location);
            }
        }
    }

    public function addKotPlaces($branch)
    {
        if (!$branch) {
            $this->command->warn(__('messages.noBranchFound'));

            return;
        }
    }

    public function addOrderTypes($branch)
    {
        $defaultOrderTypes = ['Dine In', 'Delivery', 'Pickup'];
        $defaultOrderTypesSlug = ['dine_in', 'delivery', 'pickup'];

        foreach ($defaultOrderTypes as $index => $type) {
            OrderType::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'slug' => $defaultOrderTypesSlug[$index],
                ],
                [
                    'order_type_name' => $type,
                    'enable_token_number' => true,
                    'show_order_number_on_board' => true,
                ]
            );
        }
    }
}
