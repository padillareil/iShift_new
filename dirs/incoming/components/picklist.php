<div class="container-fluid" id="picklist-maincontent">
	<div class="d-flex justify-content-between align-items-center mb-3">
	    <div>
	        <h6 class="mb-0 fw-bold">Picklist basket</h6>
	        <small class="text-muted">View all picklist setup.</small>
	    </div>
	    <div class="d-flex gap-2">
	        <button class="btn btn-primary shadow mr-2" type="button" onclick="loadPicklistCreated()">
	            Refresh
	        </button>
	    </div>
	</div>
	<div class="input-group border rounded-3 bg-white px-1 py-1 shadow-sm" style="max-width: 360px;">
	    <span class="input-group-text bg-transparent border-0 py-0 pe-2">
	        <i class="bi bi-search text-muted"></i>
	    </span>
	    <input type="search" class="form-control bg-transparent border-0 shadow-none py-0 small" id="search-picklist" placeholder="Search...">
	</div>
	<div class="mb-1 justify-content-end d-flex">
	    <nav aria-label="Page navigation">
	        <ul class="pagination pagination-sm mb-0" id="pagination-picklist">
	            <li class="page-item" id="li-prev-picklist">
	                <a class="page-link shadow-none" href="#" id="btn-preview-picklist">
	                    <i class="bi bi-chevron-left small"></i>
	                    
	                </a>
	            </li>
	            <li class="page-item" id="li-next-picklist">
	                <a class="page-link shadow-none" href="#" id="btn-next-picklist">
	                    <i class="bi bi-chevron-right small"></i>
	                </a>
	            </li>
	        </ul>
	    </nav>
	</div>
	<div class="justify-content-end d-flex">
	    <div id="page-info-picklist" class="mt-1 text-muted"></div>
	</div>
	<div class="table-responsive table-sm border shadow overflow-auto mt-3" style="height: 80vh;">
         <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
		    <thead>
		        <tr style="background:#fcf7d4;" class="text-center table-primary">
                    <th class="fw-bold text-center">#</th>
		            <th class="fw-bold text-center">Picklist No.</th>
		            <th class="fw-bold text-center">Item Quantity</th>
		            <th class="fw-bold text-center">Picklist Status</th>
		            <th class="fw-bold text-center">Picklist Date</th>
		            <th class="fw-bold text-center">Actions</th>
		        </tr>
		    </thead>
		    <tbody id="load_picklist_basket">
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


    function loadPicklistCreated(page = 1) {
            CurrentPage = page; 
            var incomingdisplay = $("#load_picklist_basket");
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
                            Please wait while we load the pick listed.
                        </p>
                    </td>
                </tr>
            `);
            var Search = $("#search-picklist").val();


            $.post("dirs/incoming/actions/get_pciklist_docs.php", {
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
                		ItemPicklistContent(response.Data,response.TotalPicklist);
                		totalPages = (
                		response.Data &&
                	response.Data.length > 0
                )
                ? parseInt(response.Data[0].TotalPages, 10)
                : 1;
                PicklistPaginationUi();
                PicklistpageNumber();
                } else {
                $("#incoming-picklist-quantity").text(0);
                showEmptyStatePicklist("");
                }
            });
        }

        /*Function to enable to select request for picklist*/
        function selectPicklist() {
            $("#btn-create_picklist").addClass('d-none');
            $("#load_picklist_basket tr").each(function () {
                var $row = $(this);
                var status = $row.attr("data-status");
                if (status === "NEW" || status === "PROCESSING") {
                    $row.find(".checkbox-item").prop("disabled", false);
                    $row.addClass("text-primary");

                    $("#btn-save_picklist").removeClass('d-none');
                }
            });
        }


       function PicklistStatusBadge(status) {
           status = (status || "").toUpperCase();

           var badgeClass = "secondary";
           var extraClass = "";
           var icon = "";

           switch (status) {
               case "OPEN":
                   badgeClass = "primary";
                   extraClass = "badge-new-glow";
                   break;

               case "CLOSED":
                   badgeClass = "success";
                   break;
           }

           return `
               <span class="badge badge-${badgeClass} px-2 py-1 ${extraClass}">
                   ${icon}${status || "--"}
               </span>
           `;
       }

       function ItemPicklistContent(data, totalPicklist) {

           const incomingdisplay = $("#load_picklist_basket");
           $("#incoming-picklist-quantity").text(totalPicklist ?? 0);

           if (!data || data.length === 0) {
               showEmptyStatePicklist("No Record Found.");
               return;
           }

           incomingdisplay.empty();

           data.forEach(item => {

               const status = (item.DocStatus || "").toUpperCase();

               incomingdisplay.append(`
                   <tr class="highlight-row" data-status="${status}">

                       <td class="text-center">
                           ${item.RowNum ?? '--'}
                       </td>

                       <td class="text-center">
                           ${item.PkList_Num ?? '--'}
                       </td>

                       <td class="text-center">
                           ${item.TotalItems ?? 0}
                       </td>
	               		<td class="text-center">
	               		    ${PicklistStatusBadge(item.DocStatus)}
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
                                    <a class="dropdown-item" href="#" onclick="encodePicklist('${item.PkList_Num}')" title="Encode Quantity">
                                        <i class="bi bi-card-list me-2"></i>Encode Quantity
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#" onclick="printPdf('${item.PkList_Num}')" title="Print Document">
                                        <i class="bi bi-printer me-2"></i>Print
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
        function showEmptyStatePicklist(message = "No records found") {
            $("#load_picklist_basket").html(`
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
        function PicklistPaginationUi() {
            $("#page-info-picklist").text("Page " + CurrentPage + " of " + totalPages);
            if (CurrentPage <= 1) {
                $("#li-prev-picklist").addClass("disabled");
            } else {
                $("#li-prev-picklist").removeClass("disabled");
            }

            if (CurrentPage >= totalPages) {
                $("#li-next-picklist").addClass("disabled");
            } else {
                $("#li-next-picklist").removeClass("disabled");
            }
        }

        /*Function to build list of pagination*/
        function PicklistpageNumber() {
            $("#pagination-picklist li.page-number-picklist").remove();
            let prevLi = $("#li-prev-picklist");
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
                    prevLi.after(`<li class="page-item page-number-picklist disabled"><span class="page-link">...</span></li>`);
                    prevLi = prevLi.next();
                }
            }
            for (let i = start; i <= end; i++) {
                insertPageverififed(i, prevLi);
                prevLi = prevLi.next();
            }
            if (end < totalPages) {
                if (end < totalPages - 1) {
                    prevLi.after(`<li class="page-item page-number-picklist disabled"><span class="page-link">...</span></li>`);
                    prevLi = prevLi.next();
                }
                insertPageverififed(totalPages, prevLi);
            }
            function insertPageverififed(i, ref) {
                let activeClass = (i === CurrentPage) ? "active" : "";

                let li = `
                    <li class="page-item page-number-picklist ${activeClass}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;

                $(li).insertAfter(ref);
            }
        }


        /*search-customer name*/
        $("#search-picklist").on("keydown", function(e) {
            if (e.key === "Enter") {
                loadPicklistCreated();
            }
        });



          /* Pagination + Fetch Blocked srvounts */
          $("#btn-preview-picklist").on("click", function(e) {
              e.preventDefault();

              if (CurrentPage > 1) {
                  loadPicklistCreated(CurrentPage - 1);
              }
          });

        /*Function load all important tags tickets*/
          $("#btn-next-picklist").on("click", function(e) {
              e.preventDefault();

              if (CurrentPage < totalPages) {
                  loadPicklistCreated(CurrentPage + 1);
              }
          });

          $(document).on("click", "#pagination-picklist .page-link[data-page]", function (e) {
              e.preventDefault();

              loadPicklistCreated($(this).data("page"));
          });



          /*Function to set picklist items*/
          function encodePicklist(PkList_Num) {
              $.post("dirs/incoming/components/picklist_form.php", {
              }, function (data){
                $("#picklistNumber_encode").val(PkList_Num);
                  $("#main-content").html(data);
                  // loadRequest_Items();
              });
          }

          /* Function to display all request items ready for picklist */
          function loadRequest_Items(Rownum) {
              $.post("dirs/incoming/actions/get_itemrequest.php", {
                  Rownum: Rownum
              }, function (data) {
                  var response = typeof data === "string" ? JSON.parse(data) : data;

                  if (jQuery.trim(response.isSuccess) === "success") {
                      const itemDisplay = $("#load_picklist_items");
                      itemDisplay.empty(); // Clear previous rows if needed

                      // Iterate through response data array
                      const items = response.Data || response.data || response;

                      items.forEach(item => {
                          itemDisplay.append(`
                              <tr>
                                  <td class="text-center font-weight-bold text-muted item_number">
                                      ${item.Numbering ?? '--'}
                                  <input type="number" class="d-none item_mothernumber" value="${item.DocEntry ?? 0}"> 
                                  </td>
                                  <td>
                                      <div class="text-center item_category">
                                          ${item.Category ?? '--'}
                                      </div>
                                  </td>
                                  <td>
                                      <div class="text-center item-brand item_brand">
                                          ${item.Brand ?? '--'}
                                      </div>
                                  </td>
                                  <td>
                                      <div class="text-center item-model">
                                          ${item.Model ?? '--'}
                                      </div>
                                  </td>
                                  <td>
                                      <div class="text-center item_quantity">
                                          ${item.RequestQuantity ?? 0}
                                      </div>
                                  </td>
                                  <td style="background-color: #fcf7d4;">
                                      <div class="text-center">
                                          <input type="number" class="form-control form-control-sm text-center item_issued_qty" min="0" step="1" value="0">
                                      </div>
                                  </td>
                              </tr>
                          `);
                      });

                  } else {
                      console.log(jQuery.trim(response.Data));
                  }
              });
          }


</script>

<!-- <script>
    
    function printPdf(PkList_Num) {
        const form = $("<form>", {
            method: "POST",
            action: "pdf/requests.php",
            target: "_blank"
        });

        form.append($("<input>", {
            type: "hidden",
            name: "PkList_Num",
            value: PkList_Num
        }));

        $("body").append(form);
        form.submit();
        form.remove();
    }
</script> -->