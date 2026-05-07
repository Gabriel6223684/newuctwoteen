import Requests from '../components/requests.js';
import Validate from '../components/validate.js';

const Action = document.getElementById('action');
const Id = document.getElementById('id');
const InsertBtn = document.getElementById('insert');

async function applyChanges() {
    $('button, input, checkbox').prop('disabled', true);

    const IsValid = Validate.SetForm('form').Validate();
    if (!IsValid) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Por favor, corrija os erros no formulário antes de salvar.',
            timer: 3000,
            timerProgressBar: true,
        });
        $('button, input, checkbox').prop('disabled', false);
        return;
    }

    const requests = new Requests();

    try {
        const response = (Action.value !== 'e')
            ? await requests.setForm('form').post('/enterprise/insert')
            : await requests.setForm('form').post('/enterprise/update');

        if (!response?.status) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: response?.msg || 'Ocorreu um erro ao salvar os dados da empresa.',
                timer: 3000,
                timerProgressBar: true,
            });
            $('button, input, checkbox').prop('disabled', false);
            return;
        }

        if (Action.value === 'e') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: response.msg || 'Dados da empresa alterados com sucesso.',
                timer: 3000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = '/enterprise/lista';
            });
            return;
        }

        Action.value = 'e';
        Id.value = response.id;

        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: response.msg || 'Empresa salva com sucesso!',
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = `/enterprise/detalhes/${response.id}`;
        });

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: `Restrições: ${error.message}`,
            timer: 3000,
            timerProgressBar: true,
        });
    } finally {
        $('button, input, checkbox').prop('disabled', false);
    }
}

InsertBtn?.addEventListener('click', async () => {
    await applyChanges();
});

