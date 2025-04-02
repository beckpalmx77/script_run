<?php
header("Content-Type: application/json; charset=UTF-8");

// ตั้งค่าเชื่อมต่อ PostgreSQL
$host = "sac.cckwqocv7kfy.ap-southeast-1.rds.amazonaws.com";  // หรือ IP ของเซิร์ฟเวอร์
$dbname = "sac";  // ชื่อฐานข้อมูล
$dbuser = "sac";  // ชื่อผู้ใช้
$dbpass = "l;ovvF9h8kiN";  // รหัสผ่าน
$port = "5432";  // พอร์ตของ PostgreSQL

try {
    // สร้าง Connection PDO
    $conn_pg = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $dbuser, $dbpass);
    $conn_pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คำสั่ง SQL ดึงข้อมูลลูกค้าจากตาราง `customers`
    $sql = "SELECT sac_orders.*,sac_customers.code,sac_customers.name,sac_customers.owner,
            sac_users.username,sac_users.name as take_name    
    FROM sac_orders
    LEFT JOIN sac_customers ON sac_customers.id = sac_orders.customer_id  		
    LEFT JOIN sac_users ON sac_users.id = sac_customers.taker_id    
    WHERE date >= :current_date
    ORDER BY id";

    // เตรียมและ execute คำสั่ง SQL
    $stmt = $conn_pg->prepare($sql);
    $stmt->execute();

    // ดึงข้อมูลทั้งหมดเป็น associative array
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // แสดงผลข้อมูลเป็น JSON
    echo json_encode(["status" => "success", "data" => $customers], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // กรณีเกิดข้อผิดพลาด
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

