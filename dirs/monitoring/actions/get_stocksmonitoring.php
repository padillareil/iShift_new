<?php
require_once "../../../config/connection.php";

$CurrentPage = $_POST['CurrentPage'] ?? 1;
$PageSize    = $_POST['PageSize'] ?? 50;
$Search      = $_POST['Search'] ?? '';

try {
    $fetch_stocksrequest = $conn->prepare(
        "EXEC Monitor_StocksMovement ?,?,?"
    );
    $fetch_stocksrequest->execute([
        $CurrentPage,
        $PageSize,
        $Search
    ]);

    $get_stocks = $fetch_stocksrequest->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "isSuccess" => "success",
        "Data"      => $get_stocks
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "isSuccess" => "Failed",
        "Data"      => "<b>Error. Please Contact System Developer.</b><br>" . $e->getMessage()
    ]);

}

?>