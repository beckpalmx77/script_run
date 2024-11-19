<?php

include 'config/connect_db_my_sac_data2.php';

$sql_select = "SELECT id, DI_DATE FROM ims_data_sale_sac_all WHERE TRD_SEQ IS NULL ORDER BY DI_DATE, id ";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->execute();
$records = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

echo $sql_select . "\n\r";

// กำหนดค่าเริ่มต้นของตัวแปรสำหรับการวนลูป
$sequence = 1;
$currentDate = null;

// เตรียมคำสั่งอัปเดต TRD_SEQ
$sql_update = "UPDATE ims_data_sale_sac_all SET TRD_SEQ = :sequence WHERE id = :id ";
$stmt_update = $conn->prepare($sql_update);

foreach ($records as $record) {
    // ถ้า DI_DATE เปลี่ยนไปจากแถวก่อนหน้า ให้รีเซ็ต sequence กลับไปที่ 1
    if ($currentDate !== $record['DI_DATE']) {
        $sequence = 1;
        $currentDate = $record['DI_DATE'];
    }
    echo $sql_update . $currentDate . " | ". $sequence . "\n\r";
    // ตั้งค่า TRD_SEQ และอัปเดต record
    $stmt_update->bindParam(':sequence', $sequence);
    $stmt_update->bindParam(':id', $record['id']);
    $stmt_update->execute();

    // เพิ่มลำดับค่า TRD_SEQ สำหรับแถวถัดไป
    $sequence++;
}

echo "อัปเดตค่า TRD_SEQ สำเร็จสำหรับข้อมูลทั้งหมดในตาราง";

