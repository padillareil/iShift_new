<?php
require_once "../../../config/connection.php";
session_start();

$User        = $_SESSION['Uid'] ?? null;
$CurrentPage = $_POST['CurrentPage'] ?? 1;
$PageSize    = $_POST['PageSize'] ?? 50;
$Search      = $_POST['Search'] ?? '';

try {
    $fetch_stocksrequest = $conn->prepare(
        "EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[StockRequest_List] ?,?,?,?"
    );
    $fetch_stocksrequest->execute([
        $CurrentPage,
        $PageSize,
        $Search,
        $User
    ]);

    $get_request = $fetch_stocksrequest->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "isSuccess" => "success",
        "Data"      => $get_request
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "isSuccess" => "Failed",
        "Data"      => "<b>Error. Please Contact System Developer.</b><br>" . $e->getMessage()
    ]);

}

?>