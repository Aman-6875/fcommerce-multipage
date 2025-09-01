<?php
/**
 * Laravel Excel Verification Script
 * Run this on your server to check if Laravel Excel is properly installed
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Laravel Excel Installation Check\n";
echo "=====================================\n\n";

// Check 1: Autoloader
echo "1. Checking autoloader...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "   ✅ Autoloader found\n";
} else {
    echo "   ❌ Autoloader missing - run: composer install\n";
    exit(1);
}

// Check 2: Laravel Excel package files
echo "\n2. Checking Laravel Excel files...\n";
if (is_dir(__DIR__ . '/vendor/maatwebsite/excel')) {
    echo "   ✅ Laravel Excel package directory exists\n";
} else {
    echo "   ❌ Laravel Excel package missing - run: composer require maatwebsite/excel\n";
    exit(1);
}

// Check 3: Laravel Excel classes
echo "\n3. Checking Laravel Excel classes...\n";
$classes = [
    'Maatwebsite\\Excel\\Facades\\Excel',
    'Maatwebsite\\Excel\\Concerns\\FromCollection',
    'Maatwebsite\\Excel\\Concerns\\WithHeadings',
    'Maatwebsite\\Excel\\Concerns\\WithMapping',
    'App\\Exports\\OrdersExport'
];

foreach ($classes as $class) {
    if (class_exists($class) || interface_exists($class)) {
        echo "   ✅ {$class}\n";
    } else {
        echo "   ❌ {$class} - missing\n";
    }
}

// Check 4: PhpSpreadsheet dependency
echo "\n4. Checking PhpSpreadsheet...\n";
if (class_exists('PhpOffice\\PhpSpreadsheet\\Reader\\Csv')) {
    echo "   ✅ PhpSpreadsheet CSV Reader available\n";
} else {
    echo "   ❌ PhpSpreadsheet missing\n";
}

// Check 5: PHP extensions
echo "\n5. Checking required PHP extensions...\n";
$extensions = ['zip', 'xml', 'gd', 'dom', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ {$ext}\n";
    } else {
        echo "   ❌ {$ext} - missing\n";
    }
}

echo "\n🎉 Verification complete!\n";
echo "If all items show ✅, Laravel Excel should work properly.\n";