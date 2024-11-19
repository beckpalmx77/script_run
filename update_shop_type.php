<?php

include 'config/connect_db_my_sac_data2.php';

ini_set('memory_limit', '256M'); // เพิ่ม memory limit

$batch_size = 1000; // กำหนดขนาดกลุ่มที่จะประมวลผล
$offset = 0;

do {
    $shop_type = "";
    $shop_type_status = "";
    // ดึงข้อมูลทีละ 1000 แถว จาก ims_data_sale_sac_all
    $sql_sale = "SELECT DISTINCT(AR_CODE) AS AR_CODE FROM ims_data_sale_sac_all LIMIT :offset, :batch_size";
    $query_sale = $conn->prepare($sql_sale);
    $query_sale->bindParam(':offset', $offset, PDO::PARAM_INT);
    $query_sale->bindParam(':batch_size', $batch_size, PDO::PARAM_INT);
    $query_sale->execute();
    $sale_results = $query_sale->fetchAll(PDO::FETCH_OBJ);

    // หากไม่มีข้อมูลเหลือให้ออกจากลูป
    if (empty($sale_results)) {
        break;
    }

    foreach ($sale_results as $sale) {

        $sql_find = "SELECT * FROM ims_ar_shop WHERE ar_code = '" . $sale->AR_CODE . "'" ;
        $query_find = $conn->prepare($sql_find);
        $query_find->execute();

        echo "Select ims_ar_shop =  " . $sql_find . " | " .  $sale->AR_CODE . " | " .$shop_type ."\n\r";
        echo "Row Count = " . $query_find->rowCount() . "\n\r";

        if ($query_find->rowCount() > 0) {
            $shop_type = "SHOP";
            $shop_type_status = "Y";
        } else {
            $shop_type = "ร้านทั่วไป";
            $shop_type_status = "N";
        }

        $sql_update = "UPDATE ims_data_sale_sac_all SET SHOP_TYPE = :shop_type , SHOP_TYPE_STATUS = :shop_type_status WHERE AR_CODE = '" . $sale->AR_CODE . "'";

        echo "Update " . $sql_update . " | " .  $sale->AR_CODE . " | " .$shop_type . " | " . $shop_type_status ."\n\r";

        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':shop_type', $shop_type, PDO::PARAM_STR);
        $stmt_update->bindParam(':shop_type_status', $shop_type_status, PDO::PARAM_STR);
        $stmt_update->execute();
    }

    // เพิ่มค่า offset สำหรับการดึงข้อมูลกลุ่มถัดไป
    $offset += $batch_size;

} while (true); // วนลูปจนกว่าจะไม่มีข้อมูลเหลือในกลุ่ม
