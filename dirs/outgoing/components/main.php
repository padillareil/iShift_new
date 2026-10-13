<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-3">
	    <div>
	        <h6 class="mb-0 fw-bold">Stock Request - Outgoing</h6>
	        <small class="text-muted">View all stock request.</small>
	    </div>
	    <div class="d-flex gap-2">
	        <button class="btn btn-primary shadow mr-2" type="button" onclick="loadRequestForm()">
	            <i class="bi bi-plus"></i>
	            New
	        </button>
	    </div>
	</div>
	<div class="input-group border rounded-3 bg-white px-1 py-1 shadow-sm" style="max-width: 360px;">
	    <span class="input-group-text bg-transparent border-0 py-0 pe-2">
	        <i class="bi bi-search text-muted"></i>
	    </span>
	    <input type="search" class="form-control bg-transparent border-0 shadow-none py-0 small" id="search-iteminventory" placeholder="Search...">
	</div>
	<div class="mb-1 justify-content-end d-flex">
	    <nav aria-label="Page navigation">
	        <ul class="pagination pagination-sm mb-0" id="pagination-iteminventory">
	            <li class="page-item" id="li-prev-iteminventory">
	                <a class="page-link shadow-none" href="#" id="btn-preview-iteminventory">
	                    <i class="bi bi-chevron-left small"></i>
	                    
	                </a>
	            </li>
	            <li class="page-item" id="li-next-iteminventory">
	                <a class="page-link shadow-none" href="#" id="btn-next-iteminventory">
	                    <i class="bi bi-chevron-right small"></i>
	                </a>
	            </li>
	        </ul>
	    </nav>
	</div>
	<div class="justify-content-end d-flex">
	    <div id="page-info-iteminventory" class="mt-1 text-muted"></div>
	</div>
	<div class="table-responsive table-sm border shadow overflow-auto mt-3" style="height: 60vh;">
         <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
		    <thead>
		        <tr style="background:#fcf7d4;" class="text-center table-primary">
                    <th class="fw-bold text-center">#</th>
		            <th class="fw-bold text-center">SRN</th>
		            <th class="fw-bold text-center">
		                Origin <i class="bi bi-arrow-right mx-2"></i> Destination
		            </th>
		            <th class="fw-bold text-center">Requested by</th>
		            <th class="fw-bold text-center">Request Status</th>
		            <th class="fw-bold text-center">Request Date</th>
		            <th class="fw-bold text-center">Actions</th>
		        </tr>
		    </thead>
		    <tbody id="load_itemmasterdata">
		       		<!-- Display content here -->
		    </tbody>
		</table>
	</div>
</div>


<style>
    .highlight-row td {
        background-color: #fcf7d4 !important;
    }
</style>

<script>
        var CurrentPage = 1;
        var PageSize = 50;
        var totalPages = 1;
        var isPackageMode = false;
        var selectedItems = [];


        function loadStockRequest(page = 1) {
            CurrentPage = page; 
            var draftdisplay = $("#load_itemmasterdata");
            draftdisplay.html(`
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <h6 class="fw-semibold mb-1">Loading...</h6>
                        <p class="text-muted mb-0">
                            Please wait while we load the stock requests.
                        </p>
                    </td>
                </tr>
            `);
            var Search = $("#search-iteminventory").val();


            $.post("dirs/outgoing/actions/get_requestlist.php", {
                CurrentPage,
                PageSize,
                Search
            }, function (data) {
                let response;

                try {
                    response = JSON.parse(data);
                } catch (e) {
                    draftdisplay.html(`
                        <tr>
                     		<td colspan="7">
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
                    ItemMasterContent(response.Data);
                    totalPages = (response.Data && response.Data.length > 0)
                        ? parseInt(response.Data[0].TotalPages)
                        : 1;

                        ItemMasterPaginationUi();
                        ItemMasterpageNumber();
                } else {
                    showEmptyStateItemMaster("");
                }
            });
        }


        function getStatusBadge(status) {
            status = (status || "").toUpperCase();

            var badgeClass = "secondary";

            switch (status) {
                case "NEW":
                    badgeClass = "primary";
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

            return `<span class="badge badge-${badgeClass} px-2 py-1">
                        ${status || "--"}
                    </span>`;
        }

       function ItemMasterContent(data) {

           const draftdisplay = $("#load_itemmasterdata");

           if (!data || data.length === 0) {
               showEmptyStateItemMaster("No Record Found.");
               return;
           }

           draftdisplay.empty();

           data.forEach(item => {
               draftdisplay.append(`
                   <tr class="highlight-row">
                       <td class="text-center fw-semibold">
                           ${item.RowNum ?? '--'}
                       </td>
                      	<td>
                          <div class="fw-semibold">
                              ${item.StockRequest_Number ?? '--'}
                          </div>
						</td>
               			<td class="text-center">
               			    ${item.TransRoute ?? '--'}
               			</td>
                       <td class="text-center">
                           ${item.RequestBy ?? '--'}
                       </td>

                       <td class="text-center">
                          ${getStatusBadge(item.DocStatus)}
                       </td>
               			<td class="text-center">
               			    ${item.DateRequest ?? '--'}
               			</td>
               			<td class="text-center">
                            <div class="dropdown">
                              <button class="btn btn-sm shadow btn-light bi bi-three-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="openRequest("${item.DocEntry}")">
                                        <i class="bi bi-folder2-open me-2"></i>Open
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#" onclick="terminateRequest("${item.DocEntry}")">
                                        <i class="bi bi-x-circle me-2"></i>Terminate
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#" onclick="printRequest("${item.DocEntry}")">
                                        <i class="bi bi-printer me-2"></i>Print
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#" onclick="reviewCollab("${item.DocEntry}")">
                                        <i class="bi bi-people me-2"></i>Collaborator
                                    </a>
                                </li>
                              </ul>
                            </div>
               			</td>
                   </tr>
               `);
           });

       }

        /*Function for no record of beverages*/
        function showEmptyStateItemMaster(message = "No records found") {
            $("#load_itemmasterdata").html(`
                <tr>
                    <td colspan="7">
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
        function ItemMasterPaginationUi() {
            $("#page-info-iteminventory").text("Page " + CurrentPage + " of " + totalPages);
            if (CurrentPage <= 1) {
                $("#li-prev-iteminventory").addClass("disabled");
            } else {
                $("#li-prev-iteminventory").removeClass("disabled");
            }

            if (CurrentPage >= totalPages) {
                $("#li-next-iteminventory").addClass("disabled");
            } else {
                $("#li-next-iteminventory").removeClass("disabled");
            }
        }

        /*Function to build list of pagination*/
        function ItemMasterpageNumber() {
            $("#pagination-iteminventory li.page-number-iteminventory").remove();
            let prevLi = $("#li-prev-iteminventory");
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
                    prevLi.after(`<li class="page-item page-number-iteminventory disabled"><span class="page-link">...</span></li>`);
                    prevLi = prevLi.next();
                }
            }
            for (let i = start; i <= end; i++) {
                insertPageverififed(i, prevLi);
                prevLi = prevLi.next();
            }
            if (end < totalPages) {
                if (end < totalPages - 1) {
                    prevLi.after(`<li class="page-item page-number-iteminventory disabled"><span class="page-link">...</span></li>`);
                    prevLi = prevLi.next();
                }
                insertPageverififed(totalPages, prevLi);
            }
            function insertPageverififed(i, ref) {
                let activeClass = (i === CurrentPage) ? "active" : "";

                let li = `
                    <li class="page-item page-number-iteminventory ${activeClass}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;

                $(li).insertAfter(ref);
            }
        }


        /*search-customer name*/
        $("#search-iteminventory").on("keydown", function(e) {
            if (e.key === "Enter") {
                loadStockRequest();
            }
        });



          /* Pagination + Fetch Blocked srvounts */
          $("#btn-preview-iteminventory").on("click", function(e) {
              e.preventDefault();

              if (CurrentPage > 1) {
                  loadStockRequest(CurrentPage - 1);
              }
          });

        /*Function load all important tags tickets*/
          $("#btn-next-iteminventory").on("click", function(e) {
              e.preventDefault();

              if (CurrentPage < totalPages) {
                  loadStockRequest(CurrentPage + 1);
              }
          });

          $(document).on("click", "#pagination-iteminventory .page-link[data-page]", function (e) {
              e.preventDefault();

              loadStockRequest($(this).data("page"));
          });
</script>

