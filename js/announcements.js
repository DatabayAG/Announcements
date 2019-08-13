$(document).ready(function () {
    $('.announcements .modal, .announcements .modal form *[aria-label=Close]').on('click', function () {
        $(this).closest('.modal').modal("hide");
        return false;
    })
    $('.announcements .modal form').on('click', function () {
        return false;
    })
    $('.announcements .modal form .btn[type=submit]').on('click', function () {
        $(this).closest('form').submit();
        return false;
    })
})
