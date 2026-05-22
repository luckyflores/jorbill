<?php

return [
    'driver'   => env('ODOO_DRIVER', 'null'),                                  // null | live
    'base_url' => env('ODOO_URL', 'http://127.0.0.1:8069'),
    'db'       => env('ODOO_DB', 'jorbill_accounting'),
    'login'    => env('ODOO_USER', 'admin'),
    'password' => env('ODOO_PASSWORD', 'admin'),
];
