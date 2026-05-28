import Swal from "sweetalert2";
import Requests from "../Components/requests.js";
import Validate from "../Components/validate.js";

const buttonPreRegister = document.getElementById("buttonPreRegister");
const buttonLogin = document.getElementById("buttonLogin");
const requests = new Requests();

// Função auxiliar para exibir alertas padronizados (Evita repetição de código)
const showAlert = (icon, title, text, callback = null) => {
  Swal.fire({
    icon,
    title,
    text,
    timer: 2500,
    showConfirmButton: icon !== "success", // Oculta botão no sucesso para fechar rápido pelo timer
    timerProgressBar: true,
  }).then(() => {
    if (callback) callback();
  });
};

// --- FLUXO DE LOGIN ---
if (buttonLogin) {
  buttonLogin.addEventListener("click", async () => {
    // Ideal alterar "form" para o ID específico do formulário de login, ex: "#formLogin"
    const valid = Validate.SetForm("form").Validate();
    if (!valid) {
      showAlert("error", "Ops...", "Preencha os campos corretamente!");
      return;
    }

    const originalText = buttonLogin.textContent;
    try {
      buttonLogin.disabled = true;
      buttonLogin.textContent = "Autenticando, aguarde...";

      const response = await requests.setForm("form").post("/authentication/auth");

      if (!response.status) {
        showAlert("error", "Ops...", response.msg || "Não foi possível validar as credenciais.");
        return;
      }

      // Redireciona para a rota retornada pela API ou raiz
      window.location.replace(response.url || "/");
    } catch (error) {
      showAlert("error", "Ops...", error.message || "Restrição: tente de novo mais tarde.");
    } finally {
      buttonLogin.disabled = false;
      buttonLogin.textContent = originalText;
    }
  });
}

// --- FLUXO DE PRÉ-CADASTRO ---
if (buttonPreRegister) {
  buttonPreRegister.addEventListener("click", async () => {
    // Ideal alterar "form" para o ID específico do modal, ex: "#formRegister"
    const validou = Validate.SetForm("form").Validate();
    if (!validou) {
      showAlert("error", "Ops...", "Preencha os campos corretamente!");
      return;
    }

    const originalText = buttonPreRegister.textContent;
    try {
      buttonPreRegister.textContent = "Cadastrando, por favor aguarde...";

      const response = await requests.setForm("form").post("/authentication/preregister");

      // CORREÇÃO: Verifica se a API retornou erro e interrompe o fluxo com 'return'
      if (!response.status) {
        showAlert("error", "Ops...", response.msg || "Erro ao realizar o cadastro.");
        return;
      }

      // Se passou pelo IF anterior, é sucesso garantido
      showAlert("success", "Sucesso!", response.msg, () => {
        // Limpa o formulário e fecha o modal se o jQuery estiver disponível
        if (typeof $ !== "undefined") {
          $("#modalPreRegisterUser").modal("hide");
          $("form").trigger("reset");
        }
      });

    } catch (error) {
      showAlert("error", "Ops...", error.message || "Ocorreu um erro ao cadastrar o usuário!");
    } finally {
    }
  });
}

// --- FLUXO DE AUTENTICAÇÃO GOOGLE ---
/**
 * Callback executado pelo One Tap / Botão do Google quando o usuário se autentica na janela deles
 */
window.handleCredentialResponse = async (googleResponse) => {
  try {
    // Exibe um feedback visual de carregamento na tela
    Swal.fire({
      title: "Autenticando com o Google...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Envia a credencial gerada pelo Google para o seu método public function google() no PHP
    const response = await fetch("/authentication/google", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      // Constrói os dados necessários (incluindo os tokens CSRF que seu backend exige)
      body: new URLSearchParams({
        credential: googleResponse.credential,
        g_csrf_token: googleResponse.select_by === "btn" ? getCookie("g_csrf_token") : (document.getElementsByName("g_csrf_token")[0]?.value || "")
      })
    });

    const result = await response.json();

    if (!result.status) {
      showAlert("error", "Falha na Autenticação", result.msg || "Não foi possível logar com o Google.");
      return;
    }

    // Sucesso: Redireciona para a URL fornecida pelo seu controller PHP (/home)
    showAlert("success", "Bem-vindo!", result.msg, () => {
      window.location.replace(result.url || "/home");
    });

  } catch (error) {
    showAlert("error", "Ops...", "Erro de comunicação com o servidor.");
    console.error(error);
  }
};

// Função auxiliar simples para capturar cookies no front-end
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}