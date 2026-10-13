<?php
  require_once "../../../../config/connection.php";
  session_start();

  $Userid     = $_POST['Uid'];

try {
  $conn->beginTransaction();
    $fetch_myrequest = $conn->prepare("EXEC Display_StockRequest ?");
    $fetch_myrequest->execute([ $Userid ]);
    $get_request = $fetch_myrequest->fetch(PDO::FETCH_ASSOC);

  $conn->commit();

  $response = array(
    "isSuccess" => 'success',
    "Data" => $get_request
  );
  echo json_encode($response);

}catch (PDOException $e){
  $conn->rollback();
  $response = array(
    "isSuccess" => 'Failed',
    "Data" => "<b>Error. Please Contact System Developer. <br/></b>".$e->getMessage()
  );
  echo json_encode($response);
}
?>