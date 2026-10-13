<?php
require_once "../../../config/connection.php";
session_start();

$User = $_SESSION["Uid"] ?? null;
$Branch = $_POST["Branch"] ?? null;
$PicklistNumber = $_POST["PicklistNumber"] ?? null;
$RowNum = $_POST['Rownum'] ?? null;

try {
    $conn->beginTransaction();

    $ins_picklist_header = $conn->prepare("
        EXEC [ISHIFT_PICKLIST].[dbo].[CreatePicklist_Entry_Header] ?, ?, ?");
    $ins_picklist_header->execute([
        $User,
        $Branch,
        $PicklistNumber
    ]);

    $ins_picklist_item = $conn->prepare("
        EXEC [ISHIFT_PICKLIST].[dbo].[CreatePicklist_Items] ?, ?");
    $ins_picklist_item->execute([
        $RowNum,
        $PicklistNumber
    ]);

    $conn->commit();

    echo json_encode([
        "status"         => "OK",
        "PicklistNumber" => $PicklistNumber,
        "message"        => "Picklist successfully created."
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        "status"  => "ERROR",
        "message" => $e->getMessage()
    ]);
}
?>