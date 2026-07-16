<?php

return [
    /*
     * PDF header mein display hone wali company details.
     */
    'company' => [
        'name' => env(
            'COMPANY_LEGAL_NAME',
            'A.S.B TRAVEL & TOURS (PVT) LTD.'
        ),

        'brand' => env(
            'COMPANY_BRAND_NAME',
            'Karwan E Al Saeed Travel & Tours'
        ),

        'phone' => env(
            'COMPANY_PHONE',
            '+92 301 2592342'
        ),

        'email' => env(
            'COMPANY_EMAIL',
            ''
        ),

        'address' => env(
            'COMPANY_ADDRESS',
            'Office # 90, 91 College Road, Chichawatni.'
        ),
    ],

    /*
     * WhatsApp par bheja gaya PDF link itne hours
     * ke baad automatically expire ho jayega.
     */
    'share_expiry_hours' => (int) env(
        'CUSTOMER_STATEMENT_LINK_HOURS',
        24
    ),

    /*
     * Local Pakistani mobile number 03xx ko
     * international WhatsApp number 923xx mein
     * convert karne ke liye.
     */
    'default_whatsapp_country_code' => env(
        'WHATSAPP_DEFAULT_COUNTRY_CODE',
        '92'
    ),
];