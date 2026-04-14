"use strict";

$(document).ready(function () {
    const $form = $("#kt_sign_in_form");
    const $submitBtn = $("#kt_sign_in_submit");

    // Initialize FormValidation
    const fv = FormValidation.formValidation($form[0], {
        fields: {
            email: {
                validators: {
                    regexp: {
                        regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                        message: "The value is not a valid email address",
                    },
                    notEmpty: {
                        message: "Email address is required",
                    },
                },
            },
            password: {
                validators: {
                    notEmpty: {
                        message: "The password is required",
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
                rowSelector: ".fv-row",
                eleInvalidClass: "",
                eleValidClass: "",
            }),
        },
    });

    // Handle form submit
    $submitBtn.on("click", function (e) {
        e.preventDefault();

        fv.validate().then(function (status) {
            if (status === "Valid") {
                $submitBtn.attr("data-kt-indicator", "on").prop("disabled", true);

                const action = $form.attr("action");
                const redirectUrl = $form.data("kt-redirect-url");

                // Check if the form action is a valid URL
                let isUrl = false;
                try {
                    new URL(action);
                    isUrl = true;
                } catch (err) {
                    isUrl = false;
                }

                // If action is not a valid URL → just simulate login (demo mode)
                if (!isUrl) {
                    setTimeout(function () {
                        $submitBtn.removeAttr("data-kt-indicator").prop("disabled", false);
                        Swal.fire({
                            text: "You have successfully logged in!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: { confirmButton: "btn btn-primary" },
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                $form[0].reset();
                                if (redirectUrl) window.location.href = redirectUrl;
                            }
                        });
                    }, 2000);
                    return;
                }

                // Otherwise → submit via axios
                axios.post(action, new FormData($form[0]))
                    .then(function (response) {
                        console.log(response)
                        if (response && response.status === 200) {
                            $form[0].reset();
                            Swal.fire({
                                text: "You have successfully logged in!",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "See Dashboard",
                                customClass: { confirmButton: "btn btn-primary" },
                            }).then(function () {
                                if (redirectUrl) window.location.href = redirectUrl;
                            });
                        } else {
                            Swal.fire({
                                text: "Sorry, the email or password is incorrect, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: { confirmButton: "btn btn-primary" },
                            });
                        }
                    })
                    .catch(function () {
                        Swal.fire({
                            text: "Sorry, looks like there are some errors detected, please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: { confirmButton: "btn btn-primary" },
                        });
                    })
                    .finally(function () {
                        $submitBtn.removeAttr("data-kt-indicator").prop("disabled", false);
                    });
            } else {
                // Swal.fire({
                //     text: "Sorry, looks like there are some errors detected, please try again.",
                //     icon: "error",
                //     buttonsStyling: false,
                //     confirmButtonText: "Ok, got it!",
                //     customClass: { confirmButton: "btn btn-primary" },
                // });
            }
        });
    });
});
