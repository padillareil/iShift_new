$(document).ready(function(){
    loadOutgoing();
});



function loadOutgoing() {
    $.post("dirs/outgoing/components/main.php", {
    }, function (data){
        $("#loadoutgoingContent").html(data);
        loadStockRequest();
    });
}

/*Function to redirect form*/
function loadRequestForm() {
    Swal.fire({
        title: 'Please Wait',
        text: 'Loading request form...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    $.post(
        "dirs/outgoing/components/request_form.php",
        {},
        function (data) {
            $("#loadoutgoingContent").html(data);
            loadDestinationWhscodes();
            loadOriginWhscodes();
            loadPreSelected_Items();
            Swal.close();
        }
    ).fail(function () {
        Swal.fire({
            icon: 'error',
            title: 'Unable to Load',
            text: 'Failed to load the request form.',
            confirmButtonText: 'OK'
        });

    });
}


/*Whscode of branch Destination*/
function loadDestinationWhscodes() {
    var Branch = $("#desForm").val();
    $.post("dirs/outgoing/actions/get_whscode.php",{
        Branch:Branch
    },
        function (data) {
            const response = JSON.parse(data);
            if ($.trim(response.isSuccess) === "success") {
                const warehouses = response.Data;
                $("#desCodeForm").empty();
                warehouses.forEach(function (warehouse) {
                    $("#desCodeForm").append(
                        $("<option>", {value: warehouse.WhsCode,text: warehouse.WhsCode,title: warehouse.WhsName})
                    );
                });
            } else {
                console.log($.trim(response.Data));
            }
        }
    );
}




/*Whscode of branch Origin*/
function loadOriginWhscodes() {
    var Branch = $("#user-origin").val();
    $.post("dirs/outgoing/actions/get_whscode.php",{
        Branch: Branch
        },
        function (data) {
            const response = JSON.parse(data);
            if ($.trim(response.isSuccess) === "success") {
                const warehouses = response.Data;
                $("#originCodeForm").empty();
                warehouses.forEach(function (warehouse) {
                    $("#originCodeForm").append(
                        $("<option>", {value: warehouse.WhsCode,text: warehouse.WhsCode,title: warehouse.WhsName})
                    );
                });
            } else {
                console.log($.trim(response.Data));
            }
        }
    );
}


/*Function show modal to select Items*/
function mdladdItem() {
    $("#modal-select-item").modal('show');
    loadBrands();


}


/*Function show brands*/
function loadBrands() {
    loadItemModels();
    $.post("dirs/outgoing/actions/get_brands.php",
        {},
        function (data) {
            const response = JSON.parse(data);
            if ($.trim(response.isSuccess) === "success") {
                const brands = response.Data;
                $("#select_brand").empty();
                brands.forEach(function (item) {
                    $("#select_brand").append(
                        $("<option>", {value: item.Brand,text: item.Brand,title: item.Brand
                        })
                    );
                });
            } else {
                console.log($.trim(response.Data));
            }
        }
    );
}


/*Function to add modal pre selected items*/
function selectItem(Model) {
    var SR_Number = $("#srnForm").val();
    $.post("dirs/outgoing/actions/save_addpre_items.php", {
        SR_Number : SR_Number,
        Model   : Model
    }, function(data) {
        if(jQuery.trim(data) === "success") {
            loadPreSelected_Items(); 
            console.log('Item Saved');
        } else {
            console.log(data);
        }
    });
}


/* Function to add item quantity for pre-selected items */
function itemAdditional(Temp_id) {
    var SR_Number = $("#srnForm").val();
    var Action = 'Additional';

    $.post("dirs/outgoing/actions/update_modifyitem.php", {
        SR_Number: SR_Number,
        Temp_id: Temp_id,
        Action: Action
    }, function(data) {

        if ($.trim(data) === "success") {
            loadPreSelected_Items();
        } else {
            console.log(data);
        }

    });
}


/* Function to deduct item quantity for pre-selected items */
function itemDeductions(Temp_id) {
    var SR_Number = $("#srnForm").val();
    var Action = 'Deduction';

    $.post("dirs/outgoing/actions/update_modifyitem.php", {
        SR_Number: SR_Number,
        Temp_id: Temp_id,
        Action: Action
    }, function(data) {

        if ($.trim(data) === "success") {
            loadPreSelected_Items();
        } else {
            console.log(data);
        }

    });
}


/* Function to clear pre selected items */
function removePreselected() {
    var SR_Number = $("#srnForm").val();
    Swal.fire({
        title: 'Remove Items?',
        text: 'Are you sure you want to remove items?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("dirs/outgoing/actions/update_remove_items.php", {
                SR_Number: SR_Number
            }, function(data) {
                if ($.trim(data) === "success") {
                    loadPreSelected_Items();
                } else {
                    console.log(data);
                }

            });
        }
    });
}
