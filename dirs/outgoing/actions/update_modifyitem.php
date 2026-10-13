<?php
require_once "../../../config/connection.php";
session_start();

$User = $_SESSION['Uid'];
$SR_Number   = $_POST['SR_Number'];
$Temp_id = $_POST['Temp_id'];
$Action = $_POST['Action'];

try {
    $conn->beginTransaction();

    $upd_additional = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Modify_preselected_items] ?,?,?,?");
    $upd_additional->execute([$User,$SR_Number,$Temp_id,$Action]);

    $conn->commit();
    echo "success";

} catch (PDOException $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
    
