# Histórico de Credenciais

## 📋 Visão Geral

O sistema de histórico de credenciais permite gerenciar e rastrear todas as credenciais de segurança dos usuários, incluindo aquelas que foram removidas (soft delete). Isso garante um registro completo e auditável de todas as credenciais emitidas ao longo do tempo.

## 🎯 Funcionalidades Principais

### 1. **Soft Delete (Exclusão Suave)**
- Credenciais não são removidas permanentemente do banco de dados
- Ficam marcadas como "deletadas" mas mantêm todos os dados
- Podem ser restauradas a qualquer momento
- Histórico completo preservado para auditoria

### 2. **Visualização do Histórico**
- Ver todas as credenciais de um usuário (ativas e deletadas)
- Filtros avançados por status, tipo, período
- Timeline visual da evolução das credenciais
- Indicadores claros de status (ativa/deletada)

### 3. **Restauração de Credenciais**
- Restaurar credenciais deletadas individualmente
- Restauração em lote (múltiplas credenciais)
- Notificações de sucesso
- Validação de regras de negócio

### 4. **Exclusão Permanente (Force Delete)**
- Disponível apenas para Super Administradores
- Requer confirmação dupla
- Remove permanentemente do banco de dados
- Ação irreversível

## 🔐 Permissões e Controle de Acesso

### Perfis e Suas Permissões

| Ação | Consulta | Admin | Super Admin |
|------|----------|-------|-------------|
| Visualizar Histórico | ✅ | ✅ | ✅ |
| Criar Credencial | ❌ | ✅ | ✅ |
| Editar Credencial | ❌ | ✅ | ✅ |
| Deletar (Soft) | ❌ | ✅ | ✅ |
| Restaurar | ❌ | ✅ | ✅ |
| Force Delete | ❌ | ❌ | ✅ |

## 📖 Como Usar

### Visualizar Histórico de um Usuário

1. Acesse o menu **Usuários**
2. Clique em **Editar** no usuário desejado
3. Navegue até a aba **Histórico de Credenciais**
4. Você verá todas as credenciais (ativas e deletadas)

### Deletar uma Credencial

1. Na lista de credenciais, clique no ícone de **lixeira** (🗑️)
2. Confirme a ação
3. A credencial será movida para o histórico (soft delete)
4. Uma notificação de sucesso será exibida

**Nota:** A credencial não é removida do banco, apenas marcada como deletada.

### Restaurar uma Credencial Deletada

1. No histórico de credenciais, ative o filtro **"Apenas Deletadas"**
2. Localize a credencial que deseja restaurar
3. Clique no ícone de **restaurar** (↻)
4. A credencial voltará ao status ativo
5. Uma notificação de sucesso será exibida

### Excluir Permanentemente (Force Delete)

⚠️ **ATENÇÃO: Esta ação é IRREVERSÍVEL!**

1. Certifique-se de estar logado como **Super Admin**
2. No histórico, localize a credencial deletada
3. Clique no ícone de **excluir permanentemente** (⚠️)
4. Leia o aviso de confirmação cuidadosamente
5. Confirme a ação
6. A credencial será removida permanentemente do sistema

**Uso recomendado:** Apenas para correção de dados duplicados ou erros graves.

### Restauração em Lote

1. Ative o filtro **"Apenas Deletadas"**
2. Selecione múltiplas credenciais usando os checkboxes
3. Clique em **Ações em Lote** → **Restaurar**
4. Confirme a ação
5. Todas as credenciais selecionadas serão restauradas

## 🔍 Filtros Disponíveis

### Filtro de Status (Trashed)

- **Sem Deletadas** (padrão): Mostra apenas credenciais ativas
- **Apenas Deletadas**: Mostra apenas credenciais deletadas
- **Com Deletadas**: Mostra todas (ativas e deletadas)

### Outros Filtros

- **Tipo**: CRED ou TCMS
- **Status da Credencial**: Ativa, Pendente, Vencida, etc.
- **Nível de Sigilo**: AR, R, S

## 📊 Indicadores Visuais

### Colunas da Tabela

- **Ícone de Status**: 
  - ✅ (verde) = Credencial ativa
  - 🗑️ (vermelho) = Credencial deletada

- **Badge de Status**:
  - 🟢 Ativa
  - 🟡 Pendente
  - 🔴 Vencida
  - ⚫ Negada

### No UserResource

- **Contador de Credenciais**: Total de credenciais (incluindo histórico)
- **Contador de Ativas**: Apenas credenciais ativas
- **Tooltip**: Informações adicionais ao passar o mouse

## 🔄 Fluxos de Trabalho Recomendados

### Cenário 1: Renovação de Credencial

```
1. Usuário possui credencial vencida
2. Admin deleta a credencial antiga (soft delete)
3. Admin cria nova credencial para o usuário
4. Histórico mantém registro da credencial antiga
5. Sistema permite apenas 1 credencial ativa por usuário
```

### Cenário 2: Correção de Erro

```
1. Credencial criada com dados incorretos
2. Admin deleta a credencial (soft delete)
3. Admin cria nova credencial com dados corretos
4. Se necessário, pode restaurar a antiga para referência
```

### Cenário 3: Auditoria

```
1. Auditor acessa histórico do usuário
2. Visualiza timeline completa de credenciais
3. Identifica todas as credenciais emitidas
4. Verifica datas de concessão e validade
5. Exporta dados para relatório (se necessário)
```

### Cenário 4: Recuperação de Credencial

```
1. Credencial deletada por engano
2. Admin acessa histórico
3. Filtra por "Apenas Deletadas"
4. Localiza a credencial
5. Clica em "Restaurar"
6. Credencial volta a ficar ativa
```

## 🛠️ Aspectos Técnicos

### SoftDeletes Trait

O modelo `Credential` utiliza o trait `SoftDeletes` do Laravel:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Credential extends Model
{
    use SoftDeletes;
    
    // Campo deleted_at é gerenciado automaticamente
}
```

### Relacionamentos no Modelo User

```php
// Todas as credenciais (padrão - sem deletadas)
public function credentials(): HasMany
{
    return $this->hasMany(Credential::class);
}

// Apenas credenciais ativas
public function activeCredential(): HasMany
{
    return $this->hasMany(Credential::class)
        ->whereNull('deleted_at');
}

// Histórico completo (incluindo deletadas)
public function credentialHistory(): HasMany
{
    return $this->hasMany(Credential::class)
        ->withTrashed()
        ->orderBy('created_at', 'desc');
}
```

### Queries Úteis

```php
// Buscar apenas ativas (padrão)
Credential::all();

// Buscar incluindo deletadas
Credential::withTrashed()->get();

// Buscar apenas deletadas
Credential::onlyTrashed()->get();

// Restaurar uma credencial
$credential->restore();

// Deletar permanentemente
$credential->forceDelete();
```

## 📈 Estatísticas e Métricas

### Contadores Disponíveis

```php
// Total de credenciais (incluindo histórico)
$user->credentials()->withTrashed()->count();

// Apenas ativas
$user->credentials()->count();

// Apenas deletadas
$user->credentials()->onlyTrashed()->count();
```

## ⚠️ Avisos Importantes

### Regras de Negócio

1. **Um usuário pode ter apenas UMA credencial ativa por vez**
   - O sistema valida esta regra ao criar/restaurar
   - Credenciais deletadas não contam para este limite

2. **Soft Delete é o padrão**
   - Sempre use delete() ao invés de forceDelete()
   - Force delete apenas em casos excepcionais

3. **Auditoria Completa**
   - Todas as ações são registradas
   - Timestamps de criação, atualização e deleção são mantidos

### Boas Práticas

✅ **FAÇA:**
- Use soft delete ao remover credenciais
- Documente o motivo da deleção em observações
- Verifique o histórico antes de criar novas credenciais
- Restaure credenciais quando apropriado

❌ **NÃO FAÇA:**
- Use force delete desnecessariamente
- Delete credenciais sem verificar o histórico
- Crie múltiplas credenciais ativas para um usuário

## 🐛 Troubleshooting

### Problema: Não consigo criar nova credencial

**Causa:** Usuário já possui uma credencial ativa

**Solução:**
1. Verifique o histórico do usuário
2. Delete a credencial ativa existente (soft delete)
3. Crie a nova credencial

### Problema: Credencial não aparece na lista

**Causa:** Filtro "Trashed" está ativo

**Solução:**
1. Verifique os filtros aplicados
2. Mude para "Com Deletadas" ou "Sem Deletadas"
3. Limpe todos os filtros

### Problema: Não consigo restaurar credencial

**Causa:** Falta de permissão ou regra de negócio

**Solução:**
1. Verifique se tem perfil Admin ou Super Admin
2. Verifique se o usuário já tem uma credencial ativa
3. Se sim, delete a ativa antes de restaurar a antiga

## 📚 Referências

- [Laravel SoftDeletes Documentation](https://laravel.com/docs/12.x/eloquent#soft-deleting)
- [Filament Tables Documentation](https://filamentphp.com/docs/4.x/tables)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission/v6)

## 🔄 Atualizações

**Versão 1.0** (Data atual)
- Implementação inicial do histórico de credenciais
- Soft delete para Credentials
- Relation Manager para UserResource
- Filtros e ações de restauração
- Notificações de sucesso
- Testes completos

---

**Desenvolvido por:** SecurID Team  
**Última atualização:** 2024  
**Documentação mantida em:** `.taskmaster/docs/credential-history.md`
