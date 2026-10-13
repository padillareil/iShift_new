<?php
	require_once "../../../../config/connection.php";
	session_start();

	$User 			= 	$_SESSION['Uid'];
	$ItemCode 		=	$_POST['ItemCode'] ?? null;
	$SRNumber 		=	$_POST['SRNumber'] ?? null;
	$Brand 			=	$_POST['Brand'] ?? null;
	$Model 			=	$_POST['Model'] ?? null;
	$Category 		=	$_POST['Category'] ?? null;
	$Quantity 		=	$_POST['Quantity'] ?? 0;

	
	try{

		$conn->beginTransaction();

		$ins_student = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Item_Preselected] ?,?,?,?,?,?,?");
		$ins_student->execute([$User,$ItemCode, $SRNumber, $Brand, $Model, $Category, $Quantity]);
		
		$conn->commit();
		echo "success";

	}catch(PDOException $e){
		$conn->rollback();
		echo "<b>Warning. Please Contact System Developer.<br/></b>".$e;getMessage();
	}


?>

