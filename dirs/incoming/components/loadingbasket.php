<div class="container-fluid" id="picklist-maincontent">
	<div class="d-flex justify-content-between align-items-center mb-3">
	    <div>
	        <h6 class="mb-0 fw-bold">Loading basket</h6>
	        <small class="text-muted">View all loading basket setup.</small>
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
