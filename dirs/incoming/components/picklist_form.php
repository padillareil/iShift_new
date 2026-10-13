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
    $BranchCode = $user['BranchCode'];

    // Generate next Picklist number
    $fetch_pknum = $conn->prepare(
        "EXEC [ISHIFT_PICKLIST].[dbo].[Generate_PKlist_Number] ?"
    );
    $fetch_pknum->execute([$Branch]);
    $pklnumber = $fetch_pknum->fetch(PDO::FETCH_ASSOC);
    $PK_Number = $pklnumber['PKList_Number'];
    // Build complete Picklist number
    $Latestnum = 'PKL' . $BranchCode . $PK_Number;

} catch (PDOException $e) {

    echo "<b>Database Error:</b> " . htmlspecialchars($e->getMessage());
    exit();

}
?>

<div class="container-fluid">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                    <div class="row align-items-start">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <label for="picklistNumber" class="text-dark-emphasis mb-0 mr-2">
                                    <small>Picklist No.:</small>
                                </label>
                                <input type="text" name="picklistNumber" id="picklistNumber" class="form-control form-control-sm" style="background: #F7F7F7;" readonly required value="<?php echo htmlspecialchars($Latestnum); ?>">
                            </div>
                        </div>

                        <!-- Right side -->
                        <div class="col-md-4 ml-auto">
                            <div class="d-flex align-items-center mb-2">
                                <label for="collectors_name" class="text-dark-emphasis mb-0 mr-2">
                                    <small>Collector:</small>
                                </label>
                                <input type="text" name="collectors_name" id="collectors_name" class="form-control form-control-sm" style="background: #fcf7d4;" required>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="origin_branch d-none" id="origin_branch" value="<?php echo $user['Branch']?>"> <!-- Origin Branch Transac -->

                   <div class="mt-2">
                       <div class="table-responsive table-sm border overflow-auto mt-3" style="height: 60vh;">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                               <thead>
                                   <tr class="text-center">
                                       <th class="fw-bold text-center table-primary" style="width: 60px;">#</th>
                                       <th class="fw-bold text-center table-primary" style="width: 200px;">Category</th>
                                       <th class="fw-bold text-center table-primary" style="width: 200px;">Brand</th>
                                       <th class="fw-bold text-center table-primary">Model</th>
                                       <th class="fw-bold text-center table-primary" style="width: 150px;">Request Quantity</th>
                                       <th class="fw-bold text-center" style="width: 150px; background-color: #fcf7d4;">Issued Quantity</th>
                                   </tr>
                               </thead>
                               <tbody id="load_picklist_items">
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
    $("#btn-submit-picklist").on("click", function (e) {
        e.preventDefault();
        const PicklistNumber = $("#picklistNumber").val();
        const Branch = $("#origin_branch").val();
        const Collector = $("#collectors_name").val().trim();
        if (Collector === "") {
                Swal.fire({
                    title: "Required Field",
                    text: "Please enter the collector's name.",
                    icon: "warning"
                });

                $("#collectors_name").focus();
                return;
            }

        const Items = [];
        $("#load_picklist_items tr").each(function () {
       
       const row = $(this);

       if (
           row.find(".item_mothernumber").length === 0 ||
           row.find(".item-model").length === 0 ||
           row.find(".item_quantity").length === 0 ||
           row.find(".item_issued_qty").length === 0
       ) {
           return;
       }

       Items.push({
           DocEntrySRN: row.find(".item_mothernumber").val(),
           ItemModel: row.find(".item-model").text().trim(),
           RequestQty: parseInt(row.find(".item_quantity").text().trim(), 10) || 0,
           IssuedQty: parseInt(row.find(".item_issued_qty").val(), 10) || 0
       });
       });


        
        Swal.fire({
            title: "Save Changes?",
            text: "Please double check before you save.",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Yes, Save",
            cancelButtonText: "Cancel",
            reverseButtons: true

        }).then(function (result) {

            if (!result.isConfirmed) {
                return;
            }
            $.post("dirs/incoming/actions/save_picklistsetup.php",{
                    PicklistNumber: PicklistNumber,
                    Branch: Branch,
                    Collector : Collector,
                    Items: JSON.stringify(Items)
                },
                function (data) {
                    let response;
                    try {
                        response = typeof data === "string"? JSON.parse(data): data;
                    } catch (error) {
                        Swal.fire({
                            icon: "error",
                            title: "Invalid Server Response",
                            text: "Unable to process the server response."
                        });
                        return;
                    }
                    if (response.status === "OK") {

                        Swal.fire({
                            icon: "success",
                            title: "Picklist Saved",
                            text: response.message || "Picklist successfully created.",
                            confirmButtonText: "OK",
                            allowOutsideClick: false
                        }).then(function (result) {

                            if (result.isConfirmed) {
                                returnHomeIncoming();
                            }

                        });

                        return;
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Save Failed",
                        text: response.message || "Unable to save picklist."
                    });
                }

            ).fail(function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Save Failed",
                    text: "Unable to save changes. Please try again."
                });
            });
        });
    });
</script>

<style>
    .highlight-row td {
        background-color: #fcf7d4 !important;
    }
</style>

