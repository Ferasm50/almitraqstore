<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$subs = \App\Models\SubSection::all();
foreach ($subs as $sub) {
    echo "ID: {$sub->id}, Name: {$sub->name}, Image: {$sub->image}\n";
}
