<?php

return [
    /*
     * WhatsApp par share hone wala secure PDF link
     * kitne hours tak valid rahega.
     */
    'share_expiry_hours' => (int) env(
        'SUPPLIER_STATEMENT_LINK_HOURS',
        24
    ),

    /*
     * Pakistani local mobile number ko international
     * WhatsApp format mein convert karne ke liye.
     */
    'default_whatsapp_country_code' => env(
        'WHATSAPP_DEFAULT_COUNTRY_CODE',
        '92'
    ),
];