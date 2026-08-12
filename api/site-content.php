<?php
declare(strict_types=1);

/* START: Site content API bootstrap section */
require_once __DIR__ . '/../backend/helpers.php';
/* END: Site content API bootstrap section */

/* START: Site content JSON response section */
try {
    school_api_json_response([
        'success' => true,
        'data' => school_read_site_content(),
    ]);
} catch (Throwable $exception) {
    school_api_json_response([
        'success' => false,
        'data' => school_default_site_content(),
        'message' => 'সাইট কনটেন্ট লোড করা যায়নি।',
    ], 500);
}
/* END: Site content JSON response section */
