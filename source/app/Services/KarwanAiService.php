<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\UmrahPackage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KarwanAiService
{
    public function answer(string $message): array
    {
        $message = trim($message);
        $text = Str::lower($message);
        $language = $this->detectLanguage($message);

        if ($this->isOutsideCompanyScope($text)) {
            return [
                'reply' => $this->localized(
                    $language,
                    'I can only assist with Karwan E Al Saeed Travel & Tours services such as Umrah, visas, air tickets, hotels, insurance, Pakistan tours, payments and bookings.',
                    'میں صرف کاروانِ آل سعید ٹریول اینڈ ٹورز کی سروسز مثلاً عمرہ، ویزا، ائیر ٹکٹ، ہوٹل، انشورنس، پاکستان ٹورز، ادائیگی اور بکنگ کے بارے میں مدد کر سکتا ہوں۔',
                    'Main sirf Karwan E Al Saeed Travel & Tours ki services, jaise Umrah, visas, air tickets, hotels, insurance, Pakistan tours, payments aur bookings ke bare me madad kar sakta hoon.'
                ),
                'language' => $language,
                'category' => 'Outside Scope',
                'needs_human_support' => false,
            ];
        }

        if ($this->containsAny($text, [
            'what services',
            'which services',
            'services do you offer',
            'your services',
            'available services',
            'kya services',
            'kia services',
            'kai kia services',
            'services kya',
            'services kia',
            'services hn',
            'services hain',
            'kon si services',
            'konsi services',
            'kaun si services',
            'کیا سروسز',
            'کون سی سروسز',
            'سروسز کیا',
            'خدمات',
        ])) {
            return $this->result(
                $language,
                'Services',
                false,
                'Karwan E Al Saeed provides Umrah packages, visit visas and visa consultancy, domestic and international air tickets, hotel booking, travel insurance, Pakistan tours, booking support, payments, invoices and registered Agent Portal services.',
                'کاروانِ آل سعید عمرہ پیکجز، وزٹ ویزا اور ویزا کنسلٹنسی، ملکی و بین الاقوامی ائیر ٹکٹ، ہوٹل بکنگ، ٹریول انشورنس، پاکستان ٹورز، بکنگ سپورٹ، ادائیگی، انوائس اور رجسٹرڈ ایجنٹ پورٹل کی خدمات فراہم کرتا ہے۔',
                'Karwan E Al Saeed Umrah packages, visit visas aur visa consultancy, domestic aur international air tickets, hotel booking, travel insurance, Pakistan tours, booking support, payments, invoices aur registered Agent Portal services provide karta hai.'
            );
        }
        if ($this->containsAny($text, [
            'assalam', 'salam', 'hello', 'hi', 'aoa', 'السلام', 'سلام',
        ])) {
            return $this->result(
                $language,
                'Greeting',
                false,
                'Assalam o Alaikum! Welcome to Karwan E Al Saeed Travel & Tours. I can help with Umrah packages, visas, air tickets, hotels, travel insurance, Pakistan tours, bookings and payments.',
                'السلام علیکم! کاروانِ آل سعید ٹریول اینڈ ٹورز میں خوش آمدید۔ میں عمرہ پیکجز، ویزا، ائیر ٹکٹ، ہوٹل، ٹریول انشورنس، پاکستان ٹورز، بکنگ اور ادائیگی کے بارے میں مدد کر سکتا ہوں۔',
                'Assalam o Alaikum! Karwan E Al Saeed Travel & Tours me khush aamdeed. Main Umrah packages, visas, air tickets, hotels, travel insurance, Pakistan tours, bookings aur payments me madad kar sakta hoon.'
            );
        }

        if ($this->containsAny($text, [
            'umrah', 'عمرہ', 'makkah', 'madina', 'package', 'پیکج',
        ])) {
            return [
                'reply' => $this->umrahReply($language),
                'language' => $language,
                'category' => 'Umrah',
                'needs_human_support' => false,
            ];
        }

        if ($this->containsAny($text, [
            'visa', 'ویزہ', 'ویزا', 'visit visa', 'passport requirement',
        ])) {
            return $this->result(
                $language,
                'Visa',
                false,
                'We provide visit visa and visa consultancy services. Requirements depend on the country and visa type. Please share the destination country, applicant nationality, travel date and passport validity for accurate guidance.',
                'ہم وزٹ ویزا اور ویزا کنسلٹنسی فراہم کرتے ہیں۔ ضروریات ملک اور ویزا کی قسم کے مطابق مختلف ہوتی ہیں۔ درست رہنمائی کے لیے ملک، درخواست گزار کی قومیت، سفر کی تاریخ اور پاسپورٹ کی میعاد بتائیں۔',
                'Hum visit visa aur visa consultancy provide karte hain. Requirements country aur visa type ke mutabiq hoti hain. Sahi guidance ke liye destination country, applicant nationality, travel date aur passport validity share karein.'
            );
        }

        if ($this->containsAny($text, [
            'ticket', 'flight', 'airline', 'pnr', 'ٹکٹ', 'فلائٹ',
        ])) {
            return $this->result(
                $language,
                'Air Ticketing',
                false,
                'For an air-ticket inquiry, please provide departure city, destination, travel date, return date, number of adults, children and infants, and preferred airline or baggage requirement.',
                'ائیر ٹکٹ کی معلومات کے لیے روانگی کا شہر، منزل، سفر کی تاریخ، واپسی کی تاریخ، بالغ، بچے، شیر خوار اور پسندیدہ ائیر لائن یا بیگج کی تفصیل فراہم کریں۔',
                'Air ticket inquiry ke liye departure city, destination, travel date, return date, adults, children, infants aur preferred airline ya baggage requirement share karein.'
            );
        }

        if ($this->containsAny($text, [
            'hotel', 'room', 'check in', 'check-in', 'check out', 'check-out', 'ہوٹل',
        ])) {
            return $this->result(
                $language,
                'Hotel',
                false,
                'For hotel booking, please share the city, check-in date, check-out date, number of guests, rooms, room type and preferred hotel category.',
                'ہوٹل بکنگ کے لیے شہر، چیک اِن، چیک آؤٹ، مہمانوں کی تعداد، کمروں کی تعداد، روم ٹائپ اور ہوٹل کی کیٹیگری بتائیں۔',
                'Hotel booking ke liye city, check-in date, check-out date, guests, rooms, room type aur preferred hotel category share karein.'
            );
        }

        if ($this->containsAny($text, [
            'insurance', 'travel insurance', 'انشورنس',
        ])) {
            return $this->result(
                $language,
                'Travel Insurance',
                false,
                'Travel insurance depends on destination, traveler age, trip duration and required coverage. Please share these details for quotation guidance.',
                'ٹریول انشورنس منزل، مسافر کی عمر، سفر کی مدت اور مطلوبہ کوریج پر منحصر ہے۔ کوٹیشن کے لیے یہ تفصیل فراہم کریں۔',
                'Travel insurance destination, traveler age, trip duration aur required coverage par depend karti hai. Quotation guidance ke liye ye details share karein.'
            );
        }

        if ($this->containsAny($text, [
            'pakistan tour', 'tour', 'trip', 'naran', 'kaghan', 'hunza',
            'skardu', 'murree', 'سیر', 'پاکستان ٹور',
        ])) {
            return $this->result(
                $language,
                'Pakistan Tours',
                false,
                'We arrange Pakistan tours including family, group and customized trips. Please share destination, travel dates, number of travelers, departure city and preferred transport or hotel category.',
                'ہم پاکستان کے فیملی، گروپ اور کسٹمائزڈ ٹورز کا انتظام کرتے ہیں۔ منزل، تاریخ، مسافروں کی تعداد، روانگی کا شہر، ٹرانسپورٹ اور ہوٹل کی ترجیح بتائیں۔',
                'Hum Pakistan ke family, group aur customized tours arrange karte hain. Destination, travel dates, travelers, departure city aur preferred transport ya hotel category share karein.'
            );
        }

        if ($this->containsAny($text, [
            'balance', 'ledger', 'account statement', 'بقایا', 'لیجر',
        ])) {
            return $this->result(
                $language,
                'Agent Ledger',
                false,
                'Registered B2B agents can view only their own balance, ledger, purchased services, invoices and vouchers from the Agent Portal.',
                'رجسٹرڈ بی ٹو بی ایجنٹ اپنے ایجنٹ پورٹل میں صرف اپنا بیلنس، لیجر، خریدی گئی سروسز، انوائس اور واؤچر دیکھ سکتے ہیں۔',
                'Registered B2B agents Agent Portal me sirf apna balance, ledger, purchased services, invoices aur vouchers dekh sakte hain.'
            );
        }

        if ($this->containsAny($text, [
            'payment', 'paid', 'receipt', 'bank transfer', 'jazzcash',
            'easypaisa', 'ادائیگی', 'رسید',
        ])) {
            return $this->result(
                $language,
                'Payment',
                false,
                'Agents can upload payment proof from Agent Portal. The payment remains pending until admin review. After approval, it is reflected in the agent ledger.',
                'ایجنٹ پورٹل سے ادائیگی کا ثبوت اپلوڈ کیا جا سکتا ہے۔ ایڈمن کی منظوری تک ادائیگی زیرِ التوا رہے گی، منظوری کے بعد لیجر میں ظاہر ہوگی۔',
                'Agent Portal se payment proof upload kiya ja sakta hai. Admin approval tak payment Pending rahegi aur approve hone ke baad agent ledger me show hogi.'
            );
        }

        if ($this->containsAny($text, [
            'booking status', 'track booking', 'tracking', 'status check',
            'بکنگ اسٹیٹس', 'ٹریک',
        ])) {
            return $this->result(
                $language,
                'Booking Tracking',
                false,
                'Registered agents can check only their own visa, flight, Umrah and hotel booking status from Agent Portal → Booking Tracking.',
                'رجسٹرڈ ایجنٹ ایجنٹ پورٹل کے بکنگ ٹریکنگ سیکشن سے صرف اپنی ویزا، فلائٹ، عمرہ اور ہوٹل بکنگ کا اسٹیٹس دیکھ سکتے ہیں۔',
                'Registered agents Agent Portal → Booking Tracking se sirf apni visa, flight, Umrah aur hotel booking status check kar sakte hain.'
            );
        }

        if ($this->containsAny($text, [
            'refund', 'cancel', 'cancellation', 'واپسی', 'ریفنڈ', 'کینسل',
        ])) {
            return $this->result(
                $language,
                'Refund',
                true,
                'Refund and cancellation depend on the airline, hotel, visa, supplier and booking terms. Please share the booking or invoice reference so our team can review it.',
                'ریفنڈ اور کینسلیشن ائیر لائن، ہوٹل، ویزا، سپلائر اور بکنگ کی شرائط پر منحصر ہوتی ہے۔ براہِ کرم بکنگ یا انوائس ریفرنس فراہم کریں۔',
                'Refund aur cancellation airline, hotel, visa, supplier aur booking terms par depend karti hai. Review ke liye booking ya invoice reference share karein.'
            );
        }

        if ($this->containsAny($text, [
            'office', 'address', 'contact', 'phone', 'whatsapp', 'timing',
            'پتہ', 'رابطہ', 'واٹس ایپ',
        ])) {
            return [
                'reply' => $this->contactReply($language),
                'language' => $language,
                'category' => 'Contact',
                'needs_human_support' => false,
            ];
        }

        return $this->result(
            $language,
            'Human Support',
            true,
            'I could not confidently answer that question. Please send your booking reference and mobile number through the support ticket or WhatsApp so our team can assist you.',
            'میں اس سوال کا درست جواب نہیں دے سکا۔ براہِ کرم سپورٹ ٹکٹ یا واٹس ایپ کے ذریعے بکنگ ریفرنس اور موبائل نمبر ارسال کریں۔',
            'Main is sawal ka confident jawab nahi de saka. Support ticket ya WhatsApp par booking reference aur mobile number bhej dein, hamari team assist karegi.'
        );
    }

    private function umrahReply(string $language): string
    {
        $packageLines = [];

        if (
            Schema::hasTable('umrah_packages')
            && class_exists(UmrahPackage::class)
        ) {
            $packages = UmrahPackage::query()
                ->where('status', 'Available')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(3)
                ->get();

            foreach ($packages as $package) {
                $price = number_format(
                    (float) ($package->sharing_price ?: $package->price),
                    0
                );

                $packageLines[] = trim(
                    $package->title
                    . ' | '
                    . ($package->duration_nights ?: 0)
                    . ' Nights | PKR '
                    . $price
                );
            }
        }

        $packagesText = count($packageLines) > 0
            ? "\n\nAvailable packages:\n• " . implode("\n• ", $packageLines)
            : '';

        return $this->localized(
            $language,
            'We offer Umrah packages with flights, Makkah/Madinah hotels, transport and different sharing options. Please share travel month, number of passengers and required sharing type.'
                . $packagesText,
            'ہم فلائٹ، مکہ و مدینہ ہوٹل، ٹرانسپورٹ اور مختلف شیئرنگ آپشنز کے ساتھ عمرہ پیکجز فراہم کرتے ہیں۔ براہِ کرم سفر کا مہینہ، مسافروں کی تعداد اور شیئرنگ ٹائپ بتائیں۔'
                . $packagesText,
            'Hum flights, Makkah/Madinah hotels, transport aur different sharing options ke sath Umrah packages provide karte hain. Travel month, passengers aur required sharing type share karein.'
                . $packagesText
        );
    }

    private function contactReply(string $language): string
    {
        $company = CompanySetting::current();

        $name = $company->company_name ?: 'Karwan E Al Saeed Travel & Tours';
        $mobile = $company->mobile ?: '+92 301 2592342';
        $whatsapp = $company->whatsapp ?: $mobile;
        $address = $company->address ?: 'Office # 90,91 College Road, Chichawatni.';

        return $this->localized(
            $language,
            "{$name}\nMobile: {$mobile}\nWhatsApp: {$whatsapp}\nAddress: {$address}",
            "{$name}\nموبائل: {$mobile}\nواٹس ایپ: {$whatsapp}\nپتہ: {$address}",
            "{$name}\nMobile: {$mobile}\nWhatsApp: {$whatsapp}\nAddress: {$address}"
        );
    }

    private function result(
        string $language,
        string $category,
        bool $needsHumanSupport,
        string $english,
        string $urdu,
        string $romanUrdu
    ): array {
        return [
            'reply' => $this->localized(
                $language,
                $english,
                $urdu,
                $romanUrdu
            ),
            'language' => $language,
            'category' => $category,
            'needs_human_support' => $needsHumanSupport,
        ];
    }

    private function localized(
        string $language,
        string $english,
        string $urdu,
        string $romanUrdu
    ): string {
        return match ($language) {
            'Urdu' => $urdu,
            'English' => $english,
            default => $romanUrdu,
        };
    }

    private function detectLanguage(string $message): string
    {
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $message)) {
            return 'Urdu';
        }

        $lower = Str::lower($message);

        if ($this->containsAny($lower, [
            'what', 'how', 'please', 'need', 'want', 'where', 'when',
            'price', 'available', 'information',
        ])) {
            return 'English';
        }

        return 'Roman Urdu';
    }

    private function isOutsideCompanyScope(string $text): bool
    {
        return $this->containsAny($text, [
            'politics',
            'election',
            'prime minister',
            'president',
            'medical advice',
            'medicine',
            'doctor',
            'diagnosis',
            'programming',
            'coding',
            'php code',
            'javascript code',
            'religious debate',
            'political debate',
        ]);
    }

    private function containsAny(string $text, array $terms): bool
    {
        foreach ($terms as $term) {
            if (Str::contains($text, Str::lower($term))) {
                return true;
            }
        }

        return false;
    }
}
