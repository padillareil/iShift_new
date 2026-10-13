<?php
session_start();
require_once "../../../config/connection.php";
require_once "../../../config/functions.php";

if (!isset($_SESSION['Uid'])) {
    header('Location: ../../../login.php');
    exit();
}

$User = $_SESSION['Uid'];

try {

    $ua = $conn->prepare("EXEC Session_Account ?");
    $ua->execute([$User]);
    $user = $ua->fetch(PDO::FETCH_ASSOC);

    $Branch = $user['Branch'];

    // Generate next Picklist number
    $fetch_pknum = $conn->prepare(
        "EXEC [ISHIFT_ASSIGNMENT].[dbo].[Generate_ASGN_Number] ?"
    );
    $fetch_pknum->execute([$Branch]);
    $asgnnumber = $fetch_pknum->fetch(PDO::FETCH_ASSOC);
    $ASGN_Number = $asgnnumber['ASGN_Number'];
    // Build complete Picklist number
    $Latestnum = 'ASGN' . $BranchCode . $ASGN_Number;

} catch (PDOException $e) {

    echo "<b>Database Error:</b> " . htmlspecialchars($e->getMessage());
    exit();

}
?>

<div class="container-fluid">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group row align-items-center mb-2">
                        <label for="picklistNumber" class="col-sm-4 col-form-label text-dark-emphasis">
                            <small>Assignment No.:</small>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="picklistNumber" id="picklistNumber" class="form-control form-control-sm" style="background-color: #F7F7F7;" value="<?php echo htmlspecialchars($Latestnum); ?>" readonly required
                            >
                        </div>
                    </div>
                    <div class="form-group row align-items-center mb-2">
                        <label for="origin_branch" class="col-sm-4 col-form-label text-dark-emphasis">
                            <small>Branch:</small>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="origin_branch" id="origin_branch" class="form-control form-control-sm" style="background-color: #F7F7F7;" value="<?php echo htmlspecialchars($Branch); ?>" readonly required
                            >
                        </div>
                    </div>
                </div>
                <input type="hidden" name="origin_branch d-none" id="origin_branch" value="<?php echo $user['Branch']?>"> <!-- Origin Branch Transac -->
                   <div class="mt-2">
                       <div class="table-responsive table-sm border overflow-auto mt-3" style="height: 60vh;">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                               <thead>
                                   <tr class="text-center">
                                       <th class="fw-bold text-center table-primary" style="width: 200px;">Category</th>
                                       <th class="fw-bold text-center table-primary" style="width: 200px;">Brand</th>
                                       <th class="fw-bold text-center table-primary">Model</th>
                                       <th class="fw-bold text-center table-primary" style="width: 150px;">Picklist Quantity</th>
                                       <th class="fw-bold text-center" style="width: 150px; background-color: #fcf7d4;">SHOWROOM</th><!-- Multiple Branches -->
                                   </tr>
                               </thead>
                               <tbody id="load_forassignment_items">
                                       <!-- Display content here -->

                               </tbody>
                           </table>
                       </div>
                   </div>

                  <div class="row mt-3">
                      <div class="col-md-12 justify-content-end d-flex">
                          <button type="submit" class="btn btn-success shadow-sm mr-1" id="btn-submit-picklist">
                              Save
                          </button>
                          <button type="button" class="btn btn-light border shadow-sm" onclick="returnHomeIncoming()">
                              Cancel
                          </button>
                      </div>
                  </div>
            </div>
        </div>
    </section>
</div>


<script>
     /* Function load incoming page */
    function returnHomeIncoming() {
        $.post("dirs/incoming/incoming.php", {}, function (data) {
            $("#main-content").html(data);
        });
    }

</script>