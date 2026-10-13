<?php
  require_once "../../../config/connection.php";
  $Branch = $_POST['Branch'] ?? '';


try {
  $conn->beginTransaction();

    $fetch_whscode = $conn->prepare("EXEC dbo.[IAP_WHSCODE_TYPE] ?");
    $fetch_whscode->execute([$Branch]);
    $get_whscode = $fetch_whscode->fetchAll(PDO::FETCH_ASSOC);

  $conn->commit();

  $response = array(
    "isSuccess" => 'success',
    "Data" => $get_whscode
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