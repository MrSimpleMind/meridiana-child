<?php
/**
 * SCSS Compiler - PHP Script
 * Compila SCSS usando scssphp (loader automatico di Composer)
 */

// Ricerca il file di bootstrap di Composer
$autoload_paths = [
    __DIR__ . '/../../../../../../../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',
];

$autoloader = false;
foreach ($autoload_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloader = true;
        break;
    }
}

if (!$autoloader) {
    // Fallback: se Composer non disponibile, usa direttamente Node.js
    echo "Composer autoloader non trovato. Uso Node.js compile-scss.js...\n";
    passthru('cd ' . __DIR__ . ' && node compile-scss.js');
    exit;
}

// Se arriviamo qui, Composer è disponibile
require_once __DIR__ . '/vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

try {
    echo "🔄 Compilazione SCSS in corso...\n";
    
    $scss = new Compiler();
    $scss->setOutputStyle(OutputStyle::COMPRESSED);
    
    $inputFile = __DIR__ . '/assets/css/src/main.scss';
    $outputFile = __DIR__ . '/assets/css/dist/main.css';
    
    if (!file_exists($inputFile)) {
        throw new Exception("File SCSS non trovato: {$inputFile}");
    }
    
    $css = $scss->compileFile($inputFile);
    
    if (!is_dir(dirname($outputFile))) {
        mkdir(dirname($outputFile), 0755, true);
    }
    
    file_put_contents($outputFile, $css);
    
    echo "✅ SCSS compilato con successo!\n";
    echo "📁 Output: {$outputFile}\n";
    echo "📊 Dimensione: " . round(filesize($outputFile) / 1024, 2) . " KB\n";
    
} catch (Exception $e) {
    echo "❌ Errore compilazione: " . $e->getMessage() . "\n";
    exit(1);
}
