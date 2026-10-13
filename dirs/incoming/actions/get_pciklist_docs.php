<?php

require_once "../../../config/connection.php";
session_start();

$User        = $_SESSION['Uid'] ?? null;
$CurrentPage = $_POST['CurrentPage'] ?? 1;
$PageSize    = $_POST['PageSize'] ?? 50;
$Search      = $_POST['Search'] ?? '';

try {

    $fetch_picklist = $conn->prepare(
        "EXEC [ISHIFT_PICKLIST].[dbo].[Picklist_Documents] ?, ?, ?, ?"
    );

    $fetch_picklist->execute([
        $CurrentPage,
        $PageSize,
        $Search,
        $User
    ]);

    $badge = $fetch_picklist->fetch(PDO::FETCH_ASSOC);
    $TotalPicklist = $badge['TotalPicklist'] ?? 0;
    $get_picklist = [];

    if ($fetch_picklist->nextRowset()) {
        $get_picklist = $fetch_picklist->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode([
        "isSuccess"     => "success",
        "TotalPicklist" => (int) $TotalPicklist,
        "Data"          => $get_picklist
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "isSuccess" => "Failed",
        "Data"      => "<b>Error. Please Contact System Developer.</b><br>" . $e->getMessage()
    ]);
}

?>