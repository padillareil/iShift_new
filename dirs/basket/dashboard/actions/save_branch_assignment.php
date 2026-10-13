<?php
require_once "../../../../config/connection.php";
session_start();

$User = $_SESSION['Uid'];

/* ===================== SINGLE VALUES ===================== */
$LoadingB_Num   = $_POST['LoadingB_Num'] ?? '';
$PickListNum   = $_POST['PickListNum'] ?? '';
$Remarks       = $_POST['Remarks'] ?? '';

/* Summary */
$SummaryBranch     = $_POST['BranchFor'] ?? [];
$SummaryBrand     = $_POST['SummaryBrand'] ?? [];
$SummaryModel     = $_POST['SummaryModel'] ?? [];
$SummaryCategory  = $_POST['SummaryCategory'] ?? [];
$SummaryItemCode  = $_POST['SummaryItemCode'] ?? [];
$SummarySerial    = $_POST['SummarySerial'] ?? [];
$SummaryAllocated = $_POST['SummaryAllocated'] ?? [];

/* ===================== ARRAY VALUES ===================== */
/* Serialized */
$Serial    = $_POST['Serial'] ?? [];
$Brand     = $_POST['Brand'] ?? [];
$Model     = $_POST['Model'] ?? [];
$ItemCode  = $_POST['ItemCode'] ?? [];
$Category  = $_POST['Category'] ?? [];
$Quantity  = $_POST['Quantity'] ?? [];

/* Non-Serialized */
$NonItemCode  = $_POST['NonSerializedItemCode'] ?? [];
$NonBrand     = $_POST['NonSerializedBrand'] ?? [];
$NonModel     = $_POST['NonSerializedModel'] ?? [];
$NonCategory  = $_POST['NonSerializedCategory'] ?? [];
$NonQuantity  = $_POST['NonQuantity'] ?? [];

try {
    $conn->beginTransaction();

    /* ===================== GENERATE RECEIVING NUMBER ===================== */
    $stmt = $conn->prepare("EXEC dbo.[Branch_Assignment_NumGenerator] ?");
    $stmt->execute([$User]);
    $BA_Number = $stmt->fetch(PDO::FETCH_ASSOC)['BA_Number'];

    /* ===================== INSERT HEADER ===================== */
    $stmt = $conn->prepare("EXEC dbo.[BA_Mother] ?,?,?,?,?");
    $stmt->execute([
        $User,
        $LoadingB_Num,
        $PickListNum,
        $BA_Number,
        $Remarks
    ]);

    /* ===================== SERIALIZED ITEMS ===================== */
    $stmtSerialized = $conn->prepare("EXEC dbo.[BA_IN_Serialized] ?,?,?,?,?,?,?,?,?");

    foreach ($Serial as $i => $serialVal) {
        $stmtSerialized->execute([
            $SummaryBranch[$i],
            $LoadingB_Num,
            $BA_Number,
            $serialVal,
            $ItemCode[$i],
            $Model[$i],
            $Brand[$i],
            $Category[$i],
            $Quantity[$i]
        ]);
    }

    /* ===================== NON-SERIALIZED ITEMS ===================== */
    $stmtNon = $conn->prepare("EXEC dbo.[BA_IN_NoNSerialized] ?,?,?,?,?,?,?,?");

    foreach ($NonItemCode as $i => $code) {
        $stmtNon->execute([
            $LoadingB_Num,
            $BA_Number,
            $SummaryBranch[$i],
            $code,
            $NonModel[$i],
            $NonBrand[$i],
            $NonCategory[$i],
            $NonQuantity[$i]
        ]);
    }

    $conn->commit();

    echo json_encode([
        "isSuccess" => "success",
        "ReceivingNumber" => $BA_Number
    ]);
} catch (PDOException $e) {
    $conn->rollback();
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo json_encode([
        "isSuccess" => "Failed",
        "message" => $e->getMessage()
    ]);
}
