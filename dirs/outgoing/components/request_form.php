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

    // Generate next SR number
    $fetch_sr = $conn->prepare(
        "EXEC [ISHIFT_STOCK_REQUESTS].[dbo].[Generate_SR_Number] ?"
    );
    $fetch_sr->execute([$Branch]);
    $srnumber = $fetch_sr->fetch(PDO::FETCH_ASSOC);
    $SR_Number = $srnumber['SR_Number'];
    // Build complete SR number
    $Latestnum = 'SRN' . $BranchCode . $SR_Number;

    /*IAP Branches*/
    $fetch_branches = $conn->prepare("EXEC dbo.[IAP_BRANCHLIST]");
    $fetch_branches->execute();
    $brances = $fetch_branches->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    echo "<b>Database Error:</b> " . htmlspecialchars($e->getMessage());
    exit();

}
?>

<div class="card shadow-sm overall-progress" id="dashboard-display">
    <div class="card-body">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <form method="POST" id="frm-request-sts">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex flex-column gap-1 col-4">
                                <div class="d-flex align-items-baseline gap-3">
                                    <label for="srnForm" class="form-label text-dark-emphasis col-2"><small>SRN:</small></label>
                                    <input type="text" name="srnForm" id="srnForm" class="form-control form-control-sm col ms-1" style="background: #F7F7F7" readonly required value="<?php echo htmlspecialchars($Latestnum); ?>">
                                </div>
                                <div class="input-group col p-0 d-flex gap-1">
                                    <div class="col p-0 d-flex align-items-baseline gap-1 col-7">
                                        <label for="desForm" class="form-label text-dark-emphasis col-4"><small>Destination:</small></label>
                                        <input type="text" name="desForm" id="desForm" class="form-control form-control-sm col" style="background: #F7F7F7;" required readonly value="<?php echo $user['Branch']?>">
                                    </div>
                                    <div class="col p-0 d-flex align-items-baseline">
                                        <label for="desCodeForm" class="form-label text-dark-emphasis col-5"><small>WHCode:</small></label>
                                        <select name="desCodeForm" id="desCodeForm" class="form-select form-select-sm col" style="background: #fcf7d4; appearance: auto;" required>

                                        </select>
                                    </div>
                                </div>
                                <div class="input-group col p-0 d-flex align-items-baseline gap-1">
                                    <div class="col p-0 d-flex align-items-baseline gap-1 col-7">
                                        <label for="user-origin" class="form-label text-dark-emphasis col-4"><small>Origin:</small></label>
                                        <select name="user-origin" id="user-origin" class="form-control form-control-sm col" style="background: #fcf7d4; appearance: auto">
                                            <option value="NEWSC1" selected>NEWSC1</option>
                                            <?php foreach ($brances as $branch): ?>
                                                <option value="<?php echo htmlspecialchars($branch['Branch']); ?>">
                                                    <?php echo htmlspecialchars($branch['Branch']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col p-0 d-flex align-items-baseline">
                                        <label for="originCodeForm" class="form-label text-dark-emphasis col-5"><small>WHCode:</small></label>
                                        <select name="originCodeForm" id="originCodeForm" class="form-select form-select-sm col" style="background: #fcf7d4; appearance: auto" required>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1 col-2">
                                <div class="d-flex align-items-baseline gap-3">
                                    <label for="date" class="form-label text-dark-emphasis col-4"><small>Date:</small></label>
                                    <input type="date" name="date" id="formattedDate" class="form-control form-control-sm col" style="background: #F7F7F7" readonly value="<?php echo date('Y-m-d'); ?>"  required>
                                </div>
                                <div class="d-flex align-items-baseline gap-3">
                                    <label for="statusForm" class="form-label text-dark-emphasis col-4"><small>Status:</small></label>
                                    <input type="text" name="statusForm" id="statusForm" class="form-control form-control-sm col" value="NEW" style="background: #F7F7F7" readonly required>
                                </div>
                            </div>
                        </div>

                       <div class="card card-body mt-2">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm border btn-primary mr-2 shadow-sm" onclick="mdladdItem()" title="Additional Item">
                                    <i class="bi bi-plus-lg mr-1"></i>
                                    Add Item
                                </button>
                                <button type="button" class="btn btn-sm border btn-danger shadow-sm" onclick="removePreselected()" title="Remove All Item">
                                    <i class="bi bi-trash3 mr-1"></i>
                                    Clear
                                </button>
                            </div>
                           <div class="input-group border rounded-3 bg-white px-1 py-1 shadow-sm" style="max-width: 360px;">
                               <span class="input-group-text bg-transparent border-0 py-0 pe-1">
                                   <i class="bi bi-search text-muted"></i>
                               </span>
                               <input type="search" class="form-control bg-transparent border-0 shadow-none py-0 small" id="search-preitems" placeholder="Search...">
                           </div>
                           <div class="mb-1 justify-content-end d-flex">
                               <nav aria-label="Page navigation">
                                   <ul class="pagination pagination-sm mb-0" id="pagination-preitems">
                                       <li class="page-item" id="li-prev-preitems">
                                           <a class="page-link shadow-none" href="#" id="btn-preview-preitems">
                                               <i class="bi bi-chevron-left small"></i>
                                               
                                           </a>
                                       </li>
                                       <li class="page-item" id="li-next-preitems">
                                           <a class="page-link shadow-none" href="#" id="btn-next-preitems">
                                               <i class="bi bi-chevron-right small"></i>
                                           </a>
                                       </li>
                                   </ul>
                               </nav>
                           </div>

                           <div class="justify-content-end d-flex">
                               <div id="page-info-preitems" class="mt-1 text-muted"></div>
                           </div>
                           <div class="table-responsive table-sm border overflow-auto mt-3" style="height: 50vh;">
                                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                                   <thead>
                                       <tr style="background:#fcf7d4;" class="text-center table-primary">
                                           <th class="fw-bold text-center">#</th>
                                           <th class="fw-bold text-center">Brand</th>
                                           <th class="fw-bold text-center">Model</th>
                                           <th class="fw-bold text-center">Category</th>
                                           <th class="fw-bold text-center">Quantity</th>
                                           <th class="fw-bold text-center">Actions</th>
                                       </tr>
                                   </thead>
                                   <tbody id="load_preselected_items">
                                           <!-- Display content here -->

                                   </tbody>
                               </table>
                           </div>
                       </div>

                      <div class="row mt-3">
                          <div class="col-md-6">
                              <div class="d-flex align-items-baseline mb-2">
                                  <label for="purposeForm" class="text-dark-emphasis mb-0 mr-2"
                                         style="width: 130px;">
                                      <small>Purpose:</small>
                                  </label>

                                  <select name="purposeForm" id="purposeForm" class="form-control form-control-sm" style="background: #fcf7d4; width: 220px;" required>
                                      <option value="" selected>Select purpose</option>
                                      <option value="Stock Refill">Stock Refill</option>
                                      <option value="With Customer">With Customer</option>
                                  </select>
                              </div>

                              <div class="d-flex align-items-baseline mb-2">
                                  <label for="reqByForm" class="text-dark-emphasis mb-0 mr-2" style="width: 130px;">
                                      <small>Requested by:</small>
                                  </label>
                                  <input type="text" name="reqByForm" id="reqByForm" class="form-control form-control-sm" style="background: #F7F7F7; width: 220px;" readonly value="<?php echo $user['Fullname']?>">
                              </div>

                              <div class="d-flex align-items-start">
                                  <label for="remarksForm" class="text-dark-emphasis mb-0 mr-2" style="width: 130px;">
                                      <small>Remarks:</small>
                                  </label>

                                  <div style="width: 520px;">
                                      <textarea name="remarksForm" id="remarksForm" class="form-control form-control-sm" rows="3" style="background: #fcf7d4; resize: none;" maxlength="100"></textarea>
                                      <small class="text-muted">
                                          Max 100 characters.
                                      </small>
                                  </div>
                              </div>
                          </div>

                          <!-- Summary / Actions -->
                          <div class="col-md-6 d-flex flex-column align-items-end">
                              <div class="border rounded px-3 py-2 bg-light mb-3" style="width: 220px;">
                                  <div class="d-flex justify-content-between align-items-center">
                                      <span class="font-weight-bold text-muted">
                                          <i class="bi bi-box-seam mr-1"></i>
                                          Total Quantity
                                      </span>
                                      <span id="totalQuantity" class="font-weight-bold" style="font-size: 18px;">
                                          0
                                      </span>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-12 justify-content-end d-flex">
                              <button type="submit" class="btn btn-success shadow-sm mr-1" id="submitFormBtn">
                                  Submit
                              </button>
                              <button type="button" class="btn btn-light border shadow-sm" onclick="loadOutgoing()">
                                  Cancel
                              </button>
                          </div>
                      </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    $("#desForm").on("change", function() {
        loadDestinationWhscodes();
    });

    /*Form submit stock request*/
        $("#frm-request-sts").on("submit", function(e) {

            e.preventDefault();

            var form = $(this);

            var Purpose        = $("#purposeForm").val();
            var Origin         = $("#user-origin").val();
            var OriginWhs      = $("#originCodeForm").val();
            var DestinationWhs = $("#desCodeForm").val();
            var Remarks        = $("#remarksForm").val();
            var SRNumber       = $("#srnForm").val();

            var Items = [];

            // Get selected items
            $("#load_preselected_items tr").each(function() {

                var row = $(this);

                // Ignore empty/loading rows
                if (
                    row.find(".item-brand").length === 0 ||
                    row.find(".item-model").length === 0 ||
                    row.find(".item-quantity").length === 0
                ) {
                    return;
                }

                Items.push({
                    ItemBrand: row.find(".item-brand").text().trim(),
                    ItemName: row.find(".item-model").text().trim(),
                    ItemCategory: row.find(".item-category").text().trim(),
                    RequestQty: parseInt(
                        row.find(".item-quantity").text().trim()
                    ) || 0
                });

            });

            // Validate items
            if (Items.length === 0) {

                Swal.fire({
                    icon: "warning",
                    title: "No Items Added",
                    text: "Please add item before submitting."
                });

                return;
            }

            // Validate warehouse
            if (!OriginWhs || !DestinationWhs) {

                Swal.fire({
                    icon: "warning",
                    title: "Required Fields",
                    text: "Please fill all the required fields."
                });

                return;
            }

            // Confirmation
            Swal.fire({
                title: "Submit Stock Request?",
                text: "Please double check before you submit.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, Submit",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then(function(result) {

                if (!result.isConfirmed) {
                    return;
                }

                // Disable submit button
                var submitBtn = form.find("#submitFormBtn");

                submitBtn
                    .prop("disabled", true)
                    .html(`
                        <span class="spinner-border spinner-border-sm mr-1"></span>
                        Processing
                    `);

                // Submit request
                $.post(
                    "dirs/outgoing/actions/save_submit_request.php",
                    {
                        SRNumber: SRNumber,
                        Purpose: Purpose,
                        Origin: Origin,
                        OriginWhs: OriginWhs,
                        DestinationWhs: DestinationWhs,
                        Remarks: Remarks
                    },
                    function(data) {

                        var response;

                        try {
                            response = JSON.parse(data);
                        } catch (e) {

                            console.log(data);

                            submitBtn
                                .prop("disabled", false)
                                .html("Submit");

                            Swal.fire({
                                icon: "error",
                                title: "Invalid Response",
                                text: "The server returned an invalid response."
                            });

                            return;
                        }

                        if (response.status === "OK") {
                            loadPreSelected_Items();
                            Swal.fire({
                                icon: "success",
                                title: "Request successfully submitted",
                                showConfirmButton: true,
                                confirmButtonText: "OK"
                            }).then(function() {

                                $.post("./actions/log.php", {
                                    Activity: "REQUESTED",
                                    Reference: response.SRNumber
                                }, function(res) {

                                    console.log("RESPONSE:", res);

                                });

                                location.reload();

                            });

                        } else {

                            Swal.fire({
                                icon: "error",
                                title: response.SRNumber + " has been created",
                                text: response.message
                            });

                            submitBtn
                                .prop("disabled", false)
                                .html("Submit");
                        }

                    }
                ).fail(function() {

                    submitBtn
                        .prop("disabled", false)
                        .html("Submit");

                    Swal.fire({
                        icon: "error",
                        title: "Submission Failed",
                        text: "Unable to submit the request. Please try again."
                    });

                });

            });

        });

        $("#user-origin").on("change", function () {
            var branch = $(this).val();
            loadOriginWhscodes(branch);
        });


/*Function to load all list of pre selected items*/
        var CurrentPage = 1; 
        var PageSize = 50; 
        var totalPages = 1; 
        var isPackageMode = false; 
        var selectedItems = [];

      function loadPreSelected_Items(page = 1) {
          CurrentPage = page;

          var itemDisplay = $("#load_preselected_items");

          itemDisplay.html(`
              <tr>
                  <td colspan="6" class="text-center py-5">
                      <div class="mb-3">
                          <div class="spinner-border text-primary" role="status">
                              <span class="sr-only">Loading...</span>
                          </div>
                      </div>
                      <h6 class="fw-semibold mb-1">Loading...</h6>
                      <p class="text-muted mb-0">
                          Please wait while we load the items.
                      </p>
                  </td>
              </tr>
          `);

          var Search = $("#search-preitems").val();
          var Srn_number = $("#srnForm").val();

          $.post("dirs/outgoing/actions/get_pre_selecteditems.php", {
              CurrentPage,
              PageSize,
              Search,
              Srn_number
          }, function (data) {

              let response;

              try {
                  response = JSON.parse(data);
              } catch (e) {

                  itemDisplay.html(`
                      <tr>
                          <td colspan="6">
                              <div class="text-center py-5">
                                  <div class="mb-3">
                                      <i class="bi bi-file-earmark text-secondary" style="font-size: 4rem;"></i>
                                  </div>
                                  <h5 class="fw-semibold mb-2">
                                      No Record Found
                                  </h5>
                                  <p class="text-muted mb-4">
                                      There are currently no items available.
                                  </p>
                              </div>
                          </td>
                      </tr>
                  `);

                  return;
              }

              if ($.trim(response.isSuccess) === "success") {

                  // No records
                  if (!response.Data || response.Data.length === 0) {

                      itemDisplay.html(`
                          <tr>
                              <td colspan="6">
                                  <div class="text-center py-5">
                                      <div class="mb-3">
                                          <i class="bi bi-file-earmark text-secondary" style="font-size: 4rem;"></i>
                                      </div>
                                      <h5 class="fw-semibold mb-2">
                                          No Record Found
                                      </h5>
                                      <p class="text-muted mb-4">
                                          There are currently no items available.
                                      </p>
                                  </div>
                              </td>
                          </tr>
                      `);

                      totalPages = 1;
                      PrePaginationUi();
                      prepageNumber();

                      return;
                  }

                  // Has records
                  PreItemsContent(response.Data);

                  totalPages = parseInt(response.Data[0].TotalPages) || 1;

                  PrePaginationUi();
                  prepageNumber();

              } else {
                  showEmptyStatePreItems("");
              }

          });
      }

        function PreItemsContent(data) {
            const itemDisplay = $("#load_preselected_items");
            itemDisplay.empty();
            let totalQuantity = 0;
            data.forEach(item => {
                const quantity = parseInt(item.Quantity) || 0;
                totalQuantity += quantity;
                itemDisplay.append(`
                    <tr class="highlight-row">
                        <td class="text-center font-weight-bold text-muted">
                            ${item.RowNum ?? '--'}
                        </td>

                        <td>
                            <div class="text-center item-brand">
                                ${item.Brand ?? '--'}
                            </div>
                        </td>

                        <td>
                            <div class="text-center item-model">
                                ${item.Model ?? '--'}
                            </div>
                        </td>

                        <td>
                            <div class="text-center">
                                ${item.Category ?? '--'}
                            </div>
                        </td>

                        <td>
                            <div class="text-center item-quantity">
                                ${item.Quantity ?? '--'}
                            </div>
                        </td>

                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="itemAdditional(${item.Temp_id})">
                                <i class="bi bi-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="itemDeductions(${item.Temp_id})">
                                <i class="bi bi-dash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            $("#totalQuantity").text(totalQuantity);
        }

                /*Function for no record of beverages*/
                function showEmptyStatePreItems(message = "No records found") {
                    $("#load_preselected_items").html(`
                         <tr>
                            <td colspan="6">
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-file-earmark text-secondary" style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="fw-semibold mb-2">
                                        No Record Found
                                    </h5>
                                    <p class="text-muted mb-4">
                                        There are currently no items available.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    `);
                }

                /* Function to update text & disable/enable prev/next buttons */
                function PrePaginationUi() {
                    $("#page-info-preitems").text("Page " + CurrentPage + " of " + totalPages);
                    
                    if (CurrentPage <= 1) {
                        $("#li-prev-preitems").addClass("disabled");
                    } else {
                        $("#li-prev-preitems").removeClass("disabled");
                    }

                    if (CurrentPage >= totalPages) {
                        $("#li-next-preitems").addClass("disabled");
                    } else {
                        $("#li-next-preitems").removeClass("disabled");
                    }
                }

                /* Function to build list of pagination numbers */
                function prepageNumber() {
                    // 1. Remove previously generated page numbers
                    $("#pagination-preitems li.page-number-preitems").remove();
                    
                    let prevLi = $("#li-prev-preitems");
                    let maxVisible = 5;
                    let start = Math.max(1, CurrentPage - 2);
                    let end = Math.min(totalPages, start + maxVisible - 1);

                    if (end - start < maxVisible - 1) {
                        start = Math.max(1, end - maxVisible + 1);
                    }

                    // Helper function moved to top of block scope
                    function insertPageVerified(i, ref) {
                        let activeClass = (i === CurrentPage) ? "active" : "";
                        let li = `
                            <li class="page-item page-number-preitems ${activeClass}">
                                <a class="page-link shadow-none" href="#" data-page="${i}">${i}</a>
                            </li>
                        `;
                        return $(li).insertAfter(ref);
                    }

                    if (start > 1) {
                        prevLi = insertPageVerified(1, prevLi);

                        if (start > 2) {
                            let ellipsis = `<li class="page-item page-number-preitems disabled"><span class="page-link">...</span></li>`;
                            prevLi = $(ellipsis).insertAfter(prevLi);
                        }
                    }

                    for (let i = start; i <= end; i++) {
                        prevLi = insertPageVerified(i, prevLi);
                    }

                    if (end < totalPages) {
                        if (end < totalPages - 1) {
                            let ellipsis = `<li class="page-item page-number-preitems disabled"><span class="page-link">...</span></li>`;
                            prevLi = $(ellipsis).insertAfter(prevLi);
                        }
                        insertPageVerified(totalPages, prevLi);
                    }
                }

                /* 3. Event Delegation for Number Clicks */
                $(document).on("click", "#pagination-preitems .page-number-preitems a", function (e) {
                    e.preventDefault();
                    let page = parseInt($(this).data("page"));
                    if (page && page !== CurrentPage) {
                        loadPreSelected_Items(page);
                    }
                });


               /* Search preselected items */
              $("#search-preitems").on("input", function() {
                  loadPreSelected_Items();
              });


                  /* Pagination + Fetch Blocked srvounts */
                  $("#btn-preview-preitems").on("click", function(e) {
                      e.preventDefault();

                      if (CurrentPage > 1) {
                          loadPreSelected_Items(CurrentPage - 1);
                      }
                  });

                /*Function load all important tags tickets*/
                  $("#btn-next-preitems").on("click", function(e) {
                      e.preventDefault();

                      if (CurrentPage < totalPages) {
                          loadPreSelected_Items(CurrentPage + 1);
                      }
                  });

                  $(document).on("click", "#pagination-preitems .page-link[data-page]", function (e) {
                      e.preventDefault();

                      loadPreSelected_Items($(this).data("page"));
                  });
</script>

<style>
    .highlight-row td {
        background-color: #fcf7d4 !important;
    }
</style>

