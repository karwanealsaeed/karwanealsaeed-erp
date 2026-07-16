<?php

namespace Database\Seeders;

use App\Models\AiKnowledgeBase;
use Illuminate\Database\Seeder;

class AiKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'title' => 'Agent Portal Balance',
                'category' => 'Agent Ledger',
                'keywords' => 'balance, ledger, account statement, mera balance, agent balance, بقایا, لیجر',
                'answer_english' => 'Registered agents can view their own balance and ledger from Agent Portal → Ledger / Balance.',
                'answer_urdu' => 'رجسٹرڈ ایجنٹ اپنا بیلنس اور لیجر ایجنٹ پورٹل کے لیجر / بیلنس سیکشن سے دیکھ سکتے ہیں۔',
                'answer_roman_urdu' => 'Registered agent apna balance aur ledger Agent Portal → Ledger / Balance se check kar sakta hai.',
                'priority' => 8,
                'status' => 'Active',
            ],
            [
                'title' => 'Payment Proof Approval',
                'category' => 'Payment',
                'keywords' => 'payment proof, payment upload, bank receipt, payment pending, approval, receipt upload',
                'answer_english' => 'Upload payment proof from Agent Portal. It will remain pending until admin review and will appear in the ledger after approval.',
                'answer_urdu' => 'ایجنٹ پورٹل سے ادائیگی کا ثبوت اپلوڈ کریں۔ ایڈمن کی منظوری تک ادائیگی زیرِ التوا رہے گی اور منظوری کے بعد لیجر میں ظاہر ہوگی۔',
                'answer_roman_urdu' => 'Agent Portal se payment proof upload karein. Admin approval tak Pending rahega aur approve hone ke baad ledger me show hoga.',
                'priority' => 8,
                'status' => 'Active',
            ],
            [
                'title' => 'Umrah Package Inquiry',
                'category' => 'Umrah',
                'keywords' => 'umrah package, umrah price, makkah hotel, madina hotel, sharing, عمرہ, پیکج',
                'answer_english' => 'Please share travel month, number of passengers, departure city and required sharing type for an Umrah package quotation.',
                'answer_urdu' => 'عمرہ پیکج کی قیمت کے لیے سفر کا مہینہ، مسافروں کی تعداد، روانگی کا شہر اور مطلوبہ شیئرنگ ٹائپ بتائیں۔',
                'answer_roman_urdu' => 'Umrah package quotation ke liye travel month, passengers, departure city aur required sharing type share karein.',
                'priority' => 7,
                'status' => 'Active',
            ],
            [
                'title' => 'Air Ticket Inquiry',
                'category' => 'Air Ticketing',
                'keywords' => 'ticket, flight, airfare, airline, departure, return ticket, ٹکٹ, فلائٹ',
                'answer_english' => 'Please share departure city, destination, travel dates, passengers and baggage requirement for a flight quotation.',
                'answer_urdu' => 'فلائٹ کوٹیشن کے لیے روانگی کا شہر، منزل، سفر کی تاریخ، مسافروں کی تعداد اور بیگج کی تفصیل بتائیں۔',
                'answer_roman_urdu' => 'Flight quotation ke liye departure city, destination, travel dates, passengers aur baggage requirement share karein.',
                'priority' => 6,
                'status' => 'Active',
            ],
        ];

        foreach ($records as $record) {
            AiKnowledgeBase::query()->updateOrCreate(
                [
                    'title' => $record['title'],
                ],
                $record
            );
        }
    }
}