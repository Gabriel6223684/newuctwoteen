import Requests from "../Components/requests.js";
import Validate from "../Components/validate.js";

const Action = document.getElementById('action');
const Id = document.getElementById('id');
const InsertBtn = document.getElementById('insert');

async function applyChanges() {
    // 1. FAÇA A VALIDAÇÃO PRIMEIRO (Com os campos ainda ativos)
    const IsValid = Validate.SetForm('form').Validate();
    if (!IsValid) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Por favor, corrija os erros no formulário antes de salvar.',
            timer: 3000,
            timerProgressBar: true,
        });
        return;
    }

    // 2. AGORA DESATIVE OS ELEMENTOS DE FORMA SEGURA
    // Usamos 'readonly' nos inputs para que o valor AINDA SEJA ENVIADO na requisição.
    // Botões nós desativamos com 'disabled' normalmente.
    $('input, select, textarea').prop('readOnly', true);
    $('button, input[type="checkbox"]').prop('disabled', true);

    const requests = new Requests();
    try {
        // Como os inputs estão apenas como 'readOnly', a sua classe Requests vai conseguir ler os dados normalmente!
        const response = (Action.value !== 'e')
            ? await requests.setForm('form').post('/fornecedor/insert')
            : await requests.setForm('form').post('/fornecedor/update');

        if (!response?.status) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: response?.msg || 'Ocorreu um erro ao salvar os dados do fornecedor.',
                timer: 3000,
                timerProgressBar: true,
            });

            // Reativa em caso de erro retornado pelo servidor
            $('input, select, textarea').prop('readOnly', false);
            $('button, input[type="checkbox"]').prop('disabled', false);
            return;
        }

        if (Action.value === 'e') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: response.msg || 'Dados do fornecedor alterados com sucesso.',
                timer: 3000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = '/fornecedor/lista';
            });
            return;
        }

        // Cadastro: transformar em edição sem recarregar (opcional) ou redirecionar
        Action.value = 'e';
        Id.value = response.id;

        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: response.msg || 'Fornecedor salvo com sucesso!',
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = `/fornecedor/detalhes/${response.id}`;
        });

    } catch (error) {
        console.log('fornecedor/insert error', error);

        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: `Restrições: ${error?.message || 'sem mensagem'}`,
            timer: 3000,
            timerProgressBar: true,
        });
    } finally {
        // 3. REATIVA TUDO NO FINAL DE QUALQUER FORMA
        $('input, select, textarea').prop('readOnly', false);
        $('button, input[type="checkbox"]').prop('disabled', false);
    }
}

InsertBtn?.addEventListener('click', async () => {
    await applyChanges();
});