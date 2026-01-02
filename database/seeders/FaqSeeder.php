<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->command->warn('No admin user found. Skipping FAQ seeding.');
            return;
        }

        $faqs = [
            [
                'title' => 'Booking Advance',
                'question' => 'How far in advance should I request a Mass or event?',
                'response' => 'For the best availability, please send your reservation 2–4 weeks before your desired date. Requests made at least 7 days in advance allow smoother review by the adviser, administrator, and priest, but you may still contact Support through your dashboard for urgent intentions.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Edit/Cancel',
                'question' => 'Can I edit or cancel my reservation after submitting it?',
                'response' => 'Yes. You can update or cancel your reservation by going to My Reservations, opening the specific booking, and submitting a change or cancellation request. For events within 7 days, kindly coordinate directly with our staff so we can assist you properly.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Pending Contact',
                'question' => 'Who do I contact if my reservation is still pending?',
                'response' => 'If your reservation is still pending, please message us through the Admin Support Chat in your dashboard. Our CREaM team usually responds within 72 hours and can provide an update, answer questions, and guide you on the next steps.',
                'sort_order' => 3,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, ['admin_id' => $admin->id, 'is_active' => true])
            );
        }

        $this->command->info('FAQs seeded successfully!');
    }
}
