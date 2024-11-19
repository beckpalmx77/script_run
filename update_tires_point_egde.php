<?php

include 'config/connect_db_my_sac_data2.php';

ini_set('memory_limit', '256M'); // เพิ่ม memory limit

$batch_size = 1000; // กำหนดขนาดกลุ่มที่จะประมวลผล
$offset = 0;

do {
    // ดึงข้อมูลทีละ 1000 แถว จาก ims_data_sale_sac_all
    $sql_sale = "SELECT SKU_CODE, AR_CODE FROM ims_data_sale_sac_all WHERE 1 LIMIT :offset, :batch_size";
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
        // ดึงข้อมูล TRD_U_POINT, TRD_S_POINT, และ TIRES_EDGE จาก ims_sac_tires_point ที่ SKU_CODE ตรงกัน
        $sql_find = "SELECT TRD_U_POINT, TRD_S_POINT, TIRES_EDGE FROM ims_sac_tires_point WHERE SKU_CODE = :sku_code";
        $query_find = $conn->prepare($sql_find);
        $query_find->bindParam(':sku_code', $sale->SKU_CODE, PDO::PARAM_STR);
        $query_find->execute();
        $tire_data = $query_find->fetch(PDO::FETCH_OBJ);

        // กำหนดค่าเริ่มต้น

        $trd_u_point = $tire_data->TRD_U_POINT ?? 0;
        $trd_s_point = $tire_data->TRD_S_POINT ?? 0;
        $tires_edge = $tire_data->TIRES_EDGE ?? "-";

        // ตรวจสอบ AR_CODE ใน ims_ar_shop เพื่อกำหนด SHOP_TYPE_STATUS
        $sql_find_shop = "SELECT * FROM ims_ar_shop WHERE ar_code = :ar_code";
        $query_find_shop = $conn->prepare($sql_find_shop);
        $query_find_shop->bindParam(':ar_code', $sale->AR_CODE, PDO::PARAM_STR);
        $query_find_shop->execute();

        // กำหนดค่า SHOP_TYPE_STATUS และปรับค่า TRD_S_POINT หากไม่ตรงกับ "Y"
        $shop_type_status = $query_find_shop->rowCount() > 0 ? "Y" : "N";
        if ($shop_type_status !== "Y") {
            $trd_s_point = 0;
        }

        // Update ข้อมูลใน ims_data_sale_sac_all
        $sql_update = "UPDATE ims_data_sale_sac_all 
                       SET TRD_U_POINT = :trd_u_point,
                           TRD_S_POINT = :trd_s_point,
                           TIRES_EDGE = :tires_edge
                       WHERE SKU_CODE = :sku_code";

        echo $sql_update . "\n\r" ;
        echo $shop_type_status . " | " .$sale->AR_CODE .  " | ". $tires_edge . " | " .  $trd_u_point . " | " . $trd_s_point . "\n\r"  ;

        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':trd_u_point', $trd_u_point, PDO::PARAM_STR);
        $stmt_update->bindParam(':trd_s_point', $trd_s_point, PDO::PARAM_STR);
        $stmt_update->bindParam(':tires_edge', $tires_edge, PDO::PARAM_STR);
        $stmt_update->bindParam(':sku_code', $sale->SKU_CODE, PDO::PARAM_STR);
        $stmt_update->execute();
    }

    // เพิ่มค่า offset สำหรับการดึงข้อมูลกลุ่มถัดไป
    $offset += $batch_size;

} while (true);
