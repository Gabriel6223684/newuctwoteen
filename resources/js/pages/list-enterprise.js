import DataTables from '../components/data-tables.js';

const table = DataTables.SetId('dataTable')
    .setRequestVariables([])
    .post('/enterprise/listingdata');

function ShowModal(id) {
    // Modal padrão do projeto
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

        const res = await fetch('/enterprise/delete', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        });

        const json = await res.json().catch(() => ({}));

        if (json?.status) {
            Swal.fire('Excluída!', json?.msg || 'Registro excluído com sucesso.', 'success');
            table.ajax.reload();
        } else {
            Swal.fire('Erro', json?.msg || 'Falha ao excluir.', 'error');
        }
    });
}

window.ShowModal = ShowModal;

