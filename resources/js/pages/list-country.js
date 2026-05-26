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
        // total de 4 colunas no backend (id, nome, codigo_iso, acoes)
        // mas no HTML existem 5 colunas (inclui Status). Para evitar erro do DataTables,
        // vamos manter 5 colunas e deixar Status vazio.
        "columns": [
            { "data": 0 }, // ID
            { "data": 1 }, // Nome
            { "data": 2 }, // Código ISO
            { "data": 3, "defaultContent": "" }, // Status (não enviado pelo backend)
            { "data": 4, "orderable": false, "searchable": false, "defaultContent": "" } // Ações
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

