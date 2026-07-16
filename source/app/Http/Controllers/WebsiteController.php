<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function home(): View
    {
        return view('website.home', [
            'company' => CompanySetting::current(),
            'title' => 'Karwan E Al Saeed Travel & Tours',
            'description' => 'Your trusted travel partner for Umrah, Visas, Air Tickets, Hotels and Pakistan Tours.',
        ]);
    }

    public function page(string $slug): View
    {
        $pages = $this->pages();

        abort_unless(array_key_exists($slug, $pages), 404);

        return view('website.page', [
            'company' => CompanySetting::current(),
            'page' => $pages[$slug],
            'title' => $pages[$slug]['title'] . ' | Karwan E Al Saeed',
            'description' => $pages[$slug]['description'],
        ]);
    }

    private function pages(): array
    {
        return [
            'about-us' => [
                'title' => 'About Us',
                'heading' => 'About Karwan E Al Saeed',
                'description' => 'Learn about Karwan E Al Saeed Travel & Tours.',
                'content' => 'Karwan E Al Saeed Travel & Tours is your trusted travel partner for Umrah, air ticketing, visit visas, hotels and tours. We focus on reliable service, clear guidance and professional support for every customer.',
                'urdu' => 'کاروان السعید ٹریول اینڈ ٹورز عمرہ، ویزہ، ائیر ٹکٹ، ہوٹل بکنگ اور ٹورز کے لیے قابل اعتماد سفری خدمات فراہم کرتا ہے۔',
            ],

            'our-services' => [
                'title' => 'Our Services',
                'heading' => 'Our Travel Services',
                'description' => 'Umrah, visas, tickets, hotels, insurance and Pakistan tours.',
                'content' => 'We provide complete travel services including Umrah packages, air ticketing, hotel booking, visit visas, visa consultancy, travel insurance and Pakistan tours.',
                'urdu' => 'ہم عمرہ پیکجز، ائیر ٹکٹنگ، ہوٹل بکنگ، وزٹ ویزہ، ویزہ کنسلٹنسی، ٹریول انشورنس اور پاکستان ٹورز کی سہولت فراہم کرتے ہیں۔',
            ],

            'umrah-packages' => [
                'title' => 'Umrah Packages',
                'heading' => 'Umrah Packages',
                'description' => 'Affordable and premium Umrah packages.',
                'content' => 'We offer simple, reliable and premium Umrah packages with proper guidance, hotel options and travel support.',
                'urdu' => 'ہم آسان، قابل اعتماد اور پریمیم عمرہ پیکجز فراہم کرتے ہیں، جن میں رہنمائی، ہوٹل اور سفری سپورٹ شامل ہے۔',
            ],

            'air-ticketing' => [
                'title' => 'Air Ticketing',
                'heading' => 'Air Ticketing',
                'description' => 'Domestic and international air ticket booking.',
                'content' => 'Book domestic and international air tickets with professional guidance and suitable fare options.',
                'urdu' => 'ملکی اور بین الاقوامی ائیر ٹکٹنگ کے لیے مناسب کرایہ اور بہترین رہنمائی حاصل کریں۔',
            ],

            'hotel-booking' => [
                'title' => 'Hotel Booking',
                'heading' => 'Hotel Booking',
                'description' => 'Hotel booking services for Umrah and tours.',
                'content' => 'We help customers book hotels for Umrah, business travel, family travel and tours.',
                'urdu' => 'عمرہ، بزنس، فیملی ٹریول اور ٹورز کے لیے ہوٹل بکنگ کی سہولت دستیاب ہے۔',
            ],

            'visit-visas' => [
                'title' => 'Visit Visas',
                'heading' => 'Visit Visa Services',
                'description' => 'Visit visa guidance and processing support.',
                'content' => 'We provide visit visa guidance and application support for different countries according to customer requirements.',
                'urdu' => 'ہم مختلف ممالک کے وزٹ ویزہ کے لیے رہنمائی اور درخواست کی سپورٹ فراہم کرتے ہیں۔',
            ],

            'visa-consultancy' => [
                'title' => 'Visa Consultancy',
                'heading' => 'Visa Consultancy',
                'description' => 'Professional visa consultancy and documentation guidance.',
                'content' => 'Our team guides customers about visa requirements, documents, application process and tracking.',
                'urdu' => 'ہماری ٹیم ویزہ ضروریات، ڈاکومنٹس، درخواست کے عمل اور ٹریکنگ میں رہنمائی فراہم کرتی ہے۔',
            ],

            'travel-insurance' => [
                'title' => 'Travel Insurance',
                'heading' => 'Travel Insurance',
                'description' => 'Travel insurance information and support.',
                'content' => 'We guide customers about travel insurance options according to travel needs and destination requirements.',
                'urdu' => 'ہم سفر کی ضرورت اور منزل کے مطابق ٹریول انشورنس کے بارے میں رہنمائی فراہم کرتے ہیں۔',
            ],

            'pakistan-tours' => [
                'title' => 'Pakistan Tours',
                'heading' => 'Pakistan Tours',
                'description' => 'Explore beautiful destinations in Pakistan.',
                'content' => 'We arrange Pakistan tours for families, groups and corporate customers with reliable planning.',
                'urdu' => 'ہم فیملی، گروپس اور کارپوریٹ کسٹمرز کے لیے پاکستان ٹورز کی منصوبہ بندی کرتے ہیں۔',
            ],

            'gallery' => [
                'title' => 'Gallery',
                'heading' => 'Gallery',
                'description' => 'View our travel and tour gallery.',
                'content' => 'Our gallery will show travel memories, Umrah groups, tours and company updates.',
                'urdu' => 'گیلری میں عمرہ گروپس، ٹورز اور کمپنی اپڈیٹس شامل ہوں گی۔',
            ],

            'testimonials' => [
                'title' => 'Testimonials',
                'heading' => 'Customer Testimonials',
                'description' => 'Customer reviews and feedback.',
                'content' => 'Customer reviews and feedback will be displayed here.',
                'urdu' => 'کسٹمر ریویوز اور فیڈبیک یہاں دکھائے جائیں گے۔',
            ],

            'faq' => [
                'title' => 'FAQ',
                'heading' => 'Frequently Asked Questions',
                'description' => 'Common questions about our travel services.',
                'content' => 'Find answers to common questions about Umrah, visas, tickets, hotels and tours.',
                'urdu' => 'عمرہ، ویزہ، ٹکٹ، ہوٹل اور ٹورز کے عام سوالات کے جواب یہاں دستیاب ہوں گے۔',
            ],

            'blog-news' => [
                'title' => 'Blog & News',
                'heading' => 'Blog & News',
                'description' => 'Latest travel news and updates.',
                'content' => 'Latest travel updates, visa news, Umrah information and company announcements will be published here.',
                'urdu' => 'تازہ سفری اپڈیٹس، ویزہ نیوز، عمرہ معلومات اور کمپنی اعلانات یہاں شائع ہوں گے۔',
            ],

            'contact' => [
                'title' => 'Contact Us',
                'heading' => 'Contact Karwan E Al Saeed',
                'description' => 'Contact Karwan E Al Saeed Travel & Tours.',
                'content' => 'Contact us for Umrah packages, tickets, visas, hotels and tours.',
                'urdu' => 'عمرہ پیکجز، ٹکٹ، ویزہ، ہوٹل اور ٹورز کے لیے ہم سے رابطہ کریں۔',
            ],
        ];
    }
}