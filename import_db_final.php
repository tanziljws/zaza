<?php

$host = 'shortline.proxy.rlwy.net';
$port = 26377;
$username = 'root';
$password = 'dCZeRwjqQkaBCLngKYHrEJWhxRIRkEzm';
$database = 'railway';
$sqlFile = '/Users/tanziljws/Downloads/db_ujikom.sql';

echo "🚀 Starting database import...\n";
echo "Host: {$host}:{$port}\n";
echo "Database: {$database}\n\n";

try {
    // Connect to MySQL
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    echo "Connecting to MySQL server...\n";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 120,
    ]);
    
    echo "✅ Connected successfully!\n";
    
    // Create database if not exists
    echo "Creating/selecting database '{$database}'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$database}`");
    echo "✅ Database ready.\n\n";
    
    // Read SQL file
    echo "Reading SQL file...\n";
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "✅ SQL file loaded (" . number_format(strlen($sql)) . " bytes)\n\n";
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Remove problematic SET statements
    $sql = preg_replace('/SET\s+SQL_MODE\s*=.*?;/i', '', $sql);
    $sql = preg_replace('/START\s+TRANSACTION;/i', '', $sql);
    $sql = preg_replace('/COMMIT;/i', '', $sql);
    $sql = preg_replace('/SET\s+time_zone\s*=.*?;/i', '', $sql);
    $sql = preg_replace('/SET\s+CHARACTER_SET.*?;/i', '', $sql);
    $sql = preg_replace('/SET\s+NAMES.*?;/i', '', $sql);
    
    // Split by semicolon but handle strings properly
    $statements = [];
    $current = '';
    $inString = false;
    $quoteChar = '';
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        $prev = $i > 0 ? $sql[$i-1] : '';
        
        // Handle string literals
        if (!$inString && ($char == '"' || $char == "'" || $char == '`')) {
            $inString = true;
            $quoteChar = $char;
        } elseif ($inString && $char == $quoteChar && $prev != '\\') {
            $inString = false;
            $quoteChar = '';
        }
        
        $current .= $char;
        
        // End of statement
        if ($char == ';' && !$inString) {
            $stmt = trim($current);
            if (!empty($stmt) && strlen($stmt) > 5) {
                // Skip empty or comment-only statements
                if (!preg_match('/^(SET|START|COMMIT|\/\*|\*\/|--)/i', $stmt)) {
                    $statements[] = $stmt;
                }
            }
            $current = '';
        }
    }
    
    echo "Found " . count($statements) . " SQL statements to execute.\n";
    echo "Executing...\n\n";
    
    $executed = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
            $executed++;
            if ($executed % 50 == 0) {
                echo "Progress: {$executed} statements executed...\n";
            }
        } catch (PDOException $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            
            // Skip errors for existing objects
            if ($code == '42S01' || // Table already exists
                $code == '42000' && (strpos($msg, 'already exists') !== false || 
                                     strpos($msg, 'Duplicate') !== false)) {
                $skipped++;
            } else {
                $errors++;
                // Only show first few errors to avoid spam
                if ($errors <= 5) {
                    echo "⚠️  Error: " . substr($msg, 0, 80) . "\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📊 Import Summary:\n";
    echo "   ✅ Executed: {$executed} statements\n";
    echo "   ⏭️  Skipped (duplicates): {$skipped} statements\n";
    if ($errors > 0) {
        echo "   ❌ Errors: {$errors} statements\n";
    }
    echo str_repeat("=", 60) . "\n";
    
    if ($executed > 0) {
        echo "\n✅ Database import completed successfully!\n";
        echo "   Total: " . ($executed + $skipped) . " statements processed\n";
    } else {
        echo "\n⚠️  No new statements executed (database may already be populated)\n";
    }
    
} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

