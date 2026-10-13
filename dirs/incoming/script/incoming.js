$(document).ready(function () {
    loadIncoming();

});

/* Function load incoming page */
function loadIncoming() {
    $("#menu-incoming").find("a[name='menu']").removeClass("active");
    $("#menu-incoming").find("a[menucode='incoming']").addClass("active");
    $.post("dirs/incoming/components/incoming_list.php", {}, function (data) {
        $("#incoming-content").html(data);
    loadIncomingRequest();
    });
}


/* Function load Picklist page */
function loadPicklist() {
    $("#menu-incoming").find("a[name='menu']").removeClass("active");
    $("#menu-incoming").find("a[menucode='picklist']").addClass("active");
    $.post("dirs/incoming/components/picklist.php", {}, function (data) {
        $("#incoming-content").html(data);
        loadPicklistCreated();
    });
}


/* Function load assignment page */
function loadAssignment() {
    $("#menu-incoming").find("a[name='menu']").removeClass("active");
    $("#menu-incoming").find("a[menucode='assignment']").addClass("active");
    $.post("dirs/incoming/components/assignment.php", {}, function (data) {
        $("#incoming-content").html(data);
    });
}

/* Function load loading basket page */
function loadBasket() {
    $("#menu-incoming").find("a[name='menu']").removeClass("active");
    $("#menu-incoming").find("a[menucode='loadingbasket']").addClass("active");
    $.post("dirs/incoming/components/loadingbasket.php", {}, function (data) {
        $("#incoming-content").html(data);
    });
}

/* Function load dispatching page */
function loadDispatching() {
    $("#menu-incoming").find("a[name='menu']").removeClass("active");
    $("#menu-incoming").find("a[menucode='delivery']").addClass("active");
    $.post("dirs/incoming/components/dispatching.php", {}, function (data) {
        $("#incoming-content").html(data);
    });
}




/*Function to validate encoding quantity*/
$(document).on("input", ".item_issued_qty", function () {
    const $input = $(this);
    const $row = $input.closest("tr");
    const requestQty = parseInt(
        $row.find(".item_quantity").text().trim(),
        10
    ) || 0;
    let issuedQty = parseInt($input.val(), 10) || 0;
    const $error = $row.find(".issued_qty_error");

    // Negative → 0
    if (issuedQty < 0) {
        issuedQty = 0;
        $input.val(0);
    }

    // Exceeds requested quantity → limit to requested quantity
    if (issuedQty > requestQty) {
        issuedQty = requestQty;
        $input.val(requestQty);
    }

    $input.removeClass("is-invalid");
    $error.text("");
});
 /* Function load incoming page */
function returnHomeIncoming() {
    $.post("dirs/incoming/incoming_list.php", {}, function (data) {
        $("#main-content").html(data);
    });
}





