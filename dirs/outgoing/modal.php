<!-- Modal review item to select -->
<div class="modal" id="modal-select-item" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="text-muted">Find Item</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="row mb-2">
              <div class="mb-2 col-md-6">
                <label for="select_brand" class="form-label fw-semibold small mb-0">Select Brand</label>
                <select name="select_brand" id="select_brand" class="form-select" style="background: #fcf7d4;">
                </select>
              </div>
              <div class="mb-2 col-md-6">
                <label for="search_item" class="form-label fw-semibold small mb-0">Search</label>
                <input type="text" name="search_item" id="search_item" class="form-control" style="background: #fcf7d4;">
              </div>
          </div>
          <div class="mb-1 justify-content-end d-flex">
              <nav aria-label="Page navigation">
                  <ul class="pagination pagination-sm mb-0" id="pagination-finditems">
                      <li class="page-item" id="li-prev-finditems">
                          <a class="page-link shadow-none" href="#" id="btn-preview-finditems">
                              <i class="bi bi-chevron-left small"></i>
                              
                          </a>
                      </li>
                      <li class="page-item" id="li-next-finditems">
                          <a class="page-link shadow-none" href="#" id="btn-next-finditems">
                              <i class="bi bi-chevron-right small"></i>
                          </a>
                      </li>
                  </ul>
              </nav>
          </div>
          <div class="justify-content-end d-flex">
              <div id="page-info-finditems" class="mt-1 text-muted"></div>
          </div>
          <div class="table-responsive table-sm border shadow overflow-auto mt-3" style="height: 60vh;">
               <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                  <thead>
                      <tr style="background:#fcf7d4;" class="text-center table-primary">
                          <th class="text-center fw-bold" style="width:60px;">#</th>
                          <th class="text-center fw-bold" style="width:250px;">Category</th>
                          <th class="text-center fw-bold">Model</th>
                      </tr>
                  </thead>
                  <tbody id="load_models_content">
                          <!-- Display content here -->

                   


                  </tbody>
              </table>
          </div>
      </div>
    </div>
  </div>
</div>


<script>
  $("#select_brand").on("change", function () {
     loadItemModels();
  });

    var CurrentPage = 1; 
    var PageSize = 50; 
    var totalPages = 1; 
    var isPackageMode = false; 
    var selectedItems = [];

   function loadItemModels(page = 1) {
       CurrentPage = page;

       var itemDisplay = $("#load_models_content");

       itemDisplay.html(`
           <tr>
               <td colspan="3" class="text-center py-5">
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

       var Search = $("#search_item").val();
       var FilteBrand = $("#select_brand").val();

       $.post("dirs/outgoing/actions/get_models.php", {
           CurrentPage,
           PageSize,
           Search,
           FilteBrand
       }, function (data) {

           let response;

           try {
               response = JSON.parse(data);
           } catch (e) {

               itemDisplay.html(`
                   <tr>
                       <td colspan="3">
                           <div class="text-center py-5">
                               <div class="mb-3">
                                   <i class="bi bi-file-earmark text-secondary" style="font-size: 4rem;"></i>
                               </div>
                               <h5 class="fw-semibold mb-2">
                                   No Items Found
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

               // No records found
               if (!response.Data || response.Data.length === 0) {

                   itemDisplay.html(`
                       <tr>
                           <td colspan="3">
                               <div class="text-center py-5">
                                   <div class="mb-3">
                                       <i class="bi bi-file-earmark text-secondary" style="font-size: 4rem;"></i>
                                   </div>
                                   <h5 class="fw-semibold mb-2">
                                       No Items Found
                                   </h5>
                                   <p class="text-muted mb-4">
                                       Please use filter to show items.
                                   </p>
                               </div>
                           </td>
                       </tr>
                   `);

                   totalPages = 1;

                   ItemsPaginationUi();
                   ItemspageNumber();

                   return;
               }

               // Has records
               ItemsContent(response.Data);

               totalPages = parseInt(response.Data[0].TotalPages) || 1;

               ItemsPaginationUi();
               ItemspageNumber();

           } else {
               showEmptyStateItems("");
           }
       });
   }
           function ItemsContent(data) {
               const itemDisplay = $("#load_models_content");
               itemDisplay.empty();
               data.forEach(item => {
                   itemDisplay.append(`
                       <tr ondblclick="selectItem('${item.Model}')">
                           <td class="text-center font-weight-bold text-muted">
                               ${item.RowNum ?? '--'}
                           </td>
                           <td>
                              <div class="text-center">${item.ItemGroup ?? '--'}</div>
                           </td>
                            <td>
                              <div class="text-start">${item.Model ?? '--'}</div>
                            </td>
                       </tr>
                   `);
               });
           }

            /*Function for no record of beverages*/
            function showEmptyStateItems(message = "No records found") {
                $("#load_models_content").html(`
                     <tr>
                        <td colspan="3">
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
            function ItemsPaginationUi() {
                $("#page-info-finditems").text("Page " + CurrentPage + " of " + totalPages);
                
                if (CurrentPage <= 1) {
                    $("#li-prev-finditems").addClass("disabled");
                } else {
                    $("#li-prev-finditems").removeClass("disabled");
                }

                if (CurrentPage >= totalPages) {
                    $("#li-next-finditems").addClass("disabled");
                } else {
                    $("#li-next-finditems").removeClass("disabled");
                }
            }

            /* Function to build list of pagination numbers */
            function ItemspageNumber() {
                // 1. Remove previously generated page numbers
                $("#pagination-finditems li.page-number-finditems").remove();
                
                let prevLi = $("#li-prev-finditems");
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
                        <li class="page-item page-number-finditems ${activeClass}">
                            <a class="page-link shadow-none" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                    return $(li).insertAfter(ref);
                }

                if (start > 1) {
                    prevLi = insertPageVerified(1, prevLi);

                    if (start > 2) {
                        let ellipsis = `<li class="page-item page-number-finditems disabled"><span class="page-link">...</span></li>`;
                        prevLi = $(ellipsis).insertAfter(prevLi);
                    }
                }

                for (let i = start; i <= end; i++) {
                    prevLi = insertPageVerified(i, prevLi);
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) {
                        let ellipsis = `<li class="page-item page-number-finditems disabled"><span class="page-link">...</span></li>`;
                        prevLi = $(ellipsis).insertAfter(prevLi);
                    }
                    insertPageVerified(totalPages, prevLi);
                }
            }

            /* 3. Event Delegation for Number Clicks */
            $(document).on("click", "#pagination-finditems .page-number-finditems a", function (e) {
                e.preventDefault();
                let page = parseInt($(this).data("page"));
                if (page && page !== CurrentPage) {
                    loadItemModels(page);
                }
            });


            /*search-customer name*/
            $("#search_item").on("keydown", function(e) {
                if (e.key === "Enter") {
                    loadItemModels();
                }
            });



              /* Pagination + Fetch Blocked srvounts */
              $("#btn-preview-finditems").on("click", function(e) {
                  e.preventDefault();

                  if (CurrentPage > 1) {
                      loadItemModels(CurrentPage - 1);
                  }
              });

            /*Function load all important tags tickets*/
              $("#btn-next-finditems").on("click", function(e) {
                  e.preventDefault();

                  if (CurrentPage < totalPages) {
                      loadItemModels(CurrentPage + 1);
                  }
              });

              $(document).on("click", "#pagination-finditems .page-link[data-page]", function (e) {
                  e.preventDefault();

                  loadItemModels($(this).data("page"));
              });


</script>