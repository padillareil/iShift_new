<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-1 pb-2 border-bottom">
        <div>
            <h5 class="mb-0 fw-bold">Monitor Transfer</h5>
            <small class="text-muted">Warehouse & Logistics - Tracking</small>
        </div>
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-end gap-2 mt-1 pt-2">
            <button class="btn btn-primary shadow" type="reset" title="Clear" onclick="loadMonitoring()">
                Refresh
            </button>
        </div>
        <div class="row g-3 d-flex justify-content-end">
            <!-- SRN Input -->
            <div class="col-md-4 col-lg-2">
                <label for="filter-request" class="form-label fw-semibold small">Search</label>
                <input type="text" class="form-control" id="filter-request">
                <div class="invalid-feedback d-none" id="error-message">Invalid SRN.</div> <!-- Error message if the SRN doesn't exist -->
            </div>

            <!-- Branch Select -->
           <!--  <div class="col-md-4 col-lg-2">
                <label for="filter-branch" class="form-label fw-semibold small">Branch</label>
                <select class="form-select" id="filter-branch" name="filter-branch">
                    <option selected value="">ALL</option>
                </select>
            </div> -->
            <!-- Status Select -->
            <!-- <div class="col-md-4 col-lg-2">
                <label for="filter-status" class="form-label fw-semibold small">Transfer Status</label>
                <select class="form-select" id="filter-status" name="filter-status">
                    <option selected value="">All</option>
                    <option value="PROCESSING">Processing</option>
                    <option value="IN TRANSIT">In-Transit</option>
                    <option value="RECIEVED">Received</option>
                    <option value="PARTIAL">Partial</option>
                    <option value="">Terminated</option>
                </select>
            </div>

            <div class="col-md-4 col-lg-2">
                <label for="filter-datefrom" class="form-label fw-semibold small">Date From</label>
                <input type="date" class="form-control" id="filter-datefrom">
            </div>

            <div class="col-md-4 col-lg-2">
                <label for="filter-dateto" class="form-label fw-semibold small">Date To</label>
                <input type="date" class="form-control" id="filter-dateto">
            </div> -->
        </div>
    </div>
</div>

<div class="mb-1 justify-content-end d-flex">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0" id="pagination-stocks">
            <li class="page-item" id="li-prev-stocks">
                <a class="page-link shadow-none" href="#" id="btn-preview-stocks">
                    <i class="bi bi-chevron-left small"></i>
                    
                </a>
            </li>
            <li class="page-item" id="li-next-stocks">
                <a class="page-link shadow-none" href="#" id="btn-next-stocks">
                    <i class="bi bi-chevron-right small"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>
<div class="justify-content-end d-flex">
    <div id="page-info-stocks" class="mt-1 text-muted"></div>
</div>
<div class="table-responsive table-sm border shadow overflow-auto mt-3" style="height: 60vh;">
     <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
        <thead>
            <tr class="text-center table-primary">
                <th class="text-center fw-bold" style="width:60px;">#</th>
                <th class="fw-bold" style="width:160px;">SRN</th>
                <th class="fw-bold">Origin <i class="bi bi-arrow-right mx-2"></i> Destination</th>
                <th class="text-center fw-bold" style="width:160px;">Purpose</th>
                <th class="text-center fw-bold" style="width:160px;">Status</th>
                <th class="text-center" style="width:160px;">Date</th>
                <th class="text-center fw-bold" style="width:160px;">Requested By</th>
            </tr>
        </thead>
        <tbody id="load_stockrequests">
                <!-- Display content here -->
        </tbody>
    </table>
</div>


<script>
    var CurrentPage = 1;
    var PageSize = 50;
    var totalPages = 1;
    var isPackageMode = false;
    var selectedItems = [];

   function loadStocksList(page = 1) {
       CurrentPage = page; 
       var stocksMovementList = $("#load_stockrequests");
       stocksMovementList.html(`
           <tr>
               <td colspan="7">
                   <div class="text-center py-5">
                       <div class="mb-3">
                          <div class="spinner-border text-primary" role="status">
                              <span class="sr-only">Loading...</span>
                          </div>
                      </div>
                      <h6 class="fw-semibold mb-1">Loading...</h6>
                       <p class="text-muted mb-4">
                           Please wait while we load the stock requests.
                       </p>
                   </div>
               </td>
           </tr>
       `);
       var Search = $("#filter-request").val();

       $.post("dirs/monitoring/actions/get_stocksmonitoring.php", {
           CurrentPage,
           PageSize,
           Search
        }, function (data) {
            let response;

            try {
                response = JSON.parse(data);
            } catch (e) {
                stocksMovementList.html(`
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-arrow-left-right text-secondary" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">
                                    No Record Found
                                </h5>
                                <p class="text-muted mb-4">
                                    There are currently no activity available.
                                </p>
                            </div>
                        </td>
                    </tr>
                `);
                return;
            }
            if ($.trim(response.isSuccess) === "success") {
                StocksContent(response.Data);
                totalPages = (response.Data && response.Data.length > 0)
                    ? parseInt(response.Data[0].TotalPages)
                    : 1;

                    StocksPaginationUi();
                    StockspageNumber();
            } else {
                showEmptyStateStocks("");
            }
        });
    }


    function StocksContent(data) {
        const stocksMovementList = $("#load_stockrequests");
        if (!data || data.length === 0) {
            showEmptyStateStocks("No Record Found.");
            return;
        }
        stocksMovementList.empty();
        data.forEach(item => {
            stocksMovementList.append(`
                <tr ondblclick="reviewTransRecord()">
                    <td class="text-center">
                        ${item.RowNum ?? '--'}
                    </td>
                     <td>
                       <div class="text-left">
                           ${item.SR_Number ?? '--'}
                       </div>
                     </td>
                     <td class="text-center">
                         ${item.DocTransfer ?? '--'}
                     </td>
                    <td class="text-center">
                        ${item.PurposeRequest ?? '--'}
                    </td>

                    <td class="text-center">
                        ${item.RequestStatus ?? '--'}
                    </td>
                     <td class="text-center">
                         ${item.DateNtime ?? '--'}
                     </td>
                    <td class="text-center">
                        ${item.Fullname ?? '--'}
                    </td>
                </tr>
            `);
        });

    }

    /*Function for no record of beverages*/
    function showEmptyStateStocks(message = "No records found") {
        $("#load_stockrequests").html(`
            <tr>
                <td colspan="7">
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-arrow-left-right text-secondary" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="fw-semibold mb-2">
                            No Record Found
                        </h5>
                        <p class="text-muted mb-4">
                            There are currently no activity available.
                        </p>
                    </div>
                </td>
            </tr>
        `);
    }


    /*Function to count page number page 1 of and so on*/
    function StocksPaginationUi() {
        $("#page-info-stocks").text("Page " + CurrentPage + " of " + totalPages);
        if (CurrentPage <= 1) {
            $("#li-prev-stocks").addClass("disabled");
        } else {
            $("#li-prev-stocks").removeClass("disabled");
        }

        if (CurrentPage >= totalPages) {
            $("#li-next-stocks").addClass("disabled");
        } else {
            $("#li-next-stocks").removeClass("disabled");
        }
    }

    /*Function to build list of pagination*/
    function StockspageNumber() {
        $("#pagination-stocks li.page-number-stocks").remove();
        let prevLi = $("#li-prev-stocks");
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
                prevLi.after(`<li class="page-item page-number-stocks disabled"><span class="page-link">...</span></li>`);
                prevLi = prevLi.next();
            }
        }
        for (let i = start; i <= end; i++) {
            insertPageverififed(i, prevLi);
            prevLi = prevLi.next();
        }
        if (end < totalPages) {
            if (end < totalPages - 1) {
                prevLi.after(`<li class="page-item page-number-stocks disabled"><span class="page-link">...</span></li>`);
                prevLi = prevLi.next();
            }
            insertPageverififed(totalPages, prevLi);
        }
        function insertPageverififed(i, ref) {
            let activeClass = (i === CurrentPage) ? "active" : "";

            let li = `
                <li class="page-item page-number-stocks ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;

            $(li).insertAfter(ref);
        }
    }


    /*search-customer name*/
    $("#filter-request").on("keydown", function(e) {
        if (e.key === "Enter") {
            loadStocksList();
        }
    });

    $("#filter-branch").on("change", function () {
        loadStocksList();
    });

    /* Pagination + Fetch Blocked srvounts */
    $("#btn-preview-stocks").on("click", function(e) {
        e.preventDefault();

        if (CurrentPage > 1) {
            loadStocksList(CurrentPage - 1);
        }
    });

    /*Function load all important tags tickets*/
    $("#btn-next-stocks").on("click", function(e) {
        e.preventDefault();
            if (CurrentPage < totalPages) {
                loadStocksList(CurrentPage + 1);
        }
    });

    $(document).on("click", "#pagination-stocks .page-link[data-page]", function (e) {
        e.preventDefault();

        loadStocksList($(this).data("page"));
    });
</script>



