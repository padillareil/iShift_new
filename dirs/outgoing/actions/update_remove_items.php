<?php
require_once "../../../config/connection.php";
session_start();

$User = $_SESSION['Uid'];
$SR_Number   = $_POST['SR_Number'];

try {
    $conn->beginTransaction();

    $upd_remvditems = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Clear_PreselectedItems] ?,?");
    $upd_remvditems->execute([$User,$SR_Number]);

    $conn->commit();
    echo "success";

} catch (PDOException $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
    
