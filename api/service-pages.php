<?php
declare(strict_types=1);

/* START: Service page API bootstrap section */
require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/service-pages.php';
/* END: Service page API bootstrap section */

/* START: Service page JSON response section */
try {
    $slug = trim((string) ($_GET['slug'] ?? ''));

    if (!school_service_slug_is_valid($slug)) {
        school_api_json_response([
            'success' => false,
            'message' => 'সঠিক সার্ভিস পেজ নির্বাচন করা হয়নি।',
        ], 400);
        exit;
    }

    $page = school_find_service_page($slug);

    if ($page === null) {
        school_api_json_response([
            'success' => false,
            'message' => 'সার্ভিস পেজটি পাওয়া যায়নি।',
        ], 404);
        exit;
    }

    school_api_json_response([
        'success' => true,
        'data' => $page,
    ]);
    exit;
} catch (Throwable $exception) {
    school_api_json_response([
        'success' => false,
        'message' => 'সার্ভিস পেজের তথ্য লোড করা যায়নি।',
    ], 500);
    exit;
}
/* END: Service page JSON response section */
