<?php
	require_once "../../../config/connection.php";
	session_start();

	$User  = $_SESSION['Uid'];
	$SR_Number 	=	$_POST['SR_Number'] ?? null;
	$Model 	=	$_POST['Model'] ?? null;
	
	try{

		$conn->beginTransaction();
		$add_items = $conn->prepare("EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Create_Preselected_items] ?,?,?");
		$add_items->execute([$User,$SR_Number,$Model]);
		$conn->commit();
		echo "success";

	}catch(PDOException $e){
		$conn->rollback();
		echo "<b>Warning. Please Contact System Developer.<br/></b>".$e;getMessage();
	}


?>


