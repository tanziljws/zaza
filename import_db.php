<?php

$host = 'shortline.proxy.rlwy.net';
$port = 26377;
$username = 'root';
$password = 'dCZeRwjqQkaBCLngKYHrEJWhxRIRkEzm';
$database = 'railway';
$sqlFile = '/Users/tanziljws/Downloads/db_ujikom.sql';

echo "Starting database import...\n";
echo "Host: {$host}:{$port}\n";
echo "Database: {$database}\n";
echo "SQL File: {$sqlFile}\n\n";

try {
    // Connect to MySQL with longer timeout
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    echo "Connecting to MySQL server...\n";
    
    // Try connecting with retry
    $maxRetries = 3;
    $pdo = null;
    for ($i = 1; $i <= $maxRetries; $i++) {
        try {
            echo "Attempt {$i}/{$maxRetries}...\n";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 120,
            ]);
            break;
        } catch (PDOException $e) {
            if ($i < $maxRetries) {
                echo "Connection failed, retrying in 2 seconds...\n";
                sleep(2);
            } else {
                throw $e;
            }
        }
    }
    
    echo "Connected successfully!\n";
    
    // Create database if not exists
    echo "Creating/selecting database '{$database}'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$database}`");
    echo "Database ready.\n\n";
    
    // Read SQL file
    echo "Reading SQL file...\n";
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    $fileSize = filesize($sqlFile);
    echo "SQL file size: " . number_format($fileSize) . " bytes\n";
    
    // Remove comments and clean up
    echo "Processing SQL statements...\n";
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Remove SET statements that might cause issues
    $sql = preg_replace('/SET\s+SQL_MODE\s*=.*?;/i', '', $sql);
    $sql = preg_replace('/START\s+TRANSACTION;/i', '', $sql);
    $sql = preg_replace('/COMMIT;/i', '', $sql);
    $sql = preg_replace('/SET\s+time_zone\s*=.*?;/i', '', $sql);
    $sql = preg_replace('/SET\s+CHARACTER_SET.*?;/i', '', $sql);
    
    // Better SQL statement splitting - handle multi-line statements properly
    $statements = [];
    $currentStatement = '';
    $inString = false;
    $stringChar = '';
    
    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check for string start/end
        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];
            $prevChar = $i > 0 ? $line[$i-1] : '';
            
            if (!$inString && ($char == '"' || $char == "'" || $char == '`')) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char == $stringChar && $prevChar != '\\') {
                $inString = false;
                $stringChar = '';
            }
            
            $currentStatement .= $char;
            
            // If we hit a semicolon and we're not in a string, end the statement
            if ($char == ';' && !$inString) {
                $stmt = trim($currentStatement);
                if (!empty($stmt) && strlen($stmt) > 10 && 
                    !preg_match('/^(SET|START|COMMIT|\/\*|\*\/)/i', $stmt)) {
                    $statements[] = $stmt;
                }
                $currentStatement = '';
            }
        }
        
        // Add newline if we're still building a statement
        if (!empty($currentStatement)) {
            $currentStatement .= "\n";
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    echo "Found " . count($statements) . " SQL statements to execute.\n\n";
    echo "Executing statements...\n";
    
    $executed = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
                $executed++;
                if ($executed % 100 == 0) {
                    echo "Progress: {$executed} statements executed...\n";
                }
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                // Skip errors for existing tables/indexes/constraints
                if (strpos($errorMsg, 'already exists') !== false || 
                    strpos($errorMsg, 'Duplicate') !== false ||
                    strpos($errorMsg, 'Duplicate key') !== false ||
                    strpos($errorMsg, 'already exists') !== false) {
                    $skipped++;
                } else {
                    $errors++;
                    echo "Error on statement " . ($index + 1) . ": " . substr($errorMsg, 0, 100) . "\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Import Summary:\n";
    echo "  Executed: {$executed} statements\n";
    echo "  Skipped (duplicates): {$skipped} statements\n";
    echo "  Errors: {$errors} statements\n";
    echo str_repeat("=", 50) . "\n";
    
    if ($executed > 0) {
        echo "\n✅ Import completed successfully!\n";
    } else {
        echo "\n⚠️  No statements were executed. Check for errors above.\n";
    }
    
} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

