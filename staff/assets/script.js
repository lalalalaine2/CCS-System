const hamBurger = document.querySelector(".toggle-btn");

hamBurger.addEventListener("click", function () {
    document.querySelector("#sidebar").classList.toggle("expand");
});

$(document).ready(function () {
    $('#accounts-table').DataTable({ });
});

$(document).ready(function () {
    $('#applications-table').DataTable({ });
});

