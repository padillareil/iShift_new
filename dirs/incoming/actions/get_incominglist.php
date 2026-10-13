<?php
require_once "../../../config/connection.php";
session_start();

$User        = $_SESSION['Uid'] ?? null;
$CurrentPage = $_POST['CurrentPage'] ?? 1;
$PageSize    = $_POST['PageSize'] ?? 50;
$Search      = $_POST['Search'] ?? '';

try {
    $fetch_incomingrequest = $conn->prepare(
        "EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[IncomingRequest_List] ?,?,?,?"
    );
    $fetch_incomingrequest->execute([
        $CurrentPage,
        $PageSize,
        $Search,
        $User
    ]);

    $get_incoming = $fetch_incomingrequest->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "isSuccess" => "success",
        "Data"      => $get_incoming
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "isSuccess" => "Failed",
        "Data"      => "<b>Error. Please Contact System Developer.</b><br>" . $e->getMessage()
    ]);

}

?>