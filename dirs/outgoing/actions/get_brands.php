<?php
  require_once "../../../config/connection.php";
try {
  $conn->beginTransaction();

    $fetch_brands = $conn->prepare("
      SELECT
          FirmName AS Brand 
      FROM VIAC_PROD_NEW.dbo.OMRC WITH (NOLOCK)
      GROUP BY FirmName
      ORDER BY FirmName
    ");
    $fetch_brands->execute();
    $brands = $fetch_brands->fetchAll(PDO::FETCH_ASSOC);

  $conn->commit();

  $response = array(
    "isSuccess" => 'success',
    "Data" => $brands
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