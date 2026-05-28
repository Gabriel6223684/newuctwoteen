import DataTables from '../Components/data-tables.js';

const table = DataTables.SetId('dataTable')
    .setRequestVariables([])
    .post('/fornecedor/listingdata');

// Sync do campo de busca custom com o DataTables
$(document).ready(() => {
    const quick = document.getElementById('quickSearch');
    if (!quick) return;

    quick.addEventListener('input', () => {
        table.search(quick.value).draw();
    });
});

function ShowModal(id) {
    $('#delete-id').val(id);
    // Se não existir modal pronto no HTML, fallback: confirma e chama delete via fetch.
    if ($('#modal-delete').length) {
        $('#modal-delete').modal('show');
        return;
    }

    Swal.fire({
        title: 'Confirma exclusão?',
        text: 'Esta ação não pode ser desfeita!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        const fd = new FormData();
        fd.append('id', id);

        const res = await fetch('/fornecedor/delete', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        });

        const json = await res.json().catch(() => ({}));

        if (json?.status) {
            Swal.fire('Excluído!', json?.msg || 'Registro excluído com sucesso.', 'success');
            table.ajax.reload();
        } else {
            Swal.fire('Erro', json?.msg || 'Falha ao excluir.', 'error');
        }
    });
}

window.ShowModal = ShowModal;

