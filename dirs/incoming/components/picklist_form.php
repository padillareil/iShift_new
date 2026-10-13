<div class="container-fluid">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                    <div class="row align-items-start">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <label for="picklistNumber_encode" class="text-dark-emphasis mb-0 mr-2">
                                    <small>Picklist No.:</small>
                                </label>
                                <input type="text" name="picklistNumber_encode" id="picklistNumber_encode" class="form-control form-control-sm" readonly>
                            </div>
                        </div>

                        <!-- Right side -->
                        <div class="col-md-4 ml-auto">
                            <div class="d-flex align-items-center mb-2">
                                <label for="collectors_name" class="text-dark-emphasis mb-0 mr-2">
                                    <small>Collected by:</small>
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
                                       <th class="fw-bold text-center" style="width: 150px; background-color: #fcf7d4;">Picked Quantity</th>
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
                          <button type="button" class="btn btn-light border shadow-sm" onclick="returnPicklist()">
                              Cancel
                          </button>
                      </div>
                  </div>
            </div>
        </div>
    </section>
</div>



<style>
    .highlight-row td {
        background-color: #fcf7d4 !important;
    }
</style>

<script>
    /* Function load Picklist page */
    function returnPicklist() {
        $("#menu-incoming").find("a[name='menu']").removeClass("active");
        $("#menu-incoming").find("a[menucode='picklist']").addClass("active");
        $.post("dirs/incoming/incoming.php", {}, function (data) {
            $("#main-content").html(data);
        });
    }
</script>