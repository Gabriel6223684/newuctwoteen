document.addEventListener("DOMContentLoaded", () => {
  const btnPreRegister = document.getElementById("buttonPreRegister");
  const form = document.getElementById("form");

  if (btnPreRegister) {
    btnPreRegister.addEventListener("click", async () => {
      // 1. Captura os dados manualmente ou via FormData
      // Como os inputs estão dentro do form principal, usamos FormData(form)
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      // Validação simples antes de enviar
      if (!data.nome || !data.email || !data.senha) {
        alert("Por favor, preencha os campos obrigatórios (*)");
        return;
      }

      try {
        // Feedback visual: desativa o botão
        btnPreRegister.disabled = true;
        btnPreRegister.innerHTML =
          '<span class="spinner-border spinner-border-sm"></span> Salvando...';

        // 2. Envio para o seu Controller PHP (Refatorado anteriormente)
        const response = await fetch("/pre-register", {
          // Ajuste para sua rota real
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok) {
          alert("Cadastro realizado com sucesso!");

          // 3. Fecha o modal do Bootstrap e limpa o form
          const modalElement = document.getElementById("modalPreRegisterUser");
          const modal = bootstrap.Modal.getInstance(modalElement);
          modal.hide();
          form.reset();
        } else {
          alert("Erro: " + (result.error || "Falha ao cadastrar"));
        }
      } catch (error) {
        console.error("Erro na requisição:", error);
        alert("Erro crítico de conexão.");
      } finally {
        // Restaura o botão
        btnPreRegister.disabled = false;
        btnPreRegister.innerHTML = "Salvar";
      }
    });
  }
});
