<?php
	require_once "../../../../config/connection.php";
	session_start();
	// Document Header for Stock Request
	$User			= 	$_SESSION['Uid'];
	$SRNumber 		=	$_POST['SRNumber'] ?? null;
	$Origin 		=	$_POST['Origin'] ?? null;
	$OriginWhs 		=	$_POST['OriginWhs'] ?? null;
	$DestinationWhs =	$_POST['DestinationWhs'] ?? null;
	$Purpose 		=	$_POST['Purpose'] ?? null;
	$Remarks 		=	$_POST['Remarks'] ?? null;
	//Multiple Items
	$Items = json_decode($_POST['Items'] ?? '[]', true);
	try{

		$conn->beginTransaction();
		$ins_stockreq_header = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Create_StockRequest_Header] ?,?,?,?,?,?,?");
		$ins_stockreq_header->execute([$User,$SRNumber, $Origin, $OriginWhs, $DestinationWhs, $Purpose,$Remarks]);
		
		$stmtInsertItem = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Create_StockRequest_Items] ?,?,?,?,?,?,?");
		foreach ($Items as $item) {
		        $ItemCode     = $item['ItemCode'] ?? null;
		        $ItemName     = $item['ItemName'] ?? null;
		        $ItemBrand    = $item['ItemBrand'] ?? null;
		        $ItemCategory = $item['ItemCategory'] ?? null;
		        $Request_Qty  = $item['RequestQty'] ?? 0;

		        $stmtInsertItem->execute([
		            $User,
		            $SRNumber,
		            $ItemCode,
		            $ItemBrand,
		            $ItemName,
		            $ItemCategory,
		            $Request_Qty
		        ]);
		    }

 

	
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



