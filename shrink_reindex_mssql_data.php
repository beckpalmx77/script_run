<?php

// กำหนดการตั้งค่าการเชื่อมต่อฐานข้อมูล MSSQL
$serverName = "localhost"; // เปลี่ยนเป็นชื่อเซิร์ฟเวอร์ของคุณ
$dbName = "SYY";
$username = "SYY";
$password = "39122222";

try {
    // สร้างการเชื่อมต่อกับฐานข้อมูล MSSQL โดยใช้ PDO
    $conn = new PDO("sqlsrv:server=$serverName;Database=$dbName", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ขั้นตอนที่ 1: เปลี่ยนโหมดการ Recovery
    $conn->exec("ALTER DATABASE SYY SET RECOVERY SIMPLE");

    // ขั้นตอนที่ 2: ดึงข้อมูลจาก sys.database_files
    $stmt = $conn->query("SELECT file_id, name FROM sys.database_files");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($files);
    echo "</pre>";

    // ขั้นตอนที่ 3: ทำการบีบอัดไฟล์
    $conn->exec("DBCC SHRINKFILE (2, TRUNCATEONLY)");
    echo "Database shrink completed successfully.\n\r";

    // ขั้นตอนที่ 4: ปรับ Fill Factor ของทุกตาราง
    $fillfactor = 80;

    // สร้าง cursor ด้วยการ query ตารางทั้งหมด
    $stmt = $conn->query("
        SELECT OBJECT_SCHEMA_NAME([object_id]) + '.' + name AS TableName
        FROM sys.tables
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // วน loop เพื่อปรับ Fill Factor ของทุกตาราง
    foreach ($tables as $table) {
        $tableName = $table['TableName'];
        $sql = "ALTER INDEX ALL ON $tableName REBUILD WITH (FILLFACTOR = $fillfactor)";
        $conn->exec($sql);
        echo "Rebuilt indexes for table: $tableName with FILLFACTOR = $fillfactor\n\r";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// ปิดการเชื่อมต่อ
$conn = null;

