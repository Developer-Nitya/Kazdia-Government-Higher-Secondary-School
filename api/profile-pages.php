<?php
declare(strict_types=1);
// START: Profile page API section
header('Content-Type: application/json; charset=utf-8');
$data=json_decode(file_get_contents(__DIR__.'/../storage/profile-pages.json'), true) ?: [];
$slug=$_GET['slug'] ?? '';
echo json_encode(['success'=>isset($data[$slug]), 'data'=>$data[$slug] ?? null], JSON_UNESCAPED_UNICODE);
// END: Profile page API section
