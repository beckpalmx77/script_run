<?php
$loop = 0;  // นำตัวแปร loop ไว้ข้างนอก เพื่อเก็บค่าเดิมระหว่าง loop
$str = rand();
$seq_record = md5($str);

function runScript()
{
    global $loop;  // เรียกใช้ตัวแปร loop จากภายนอกฟังก์ชัน
    global $seq_record;

    $conn = null;
    include 'config/connect_db.php';
    date_default_timezone_set('Asia/Bangkok');
    $sourceDir = 'H:/FingerScan/';         // โฟลเดอร์ต้นทาง
    $destinationDir = 'H:/FingerScan/BACKUP/';  // โฟลเดอร์ปลายทาง

    // ใช้ glob() เพื่อค้นหาไฟล์ทั้งหมดที่ลงท้ายด้วย .txt
    $files = glob($sourceDir . '*.txt');
    $start_process = date("Y-m-d H:i:s");

    if (count($files) > 0) {
        foreach ($files as $file) {
            // basename() เพื่อดึงชื่อไฟล์ออกมา
            $fileName = basename($file);

            // เส้นทางของไฟล์ปลายทาง
            $destinationFile = $destinationDir . $fileName;

            // คัดลอกไฟล์จากต้นทางไปปลายทาง
            if (copy($file, $destinationFile)) {
                echo "Copied: " . $fileName . " to " . $destinationDir . "\n\r";
            } else {
                echo "Failed to copy: " . $fileName . "\n\r";
            }
        }
        $loop++;  // เพิ่มค่า loop ต่อเนื่อง
        $end_process = date("Y-m-d H:i:s");
        echo "Loop = " . $loop . " Start Process = " . $start_process . " End Process = " . $end_process . " - " . $seq_record . "\n\r";
        $loop_detail = "Loop = " . $loop . " Start Process = " . $start_process . " End Process = " . $end_process;
        $sql_insert_log = "INSERT INTO sac_backup_finger_log (detail,start_process,end_process,create_by,seq_record) VALUES (?,?,?,?,?)";
        $stmt_insert_log = $conn->prepare($sql_insert_log);
        $stmt_insert_log->execute([$loop_detail, $start_process, $end_process, "System",$seq_record]);
    } else {
        echo "No .txt files found in the source directory." . "\n\r";
    }
}

// รันสคริปต์ทุก 1 ชั่วโมง
while (true) {
    runScript();
    // หน่วงเวลาการทำงาน 1 ชั่วโมง (3600 วินาที)
    sleep(3600);
}