"use strict";

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

var KTUsersList = (function () {

    var datatable;
    var table = document.getElementById("kt_table_users");

    // ---------------------------------------------
    // 🔹 Single Row Delete Logic (kept)
    // ---------------------------------------------
    var handleDeleteRow = function () {
        table.querySelectorAll('[data-kt-users-table-filter="delete_row"]').forEach((btn) => {
            btn.addEventListener("click", function (event) {
                event.preventDefault();

                const row = event.target.closest("tr");
                const username = row.querySelectorAll("td")[1].innerText.trim();

                Swal.fire({
                    text: "Are you sure you want to Disable " + username + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary",
                    },
                }).then(function (result) {
                    if (result.value) {
                        axios.delete('/admin/users/' + row.dataset.id)
                            .then(function () {

                                Swal.fire({
                                    text: username + " has been disabled!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: { confirmButton: "btn fw-bold btn-primary" },
                                }).then(function (response) {
                                    location.reload();
                                });
                            })
                            .catch(function () {
                                Swal.fire({
                                    text: "Failed to disable " + username + ".",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: { confirmButton: "btn fw-bold btn-primary" },
                                });
                            });
                    }
                });
            });
        });
    };
    var handleUpdateRow = function () {
        table.querySelectorAll('[data-kt-users-table-filter="update_row"]').forEach((btn) => {
            btn.addEventListener("click", function (event) {
                event.preventDefault();

                const row = event.target.closest("tr");
                const username = row.querySelectorAll("td")[1].innerText.trim();

                Swal.fire({
                    text: "Are you sure you want to Enable? " + username + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary",
                    },
                }).then(function (result) {
                    if (result.value) {
                        axios.post('/admin/users/enable/' + row.dataset.id)
                            .then(function () {

                                Swal.fire({
                                    text: username + " has been enabled!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: { confirmButton: "btn fw-bold btn-primary" },
                                }).then(function () {
                                    //refresh the page
                                    location.reload();


                                });
                            })
                            .catch(function () {
                                Swal.fire({
                                    text: "Failed to enable " + username + ".",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: { confirmButton: "btn fw-bold btn-primary" },
                                });
                            });
                    }
                });
            });
        });
    };

    // ---------------------------------------------
    // 🔹 Init Function
    // ---------------------------------------------
    return {
        init: function () {
            if (!table) return;

            // Initialize DataTable
            datatable = $(table).DataTable({
                info: true,
                order: [],
                lengthChange: true,
                columnDefs: [
                    { orderable: false, targets: 0 },
                    { orderable: false, targets: 4 },
                    
                ],
            }).on("draw", function () {
                handleDeleteRow();
                handleUpdateRow();
            });

            // Initial handlers
            handleDeleteRow();
            handleUpdateRow();

            // Search input
            const searchInput = document.querySelector('[data-kt-user-table-filter="search"]');
            if (searchInput) {
                searchInput.addEventListener("keyup", function (event) {
                    datatable.search(event.target.value).draw();
                });
            }

            // Reset filter
            const resetBtn = document.querySelector('[data-kt-user-table-filter="reset"]');
            if (resetBtn) {
                resetBtn.addEventListener("click", function () {
                    const form = document.querySelector('[data-kt-user-table-filter="form"]');
                    if (form) {
                        form.querySelectorAll("select").forEach((select) => {
                            $(select).val("").trigger("change");
                        });
                    }
                    datatable.search("").draw();
                });
            }

            // Filter button
            const form = document.querySelector('[data-kt-user-table-filter="form"]');
            if (form) {
                const filterBtn = form.querySelector('[data-kt-user-table-filter="filter"]');
                const selects = form.querySelectorAll("select");
                
                if (filterBtn) {
                    filterBtn.addEventListener("click", function () {
                        let searchValue = "";
                        selects.forEach((select, i) => {
                            if (select.value && select.value !== "") {
                                if (i !== 0) searchValue += " ";
                                searchValue += select.value;
                            }
                        });
                        datatable.search(searchValue).draw();
                    });
                }
            }
        },
    };
})();

jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
    "priority-pre": function (data) {
        if (!data) return 999; // handle empty cells

        const priority = data.toString().toLowerCase().trim();

        switch (priority) {
            case "urgent": return 1;
            case "high": return 2;
            case "medium": return 3;
            case "low": return 4;
            default: return 5;
        }
    }
});


var KTIssuesList = (function () {

    var datatable;
    var table = document.getElementById("kt_table_issues");

  

    // ---------------------------------------------
    // 🔹 Init Function
    // ---------------------------------------------
    return {
        init: function () {
            if (!table) return;

            // Initialize DataTable
            datatable = $(table).DataTable({
                info: true,
                order: [],
                paging: true,
                responsive: true,
                
               
                lengthChange: true,
                columnDefs: [
                    { orderable: false, targets: -1 },
                ],
            }).on("draw", function () {
                KTMenu.init();
            });

            // // Initial handlers
            // handleDeleteRow();
            // handleUpdateRow();

            // Search input
            const searchInput = document.querySelector('[data-kt-issues-table-filter="search"]');
            if (searchInput) {
                searchInput.addEventListener("keyup", function (event) {
                    datatable.search(event.target.value).draw();
                });
            }

            // Reset filter
            const resetBtn = document.querySelector('[data-kt-issues-table-filter="reset"]');
            if (resetBtn) {
                resetBtn.addEventListener("click", function () {
                    const form = document.querySelector('[data-kt-issues-table-filter="form"]');
                    if (form) {
                        form.querySelectorAll("select").forEach((select) => {
                            $(select).val("").trigger("change");
                        });
                    }
                    datatable.search("").draw();
                });
            }

            // Filter button
            const form = document.querySelector('[data-kt-issues-table-filter="form"]');
            if (form) {
                const filterBtn = form.querySelector('[data-kt-issues-table-filter="filter"]');
                const selects = form.querySelectorAll("select");
              
                if (filterBtn) {
                    filterBtn.addEventListener("click", function () {
                        let searchValue = "";
                        selects.forEach((select, i) => {
                            if (select.value && select.value !== "") {
                                if (i !== 0) searchValue += " ";
                                searchValue += select.value;
                            }
                        });
                        datatable.search(searchValue).draw();
                    });
                }
            }
        },
    };
})();
var KTIssuesListByUser = (function () {

    var datatable;
    var table = document.getElementById("kt_table_issues_by_user");

  

    // ---------------------------------------------
    // 🔹 Init Function
    // ---------------------------------------------
    return {
        init: function () {
            if (!table) return;

            // Initialize DataTable
            datatable = $(table).DataTable({
                info: true,
                order: [],
                paging: true,
                responsive: true,
                
               
                lengthChange: true,
                columnDefs: [
                    { orderable: false, targets: 0 },
                    { orderable: false, targets: 7},
                ],
            }).on("draw", function () {
                KTMenu.init();
            });

            // // Initial handlers
            // handleDeleteRow();
            // handleUpdateRow();

            // Search input
            const searchInput = document.querySelector('[data-kt-issues-by-user-table-filter="search"]');
            if (searchInput) {
                searchInput.addEventListener("keyup", function (event) {
                    datatable.search(event.target.value).draw();
                });
            }

            // Reset filter
            const resetBtn = document.querySelector('[data-kt-issues-by-user-table-filter="reset"]');
            if (resetBtn) {
                resetBtn.addEventListener("click", function () {
                    const form = document.querySelector('[data-kt-issues-by-user-table-filter="form"]');
                    if (form) {
                        form.querySelectorAll("select").forEach((select) => {
                            $(select).val("").trigger("change");
                        });
                    }
                    datatable.search("").draw();
                });
            }

            // Filter button
            const form = document.querySelector('[data-kt-issues-by-user-table-filter="form"]');
            if (form) {
                const filterBtn = form.querySelector('[data-kt-issues-by-user-table-filter="filter"]');
                const selects = form.querySelectorAll("select");
              
                if (filterBtn) {
                    filterBtn.addEventListener("click", function () {
                        let searchValue = "";
                        selects.forEach((select, i) => {
                            if (select.value && select.value !== "") {
                                if (i !== 0) searchValue += " ";
                                searchValue += select.value;
                            }
                        });
                        datatable.search(searchValue).draw();
                    });
                }
            }
        },
    };
})();

// Run when DOM is ready
KTUtil.onDOMContentLoaded(function () {
    KTIssuesList.init();
    KTUsersList.init();
    KTIssuesListByUser.init();
});





