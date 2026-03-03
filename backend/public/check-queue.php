<?php
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pending = DB::table('jobs')->count();
$failed = DB::table('failed_jobs')->count();

echo json_encode([
    'pending_jobs' => $pending,
    'failed_jobs' => $failed,
    'message' => $pending > 0 ? 'Hay trabajos pendientes' : 'No hay trabajos pendientes'
]);
