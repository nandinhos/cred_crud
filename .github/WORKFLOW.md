# 🔄 Fluxo de Trabalho Git - CredCrud

## 📋 Regras Obrigatórias

### ✅ Sempre Trabalhar com Branches

**NUNCA fazer commit direto na `main`!**

A branch `main` deve sempre estar estável e em condições de produção.

---

## 🚀 Fluxo para Novas Features

### 1️⃣ **Atualizar a Main Local**

Antes de criar qualquer nova branch, sempre atualize a `main`:

```bash
git checkout main
git pull origin main
```

### 2️⃣ **Criar Branch para a Feature**

Use nomes descritivos e siga a convenção:

```bash
git checkout -b feature/nome-da-feature
```

**Convenções de nomes:**
- `feature/` - Nova funcionalidade
- `fix/` - Correção de bug
- `hotfix/` - Correção urgente em produção
- `refactor/` - Refatoração de código
- `docs/` - Apenas documentação
- `test/` - Adicionar ou corrigir testes

**Exemplos:**
```bash
git checkout -b feature/historico-credenciais
git checkout -b fix/validacao-usuario
git checkout -b hotfix/erro-critico-login
git checkout -b refactor/reorganizar-controllers
```

### 3️⃣ **Desenvolver e Commitar**

Faça commits frequentes e com mensagens claras seguindo o **Conventional Commits**:

```bash
git add .
git commit -m "feat: adicionar histórico de credenciais"
```

**Tipos de commit:**
- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Documentação
- `style:` - Formatação (não afeta código)
- `refactor:` - Refatoração
- `test:` - Adicionar/corrigir testes
- `chore:` - Tarefas de manutenção
- `perf:` - Melhorias de performance

**Exemplos de commits:**
```bash
git commit -m "feat: implementar soft delete em credenciais"
git commit -m "fix: corrigir validação de usuário duplicado"
git commit -m "docs: atualizar README com instruções de instalação"
git commit -m "refactor: reorganizar estrutura de pastas"
git commit -m "test: adicionar testes para histórico"
git commit -m "chore: atualizar dependências"
```

### 4️⃣ **Push para o Repositório Remoto**

```bash
git push origin feature/nome-da-feature
```

Se for o primeiro push da branch:
```bash
git push -u origin feature/nome-da-feature
```

### 5️⃣ **Criar Pull Request (PR)**

No GitHub, crie um Pull Request da sua branch para a `main`.

**Descrição do PR deve conter:**
- 📝 Descrição clara do que foi implementado
- ✅ Checklist de tarefas concluídas
- 🧪 Evidências de testes realizados
- 📸 Screenshots (se aplicável)
- 🔗 Link para issue relacionada (se houver)

### 6️⃣ **Review e Merge**

- Aguardar aprovação (se configurado)
- Resolver conflitos se houver
- Fazer merge para `main`

### 7️⃣ **Limpar Branches Antigas**

Após o merge, deletar a branch local e remota:

```bash
# Voltar para main
git checkout main
git pull origin main

# Deletar branch local
git branch -d feature/nome-da-feature

# Deletar branch remota
git push origin --delete feature/nome-da-feature
```

---

## 🔧 Comandos Úteis

### Verificar branches
```bash
# Listar todas as branches
git branch -a

# Ver branches já mergeadas
git branch --merged main

# Ver branches não mergeadas
git branch --no-merged main
```

### Atualizar branch com main
```bash
# Estando na sua branch de feature
git checkout feature/sua-branch
git fetch origin
git merge origin/main

# Ou usando rebase (mantém histórico linear)
git rebase origin/main
```

### Desfazer mudanças
```bash
# Desfazer mudanças não commitadas
git restore arquivo.php

# Desfazer último commit (mantém mudanças)
git reset --soft HEAD~1

# Desfazer último commit (descarta mudanças)
git reset --hard HEAD~1
```

### Stash (guardar mudanças temporariamente)
```bash
# Guardar mudanças
git stash

# Ver stashes
git stash list

# Recuperar última mudança
git stash pop

# Recuperar stash específico
git stash apply stash@{0}
```

---

## 🚫 O Que NÃO Fazer

❌ **NUNCA** fazer commit direto na `main`
❌ **NUNCA** fazer force push (`git push -f`) em branches compartilhadas
❌ **NUNCA** commitar arquivos sensíveis (.env, senhas, tokens)
❌ **NUNCA** commitar arquivos temporários ou de build
❌ **NUNCA** fazer merge sem testar
❌ **NUNCA** criar branch a partir de outra branch de feature (sempre da `main`)

---

## ✅ Boas Práticas

### Commits
✅ Fazer commits pequenos e frequentes
✅ Escrever mensagens claras e descritivas
✅ Seguir Conventional Commits
✅ Commitar apenas código testado

### Branches
✅ Criar branch para cada feature/fix
✅ Usar nomes descritivos
✅ Manter branches curtas (poucos dias)
✅ Deletar branches após merge

### Code Review
✅ Revisar próprio código antes de PR
✅ Adicionar testes
✅ Atualizar documentação
✅ Verificar se não quebra nada existente

### Merge
✅ Atualizar branch com main antes do merge
✅ Resolver conflitos cuidadosamente
✅ Executar testes antes do merge
✅ Usar merge commit (--no-ff) para manter histórico

---

## 🔄 Exemplo Completo

```bash
# 1. Atualizar main
git checkout main
git pull origin main

# 2. Criar branch
git checkout -b feature/adicionar-relatorios

# 3. Desenvolver
# ... fazer alterações ...

# 4. Commitar
git add .
git commit -m "feat: adicionar módulo de relatórios

- Criar controller de relatórios
- Adicionar views de listagem e detalhes
- Implementar filtros por data e tipo
- Adicionar testes unitários"

# 5. Push
git push origin feature/adicionar-relatorios

# 6. Criar PR no GitHub

# 7. Após merge, limpar
git checkout main
git pull origin main
git branch -d feature/adicionar-relatorios
git push origin --delete feature/adicionar-relatorios
```

---

## 📚 Recursos

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)

---

## 🆘 Problemas Comuns

### "Minha branch está desatualizada"
```bash
git checkout feature/sua-branch
git fetch origin
git merge origin/main
# Resolver conflitos se houver
git push origin feature/sua-branch
```

### "Comiti na main por engano"
```bash
# Se não fez push ainda
git reset --soft HEAD~1
git checkout -b feature/nova-branch
git push origin feature/nova-branch
```

### "Preciso atualizar commit anterior"
```bash
# Adicionar mudanças ao último commit
git add .
git commit --amend --no-edit

# Se já fez push, vai precisar force push (cuidado!)
git push -f origin feature/sua-branch
```

---

**Última atualização:** 2024
**Mantido por:** Equipe de Desenvolvimento CredCrud
