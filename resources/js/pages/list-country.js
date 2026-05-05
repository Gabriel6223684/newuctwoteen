// resources/js/pages/list-country.js
$(document).ready(function() {
    $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "/pais/listingdata",
            "type": "POST"
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3, "orderable": false, "searchable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        },
        "drawCallback": function() {
            // Re-attach event listeners after DataTable redraw
        }
    });
});

function ShowModal(id) {
    $('#delete-id').val(id);
    $('#modal-delete').modal('show');
}

