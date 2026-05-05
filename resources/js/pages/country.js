// resources/js/pages/country.js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form');
    const actionInput = document.getElementById('action');
    const idInput = document.getElementById('id');
    const insertBtn = document.getElementById('insert');

    const showModal = (id) => {
        document.getElementById('delete-id').value = id;
        $('#modal-delete').modal('show');
    };

    const saveData = () => {
        const formData = new FormData(form);
        const action = actionInput.value;
        const url = action === 'c' ? '/pais/insert' : '/pais/update';
        fetch(url, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            if (data.status) {
                Swal.fire({
                    title: 'Sucesso!',
                    text: data.msg,
                    icon: 'success'
                }).then(() => {
                    window.location.href = '/pais/lista';
                });
            } else {
                Swal.fire({
                    title: 'Erro!',
                    text: data.msg,
                    icon: 'error'
                });
            }
        });
    };

    insertBtn.addEventListener('click', saveData);

    $('#confirm-delete').on('click', function() {
        const id = document.getElementById('delete-id').value;
        const formData = new FormData();
        formData.append('id', id);

        fetch('/pais/delete', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            if (data.status) {
                $('#modal-delete').modal('hide');
                Swal.fire({
                    title: 'Sucesso!',
                    text: data.msg,
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Erro!',
                    text: data.msg,
                    icon: 'error'
                });
            }
        });
    });

    // Input masks
    Inputmask({ 
        mask: "999.999.999-99", 
        placeholder: "*" 
    }).mask('#numeroDocumento');
});

