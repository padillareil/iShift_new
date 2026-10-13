<!-- Modal review transfer records -->
<div class="modal fade" id="modal-review-record" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="text-muted">Stock Transfer Summary</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card card-body shadow-sm">
          <div class="mb-2 col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div class="d-flex gap-2 align-items-center small" style="font-size: 0.75rem;">
                <span title="In Transit"><i class="bi bi-circle-fill text-primary"></i> Stock Out</span>
                <span title="Fully Received"><i class="bi bi-circle-fill text-success"></i> Stock In</span>
                <span title="Partial Receipt"><i class="bi bi-circle-fill text-orange"></i> Partial</span>
                <span title="Discrepancy"><i class="bi bi-circle-fill text-danger"></i> Discrepancy</span>
                <span title="Over"><i class="bi bi-circle-fill" style="color: #6f42c1;"></i> Overage</span>
              </div>
              
              
            </div>
              <label for="srn_number" class="form-label fw-semibold small mb-0">SRN</label>

            <input type="text" name="srn_number" id="srn_number" class="form-control bg-white" readonly>
          </div>
          <div class="table-responsive table-sm border shadow overflow-auto mt-3" style="height: 60vh;">
               <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                  <thead>
                      <tr class="text-center">
                          <th class="text-center fw-bold" style="width:60px;">#</th>
                          <th class="text-center" style="width:160px;">Route [From &rarr; To]</th>
                          <th class="text-center fw-bold">Itemcode</th>
                          <th class="text-center fw-bold">Brand/Model</th>
                          <th class="text-center fw-bold">Category</th>
                          <th class="text-center fw-bold" style="width:100px;">Quantity</th>
                          <th class="text-center" style="width:100px;">Item Status</th>
                          <th class="text-center" style="width:100px;">Transfer Out</th>
                          <th class="text-center" style="width:100px;">Transfer In</th>
                      </tr>
                  </thead>
                  <tbody id="load_transferrecord">
                          <!-- Display content here -->

                    <tr>
                        <td colspan="9">
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


                  </tbody>
              </table>
          </div>
        </div>


      </div>
    </div>
  </div>
</div>

