<?php

$host = '192.168.88.7';
$port = '3307';
$username = 'myadmin';
$password = 'myadmin';

$backupDir = 'D:/backups/';

// ตรวจสอบและสร้างโฟลเดอร์สำรองข้อมูลหากไม่พบ
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$databases = [
    'sac_data',
    'sac_data2',
    'sac_emp',
    'sac_emp_dbs',
    'sac_lotto',
    'sac_cust'
];

// ตรวจสอบ path ของ mysqldump
$mysqldumpPath = "D:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump";

// รหัสผ่านสำหรับไฟล์ zip
$zipPassword = 'syydata';

// ฟังก์ชันตรวจสอบว่าฐานข้อมูลมีอยู่หรือไม่
function databaseExists($host, $port, $username, $password, $database): bool {
    try {
        $dsn = "mysql:host=$host;port=$port;";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// สำรองข้อมูลแต่ละฐานข้อมูล
foreach ($databases as $database) {

    if (!databaseExists($host, $port, $username, $password, $database)) {
        echo "ไม่พบฐานข้อมูล $database, ข้ามการสำรองข้อมูล\n";
        continue;
    }

    // สร้างชื่อไฟล์สำรองข้อมูล
    $backupFile = $backupDir . $database . '_backup_' . date('Y-m-d_H-i-s') . '.sql';

    // สร้างคำสั่ง mysqldump
    $command = "\"$mysqldumpPath\" --host=$host --port=$port --user=$username --password=$password $database > \"$backupFile\"";

    // รันคำสั่งสำรองข้อมูล
    exec($command, $output, $result);

    if ($result === 0 && file_exists($backupFile)) {
        echo "สำรองข้อมูลสำเร็จ: $backupFile\n";

        // สร้างไฟล์ zip และเข้ารหัส
        $zipFile = $backupDir . $database . '_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->setPassword($zipPassword);
            $zip->addFile($backupFile, basename($backupFile));

            // เข้ารหัสไฟล์ใน zip ด้วย AES-256
            if ($zip->setEncryptionName(basename($backupFile), ZipArchive::EM_AES_256)) {
                echo "บีบอัดไฟล์สำเร็จ: $zipFile\n";
            } else {
                echo "ไม่สามารถเข้ารหัสไฟล์ใน zip ได้: $zipFile\n";
            }

            $zip->close();

            // ลบไฟล์ .sql หลังจากบีบอัดสำเร็จ
            unlink($backupFile);
        } else {
            echo "ไม่สามารถสร้างไฟล์ zip ได้: $zipFile\n";
        }
    } else {
        echo "เกิดข้อผิดพลาดในการสำรองข้อมูลสำหรับฐานข้อมูล $database\n";
        print_r($output);
    }
}