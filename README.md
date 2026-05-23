# newuctwoteen

Repositório de apoio à aula de **Técnicas de Teste de Software**, preparado para apresentação aos alunos do Senac.

O projeto demonstra, de forma prática, a aplicação de testes automatizados em uma aplicação PHP moderna, utilizando ambiente containerizado, banco de dados relacional e pipeline de integração e deploy automatizado.

---

# Objetivo

Este repositório tem como finalidade servir como base didática para o estudo e demonstração de:

- Fundamentos de testes de software
- Testes automatizados em aplicações PHP
- Organização de ambiente com Docker
- Integração com banco de dados PostgreSQL
- Execução de testes com Pest
- Automação de validação e deploy com GitHub Actions

---

# Stack Tecnológica

- PHP 8.5
- PostgreSQL 18.3
- Docker
- Pest PHP
- GitHub Actions

---

# Badges

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.3-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Pest](https://img.shields.io/badge/Pest-Testing-7B2BF9?style=for-the-badge)
![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?style=for-the-badge&logo=github-actions&logoColor=white)

---

# Estrutura do Projeto

Este ambiente foi planejado para fornecer uma base reprodutível e próxima de um cenário real de desenvolvimento, permitindo aos participantes:

- Executar a aplicação
- Rodar testes automatizados
- Compreender práticas de qualidade de software
- Integrar testes ao fluxo de entrega contínua

---

# Como executar o projeto

## Subir o ambiente com Docker

```bash
docker compose up -d
```

## Acessar os containers

```bash
docker ps
```

## Executar os testes

```bash
docker compose exec app ./vendor/bin/pest
```