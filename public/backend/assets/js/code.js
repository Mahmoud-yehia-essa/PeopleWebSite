$(function () {
    $(document).on("click", "#delete", function (e) {
        e.preventDefault();
        var link = $(this).attr("href");

        Swal.fire({
            title: "هل أنت متأكد من الحذف ؟",
            text: "سوف يتم الحذف نهائيا",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            cancelButtonText: "الغاء",
            confirmButtonText: "نعم",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
                Swal.fire("تم الحذف!", "تم حذف البيانات بنجاح", "success");
            }
        });
    });
});

/// Confirm Order
$(function () {
    $(document).on("click", "#confirm", function (e) {
        e.preventDefault();
        var link = $(this).attr("href");

        Swal.fire({
            title: "Are you sure to Confirm?",
            text: "Once Confirm, You will not be able to pending again?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, Confirm!",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
                Swal.fire("Confirm!", "Confirm Change", "success");
            }
        });
    });
});

/// Processing Order
$(function () {
    $(document).on("click", "#processing", function (e) {
        e.preventDefault();
        var link = $(this).attr("href");

        Swal.fire({
            title: "Are you sure to Processing?",
            text: "Once Processing, You will not be able to pending again?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, Processing!",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
                Swal.fire("Processing!", "Processing Change", "success");
            }
        });
    });
});
/// Eend Processing Order

/// Deliverd Order
$(function () {
    $(document).on("click", "#delivered", function (e) {
        e.preventDefault();
        var link = $(this).attr("href");

        Swal.fire({
            title: "Are you sure to Delivered?",
            text: "Once Delivered, You will not be able to pending again?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, Delivered!",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
                Swal.fire("Delivered!", "Delivered Change", "success");
            }
        });
    });
});
/// End Deliverd Order

/// Return Approved Order
$(function () {
    $(document).on("click", "#approved", function (e) {
        e.preventDefault();
        var link = $(this).attr("href");

        Swal.fire({
            title: "Are you sure to Approved?",
            text: "Return Order Approved",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, Approved!",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
                Swal.fire("Approved!", "Approved Change", "success");
            }
        });
    });
});
