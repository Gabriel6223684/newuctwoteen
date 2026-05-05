// resources/js/pages/user.js
$(document).ready(function() {
    const id = $('input[name="id"]').val();
    
    $('#formUser').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = id ? '/api/usuario/update' : '/api/usuario/insert';
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.msg,
                        timer: 1500
                    }).then(() => {
                        if (id) {
                            history.back();
                        } else {
                            $('#formUser')[0].reset();
                            $('input[name="id"]').val('');
                        }
                    });
                } else {
                    Swal.fire('Erro', response.msg, 'error');
                }
            },
            error: function() {
                Swal.fire('Erro', 'Erro ao salvar', 'error');
            }
        });
    });
});
