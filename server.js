require("dotenv").config();

const express = require("express");
const axios = require("axios");
const jwt = require("jsonwebtoken");
const cookieParser = require("cookie-parser");

const app = express();

app.use(cookieParser());

// 1. Redireciona para Google
app.get("/auth/google", (req, res) => {
  const url = `https://accounts.google.com/o/oauth2/v2/auth?
    client_id=${process.env.GOOGLE_CLIENT_ID}
    &redirect_uri=${process.env.GOOGLE_REDIRECT_URI}
    &response_type=code
    &scope=openid email profile
    &access_type=offline
  `;

  res.redirect(url);
});

// 2. Callback do Google
app.get("/auth/google/callback", async (req, res) => {
  const code = req.query.code;

  try {
    // troca code por tokens
    const tokenResponse = await axios.post(
      "https://oauth2.googleapis.com/token",
      {
        code,
        client_id: process.env.GOOGLE_CLIENT_ID,
        client_secret: process.env.GOOGLE_CLIENT_SECRET,
        redirect_uri: process.env.GOOGLE_REDIRECT_URI,
        grant_type: "authorization_code",
      },
    );

    const { id_token } = tokenResponse.data;

    // pega dados do usuário
    const userResponse = await axios.get(
      "https://openidconnect.googleapis.com/v1/userinfo",
      {
        headers: {
          Authorization: `Bearer ${tokenResponse.data.access_token}`,
        },
      },
    );

    const user = userResponse.data;

    // cria JWT interno
    const appToken = jwt.sign(
      {
        email: user.email,
        name: user.name,
        picture: user.picture,
      },
      process.env.JWT_SECRET,
      { expiresIn: "7d" },
    );

    // salva cookie
    res.cookie("token", appToken, {
      httpOnly: true,
      secure: false,
      sameSite: "lax",
    });

    res.send("Login realizado com sucesso");
  } catch (err) {
    console.error(err.response?.data || err);

    res.status(500).send("Erro autenticação");
  }
});

app.listen(3000, () => {
  console.log("Servidor rodando");
});
