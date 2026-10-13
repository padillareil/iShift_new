<?php
	require_once "../../../config/connection.php";
	session_start();
	// Document Header for Stock Request
	$User			= 	$_SESSION['Uid'];
	$SRNumber 		=	$_POST['SRNumber'] ?? null;
	$Origin 		=	$_POST['Origin'] ?? null;
	$OriginWhs 		=	$_POST['OriginWhs'] ?? null;
	$DestinationWhs =	$_POST['DestinationWhs'] ?? null;
	$Purpose 		=	$_POST['Purpose'] ?? null;
	$Remarks 		=	$_POST['Remarks'] ?? null;
	try{

		$conn->beginTransaction();
		$ins_stockreq_header = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Create_StockRequest] ?,?,?,?,?,?,?");
		$ins_stockreq_header->execute([$User,$SRNumber, $Origin, $OriginWhs, $DestinationWhs, $Purpose,$Remarks]);
	
		$conn->commit();
		echo json_encode([
		    "status" => "OK",
		    "message" => "success."
		]);

	}catch(PDOException $e){
		$conn->rollback();
		echo "<b>Warning. Please Contact System Developer.<br/></b>".$e;getMessage();
	}
?>



