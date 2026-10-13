$(document).ready(function(){
    loadMonitoring();
});

function loadMonitoring() {
    $.post("dirs/monitoring/components/main.php", {
    }, function (data){
        $("#load_monitoringKontent").html(data);
        loadIAPBranches();
        loadStocksList();
    });
}


/*IAP All Branch list*/
function loadIAPBranches() {
  $.post("dirs/monitoring/actions/get_branchlist.php", {}, function (data) {
    const response = JSON.parse(data);
    if ($.trim(response.isSuccess) === "success") {
      const branches = response.Data;
      $("#filter-branch").html(response.Data.Branch);
      branches.forEach((branch) => {
        $("#filter-branch").append(
          $("<option>", {
            value: branch.Branch,
            text: branch.Branch,
          }),
        );
      });
    } else {
      console.log($.trim(response.Data));
    }
  });
}


/*Function to show record*/
function reviewTransRecord() {
  $("#modal-review-record").modal('show');
}