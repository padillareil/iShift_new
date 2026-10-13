<?php
require_once "../../../config/connection.php";
session_start();

$User        = $_SESSION['Uid'] ?? null;
$CurrentPage = $_POST['CurrentPage'] ?? 1;
$PageSize    = $_POST['PageSize'] ?? 50;
$Search      = $_POST['Search'] ?? '';
$Srn_number = $_POST['Srn_number'] ?? '';

try {
    $fetch_preselected = $conn->prepare(
        "EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Preselected_items_List] ?,?,?,?,?"
    );
    $fetch_preselected->execute([
        $CurrentPage,
        $PageSize,
        $Search,
        $User,
        $Srn_number
    ]);

    $get_request = $fetch_preselected->fetchAll(PDO::FETCH_ASSOC);
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