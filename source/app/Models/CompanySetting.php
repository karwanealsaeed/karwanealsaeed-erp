<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'legal_name',
        'short_name',
        'logo',
        'phone',
        'mobile',
        'whatsapp',
        'email',
        'website',
        'address',
        'city',
        'country',
        'ntn_no',
        'license_no',
        'bank_name',
        'bank_account_title',
        'bank_account_no',
        'bank_iban',
        'notes',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Karwan E Al Saeed Travel & Tours',
            'legal_name' => 'Karwan E Al Saeed Travel & Tours',
                'short_name' => 'KES Travel',
                'mobile' => '+92 301 2592342',
                'whatsapp' => '+92 301 2592342',
                'city' => 'Chichawatni',
                'country' => 'Pakistan',
                'address' => 'Office # 90,91 College Road, Chichawatni.',
            ]
        );
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return asset('storage/' . $this->logo);
    }
}