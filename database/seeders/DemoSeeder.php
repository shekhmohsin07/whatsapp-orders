<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(OrderService $orders): void
    {
        // The "hashed" cast on User hashes this password.
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo Owner', 'password' => 'password']
        );

        // Safe to run twice: skip if the demo user already has a store.
        if ($user->stores()->exists()) {
            return;
        }

        $store = $user->stores()->create([
            'name' => 'ABC Restaurant',
            'slug' => 'abc-restaurant',
            'description' => 'Fresh burgers, wood-fired pizza and cold drinks, made to order and ready in 20 minutes.',
            'whatsapp_number' => '+15551234567', // replace with a real number to test WhatsApp
            'currency' => '$',
            'status' => 'active',
        ]);

        $products = [
            ['Chicken Burger', 'Crispy chicken fillet, lettuce, tomato and house mayo.', 5.00, 'active'],
            ['Beef Burger', 'Grilled beef patty, cheddar, pickles and burger sauce.', 6.50, 'active'],
            ['Margherita Pizza', 'Tomato, mozzarella and fresh basil. 10 inch.', 8.00, 'active'],
            ['Pepperoni Pizza', 'Mozzarella and spicy pepperoni. 10 inch.', 9.50, 'active'],
            ['French Fries', 'Golden fries with sea salt.', 3.00, 'active'],
            ['Caesar Salad', 'Romaine, parmesan, croutons and Caesar dressing.', 6.00, 'active'],
            ['Coke', 'Chilled 330 ml can.', 2.00, 'active'],
            ['Fresh Lemonade', 'Squeezed to order, lightly sweetened.', 2.50, 'active'],
            ['Chocolate Brownie', 'Warm brownie with a fudge centre.', 3.50, 'active'],
            // Inactive on purpose: proves it stays hidden on the public store.
            ['Seasonal Soup', 'Soup of the day. Currently unavailable.', 4.00, 'inactive'],
        ];

        foreach ($products as [$name, $description, $price, $status]) {
            $store->products()->create([
                'name' => $name,
                'slug' => Product::generateUniqueSlug($store->id, $name),
                'description' => $description,
                'price' => $price,
                'status' => $status,
            ]);
        }

        $ids = $store->products()->pluck('id', 'name');

        $samples = [
            [
                'name' => 'Sara Ahmed', 'phone' => '+15550100001', 'address' => '12 Palm Street, Apt 4',
                'items' => [['Chicken Burger', 2], ['Coke', 2]],
                'status' => Order::STATUS_PENDING, 'at' => now()->subHours(2),
            ],
            [
                'name' => 'Michael Brown', 'phone' => '+15550100002', 'address' => '88 Lake Road',
                'items' => [['Pepperoni Pizza', 1], ['French Fries', 1], ['Fresh Lemonade', 2]],
                'status' => Order::STATUS_PENDING, 'at' => now()->subHours(5),
            ],
            [
                'name' => 'Priya Nair', 'phone' => '+15550100003', 'address' => '5 Garden Avenue',
                'items' => [['Margherita Pizza', 2], ['Caesar Salad', 1]],
                'status' => Order::STATUS_CONFIRMED, 'at' => now()->subDay(),
            ],
            [
                'name' => 'Omar Khalil', 'phone' => '+15550100004', 'address' => '31 Market Square',
                'items' => [['Beef Burger', 3], ['French Fries', 3], ['Chocolate Brownie', 1]],
                'status' => Order::STATUS_COMPLETED, 'at' => now()->subDays(3),
            ],
        ];

        foreach ($samples as $sample) {
            $order = $orders->createOrder($store, [
                'customer_name' => $sample['name'],
                'customer_phone' => $sample['phone'],
                'customer_address' => $sample['address'],
                'items' => array_map(
                    fn ($item) => ['product_id' => $ids[$item[0]], 'quantity' => $item[1]],
                    $sample['items']
                ),
            ]);

            $order->status = $sample['status'];
            $order->created_at = $sample['at'];
            $order->save();
        }
    }
}
