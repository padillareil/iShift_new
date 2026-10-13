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
<input type="hidden" name="picklistNumber" id="picklistNumber" class="form-control form-control-sm" style="background: #F7F7F7;" readonly required value="<?php echo htmlspecialchars($Latestnum); ?>">
<input type="hidden" name="origin_branch d-none" id="origin_branch" value="<?php echo $user['Branch']?>">
<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-3">
	    <div>
	        <h6 class="mb-0 fw-bold">Incoming Requests</h6>
	        <small class="text-muted">View all incoming request.</small>
	    </div>
	    <div class="d-flex gap-2">
	        <button class="btn bg-orange shadow mr-1" type="button" onclick="selectPicklist()" id="btn-create_picklist">
	            <i class="bi bi-card-checklist"></i>
	            Picklist
	        </button>
	        <button class="btn btn-success d-none shadow mr-1" type="button" id="btn-save_picklist">
                Create Picklist
                <span class="badge badge-light ml-1" id="picklist-count">0</span>
            </button>
	       
	        <button class="btn btn-primary shadow mr-2" type="button" onclick="loadIncoming()">
	            Refresh
	        </button>
	    </div>
	</div>
	<div class="input-group border rounded-3 bg-white px-1 py-1 shadow-sm" style="max-width: 360px;">
	    <span class="input-group-text bg-transparent border-0 py-0 pe-2">
	        <i class="bi bi-search text-muted"></i>
	    </span>
	    <input type="search" class="form-control bg-transparent border-0 shadow-none py-0 small" id="search-incoming" placeholder="Search...">

	</div>
	<div class="mb-1 justify-content-end d-flex">
	    <nav aria-label="Page navigation">
	        <ul class="pagination pagination-sm mb-0" id="pagination-incoming">
	            <li class="page-item" id="li-prev-incoming">
	                <a class="page-link shadow-none" href="#" id="btn-preview-incoming">
	                    <i class="bi bi-chevron-left small"></i>
	                    
	                </a>
	            </li>
	            <li class="page-item" id="li-next-incoming">
	                <a class="page-link shadow-none" href="#" id="btn-next-incoming">
	                    <i class="bi bi-chevron-right small"></i>
	                </a>
	            </li>
	        </ul>
	    </nav>
	</div>
	<div class="justify-content-end d-flex">
	    <div id="page-info-incoming" class="mt-1 text-muted"></div>
	</div>

	<div class="table-responsive table-sm border shadow overflow-auto mt-3" style="height: 80vh;">
         <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
		    <thead>
		        <tr style="background:#fcf7d4;" class="text-center table-primary">
                    <th class="fw-bold text-center">#</th>
		        	<th class="fw-bold text-center" style="width: 100px;">Action</th>
		            <th class="fw-bold text-center">SRN</th>
		            <th class="fw-bold text-center">
		                Requesting Branch
		            </th>
		            <th class="fw-bold text-center">Request Status</th>
		            <th class="fw-bold text-center">Request Date</th>
		        </tr>
		    </thead>
		    <tbody id="load_incomingdata">
		       		<!-- Display content here -->
		    </tbody>
		</table>
	</div>
</div>



<script>
	   var CurrentPage = 1;
        var PageSize = 50;
        var totalPages = 1;
        var isPackageMode = false;
        var selectedItems = [];


        function loadIncomingRequest(page = 1) {
            CurrentPage = page; 
            var incomingdisplay = $("#load_incomingdata");
            incomingdisplay.html(`
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <h6 class="fw-semibold mb-1">Loading...</h6>
                        <p class="text-muted mb-0">
                            Please wait while we load the requests.
                        </p>
                    </td>
                </tr>
            `);
            var Search = $("#search-incoming").val();


            $.post("dirs/incoming/actions/get_incominglist.php", {
                CurrentPage,
                PageSize,
                Search
            }, function (data) {
                let response;

                try {
                    response = JSON.parse(data);
                } catch (e) {
                    incomingdisplay.html(`
                        <tr>
                     		<td colspan="6">
                     		    <div class="text-center py-5">
                     		        <div class="mb-3">
                     		            <i class="bi bi-clock-history text-secondary" style="font-size: 4rem;"></i>
                     		        </div>
                     		        <h5 class="fw-semibold mb-2">
                     		            No Record Found
                     		        </h5>
                     		        <p class="text-muted mb-4">
                     		            There are currently no record available.
                     		        </p>
                     		    </div>
                     		</td>
						</tr>
                    `);
                    return;
                }
                if ($.trim(response.isSuccess) === "success") {
                    ItemIncomingContent(response.Data);
                    totalPages = (response.Data && response.Data.length > 0)
                        ? parseInt(response.Data[0].TotalPages)
                        : 1;

                        IncomingPaginationUi();
                        IncomingpageNumber();
                } else {
                    showEmptyStateIncoming("");
                }
            });
        }

        /*Function to enable to select request for picklist*/
        function selectPicklist() {
            $("#btn-create_picklist").addClass("d-none");

            $("#load_incomingdata tr").each(function () {
                const $row = $(this);
                const status = $row.attr("data-status");

                if (status === "NEW" || status === "PROCESSING") {
                    $row.find(".checkbox-item").prop("disabled", false);
                    $row.addClass("text-primary");
                    $("#btn-save_picklist").removeClass("d-none");
                }
            });

            updatePicklistCount();
        }

        /* Update badge and enforce maximum of 5 */
        function updatePicklistCount() {
            const checked = $(".checkbox-item:checked").length;

            $("#picklist-count").text(checked + "");

            $(".checkbox-item:not(:checked)").prop("disabled", checked >= 5);
        }

        /* Update whenever checked or unchecked */
        $(document).on("change", ".checkbox-item", function () {
            updatePicklistCount();
        });


       function IncomingStatusBadge(status) {
           status = (status || "").toUpperCase();

           var badgeClass = "secondary";
           var extraClass = "";
           var icon = "";

           switch (status) {
               case "NEW":
                   badgeClass = "primary";
                   extraClass = "badge-new-glow";
                   icon = `<i class="bi bi-stars me-1"></i>`;
                   break;

               case "PROCESSING":
                   badgeClass = "warning";
                   break;

               case "IN TRANSIT":
                   badgeClass = "info";
                   break;

               case "PARTIAL":
                   badgeClass = "dark";
                   break;

               case "COMPLETE":
                   badgeClass = "success";
                   break;

               case "TERMINATED":
                   badgeClass = "danger";
                   break;
           }

           return `
               <span class="badge badge-${badgeClass} px-2 py-1 ${extraClass}">
                   ${icon}${status || "--"}
               </span>
           `;
       }

       function ItemIncomingContent(data) {
           const incomingdisplay = $("#load_incomingdata");
           if (!data || data.length === 0) {
               showEmptyStateIncoming("No Record Found.");
               return;
           }
           incomingdisplay.empty();
           data.forEach(item => {

               var status = (item.DocStatus || "").toUpperCase();

               incomingdisplay.append(`
                   <tr class="highlight-row" data-status="${status}">
                       <td class="text-center">
                           ${item.RowNum ?? '--'}
                       </td>

                       <td class="text-center">
                           <div class="d-flex justify-content-center align-items-center">
                               <input type="checkbox" class="m-0 checkbox-item" value="${item.RowNum}" disabled>
                           </div>
                       </td>

                       <td>
                           <div class="text-center">
                               ${item.StockRequest_Number ?? '--'}
                           </div>
                       </td>

                       <td class="text-center">
                           ${item.RequesstingBranch ?? '--'}
                       </td>

                       <td class="text-center">
                           ${IncomingStatusBadge(item.DocStatus)}
                       </td>

                       <td class="text-center">
                           ${item.DateRequest ?? '--'}
                       </td>

                   </tr>
               `);
           });
       }

        /*Function for no record of beverages*/
        function showEmptyStateIncoming(message = "No records found") {
            $("#load_incomingdata").html(`
                <tr>
                    <td colspan="6">
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-clock-history text-secondary"
                                   style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="fw-semibold mb-2">
                                No Record Found
                            </h5>
                            <p class="text-muted mb-4">
                                There are currently no record available.
                            </p>
                        </div>
                    </td>
				</tr>
            `);
        }


        /*Function to count page number page 1 of and so on*/
        function IncomingPaginationUi() {
            $("#page-info-incoming").text("Page " + CurrentPage + " of " + totalPages);
            if (CurrentPage <= 1) {
                $("#li-prev-incoming").addClass("disabled");
            } else {
                $("#li-prev-incoming").removeClass("disabled");
            }

            if (CurrentPage >= totalPages) {
                $("#li-next-incoming").addClass("disabled");
            } else {
                $("#li-next-incoming").removeClass("disabled");
            }
        }

        /*Function to build list of pagination*/
        function IncomingpageNumber() {
            $("#pagination-incoming li.page-number-incoming").remove();
            let prevLi = $("#li-prev-incoming");
            let maxVisible = 5;
            let start = Math.max(1, CurrentPage - 2);
            let end = Math.min(totalPages, start + maxVisible - 1);
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }
            if (start > 1) {
                insertPageverififed(1, prevLi);
                prevLi = prevLi.next();

                if (start > 2) {
                    prevLi.after(`<li class="page-item page-number-incoming disabled"><span class="page-link">...</span></li>`);
                    prevLi = prevLi.next();
                }
            }
            for (let i = start; i <= end; i++) {
                insertPageverififed(i, prevLi);
                prevLi = prevLi.next();
            }
            if (end < totalPages) {
                if (end < totalPages - 1) {
                    prevLi.after(`<li class="page-item page-number-incoming disabled"><span class="page-link">...</span></li>`);
                    prevLi = prevLi.next();
                }
                insertPageverififed(totalPages, prevLi);
            }
            function insertPageverififed(i, ref) {
                let activeClass = (i === CurrentPage) ? "active" : "";

                let li = `
                    <li class="page-item page-number-incoming ${activeClass}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;

                $(li).insertAfter(ref);
            }
        }


        /*search-customer name*/
        $("#search-incoming").on("keydown", function(e) {
            if (e.key === "Enter") {
                loadIncomingRequest();
            }
        });



          /* Pagination + Fetch Blocked srvounts */
          $("#btn-preview-incoming").on("click", function(e) {
              e.preventDefault();

              if (CurrentPage > 1) {
                  loadIncomingRequest(CurrentPage - 1);
              }
          });

        /*Function load all important tags tickets*/
          $("#btn-next-incoming").on("click", function(e) {
              e.preventDefault();

              if (CurrentPage < totalPages) {
                  loadIncomingRequest(CurrentPage + 1);
              }
          });

          $(document).on("click", "#pagination-incoming .page-link[data-page]", function (e) {
              e.preventDefault();

              loadIncomingRequest($(this).data("page"));
          });



    /*Function to submit selected to piclist*/
    $("#btn-save_picklist").on("click", function (e) {
        e.preventDefault();
        var selectedRows = [];
        $("#load_incomingdata .checkbox-item:checked").each(function () {
            var val = $(this).val();
            if (val && val !== "undefined") {
                selectedRows.push(val);
            }
        });
        if (selectedRows.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Stock Request Selected",
                text: "Please select at least one before submitting.",
                confirmButtonText: "OK"
            });
            return;
        }

        var rownumList = selectedRows.join(",");
        var PicklistNumber = $("#picklistNumber").val();
        var Branch = $("#origin_branch").val();

        Swal.fire({
            title: "Save Changes?",
            text: "Are you sure you want to finalize this?",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Yes, Save",
            cancelButtonText: "Cancel",
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.post("dirs/incoming/actions/save_picklistsetup.php", {
                Rownum: rownumList,
                PicklistNumber: PicklistNumber,
                Branch: Branch
            },
            function (data) {
                let response;
                try {
                    response = typeof data === "string" ? JSON.parse(data) : data;
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
                            loadIncoming();
                            loadPicklistCreated();/*Refersh the badge of Picklist items*/
                        }
                    });
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Save Failed",
                    text: response.message || "Unable to save picklist."
                });
            }).fail(function (xhr) {
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
    .badge-new-glow {
        position: relative;
        animation: newBadgeGlow 1.5s ease-in-out infinite alternate;
    }

    .badge-new-glow .bi-stars {
        display: inline-block;
        animation: newStarGlare 1.2s ease-in-out infinite;
    }

    @keyframes newBadgeGlow {
        0% {
            box-shadow: 0 0 2px rgba(0, 123, 255, 0.4);
        }

        100% {
            box-shadow:
                0 0 6px rgba(0, 123, 255, 0.8),
                0 0 12px rgba(0, 123, 255, 0.5);
        }
    }

    @keyframes newStarGlare {
        0% {
            opacity: 0.5;
            transform: scale(0.8) rotate(0deg);
            filter: brightness(1);
        }

        50% {
            opacity: 1;
            transform: scale(1.25) rotate(15deg);
            filter: brightness(2);
        }

        100% {
            opacity: 0.6;
            transform: scale(0.9) rotate(0deg);
            filter: brightness(1);
        }
    }
</style>