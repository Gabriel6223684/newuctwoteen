// resources/js/pages/list-user.js
$(document).ready(function() {
    const table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/usuario/listingdata',
            type: 'POST'
        },
        columns: [
            { data: 0, name: 'id' },
            { data: 1, name: 'name' },
            { data: 2, name: 'email' },
            { data: 3, name: 'active', orderable: false },
            { data: 4, name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']]
    });
    
    function ShowModal(id) {
        Swal.fire({
            title: 'Confirma exclusão?',
            text: 'Esta ação não pode ser desfeita!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/usuario/delete',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Excluído!', response.msg, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Erro', response.msg, 'error');
                        }
                    }
                });
            }
        });
    }
    
    window.ShowModal = ShowModal;
});
