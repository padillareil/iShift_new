<?php
require_once "../../../config/connection.php";

$CurrentPage = $_POST['CurrentPage'] ?? 1;
$PageSize    = $_POST['PageSize'] ?? 50;
$Search      = $_POST['Search'] ?? '';
$FilteBrand      = $_POST['FilteBrand'] ?? '';

try {
    $fetch_models = $conn->prepare(
        "EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Model_List] ?,?,?,?"
    );
    $fetch_models->execute([
        $CurrentPage,
        $PageSize,
        $Search,
        $FilteBrand
    ]);

    $models = $fetch_models->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "isSuccess" => "success",
        "Data"      => $models
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "isSuccess" => "Failed",
        "Data"      => "<b>Error. Please Contact System Developer.</b><br>" . $e->getMessage()
    ]);

}

?>