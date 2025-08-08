# Sistema de Controle de Daily Presenter

## Estrutura do Projeto

```
gd-ponto/
├── index.php                      # Arquivo principal
├── assets/
│   ├── css/
│   │   └── style.css              # Estilos CSS
│   └── js/
│       └── script.js              # JavaScript
├── includes/
│   ├── auth.php                   # Funções de autenticação
│   ├── holidays.php               # Gerenciamento de feriados
│   ├── vacations.php              # Gerenciamento de férias
│   ├── presenter.php              # Cálculo de apresentadores
│   ├── forms.php                  # Processamento de formulários
│   └── utils.php                  # Funções utilitárias
├── templates/
│   ├── login.php                  # Template de login
│   ├── main.php                   # Template principal
│   ├── team_list.php              # Lista da equipe
│   ├── config_panel.php           # Painel de configurações
│   └── calendar.php               # Calendário de apresentações
├── auth_config.json               # Configurações de autenticação
├── daily_config.json              # Configurações da equipe
├── holidays.json                  # Lista de feriados
└── vacations.json                 # Períodos de férias
```

## Arquivos por Funcionalidade

### Autenticação (`includes/auth.php`)
- `initializeAuth()` - Inicializa o sistema de autenticação
- `processLogin()` - Processa o login do usuário
- `processLogout()` - Processa o logout
- `isAuthenticated()` - Verifica se o usuário está autenticado

### Feriados (`includes/holidays.php`)
- `ensureHolidaysFile()` - Garante que o arquivo de feriados existe
- `isHoliday()` - Verifica se uma data é feriado
- `isBusinessDay()` - Verifica se é dia útil
- `getNextBusinessDay()` - Obtém o próximo dia útil
- `getPreviousBusinessDay()` - Obtém o dia útil anterior

### Férias (`includes/vacations.php`)
- `ensureVacationsFile()` - Garante que o arquivo de férias existe
- `isOnVacation()` - Verifica se um membro está de férias
- `getNextAvailableMember()` - Encontra o próximo membro disponível

### Apresentadores (`includes/presenter.php`)
- `getCurrentPresenter()` - Calcula o apresentador atual
- `getPresentationDates()` - Calcula as datas de apresentação

### Formulários (`includes/forms.php`)
- `processFormSubmissions()` - Processa todos os formulários da aplicação

### Utilitários (`includes/utils.php`)
- `getPortugueseTranslations()` - Traduções para português
- `getPortugueseDate()` - Formata datas em português
- `initializeConfig()` - Inicializa configurações
- `getOrderedTeam()` - Ordena equipe por ordem de apresentação
- `getPresentersContext()` - Obtém contexto de apresentadores

## Templates

### Login (`templates/login.php`)
Interface de login simples e responsiva.

### Principal (`templates/main.php`)
Template principal que inclui todos os outros componentes.

### Lista da Equipe (`templates/team_list.php`)
Exibe a lista ordenada dos apresentadores.

### Painel de Configurações (`templates/config_panel.php`)
Interface com abas para gerenciar:
- Configurações da equipe
- Feriados
- Férias

### Calendário (`templates/calendar.php`)
Exibe o calendário de apresentações dos próximos dias.

## Assets

### CSS (`assets/css/style.css`)
Contém todos os estilos da aplicação, organizados por seções:
- Estilos de login
- Estilos da aplicação principal
- Estilos do calendário
- Media queries para responsividade

### JavaScript (`assets/js/script.js`)
Funcionalidades JavaScript:
- Gerenciamento de abas
- Validação de formulários
- Funções utilitárias
- Feedback visual

## Vantagens da Nova Estrutura

1. **Separação de Responsabilidades**: Cada arquivo tem uma função específica
2. **Facilidade de Manutenção**: Modificações são mais localizadas
3. **Reutilização de Código**: Funções podem ser reutilizadas
4. **Melhor Organização**: Código mais limpo e legível
5. **Performance**: CSS e JS podem ser cacheados separadamente
6. **Escalabilidade**: Fácil adicionar novas funcionalidades

## Como Usar

1. Certifique-se de que todas as pastas e arquivos estão no local correto
2. Acesse o `index.php` no navegador
3. Use as credenciais padrão: usuário `gd` e senha `gd`
4. Configure sua equipe e feriados conforme necessário

## Configurações

- **Equipe**: Adicione/remova membros da equipe
- **Feriados**: Gerencie feriados personalizados
- **Férias**: Registre períodos de férias dos membros
- **Data inicial**: Configure o ponto de partida das apresentações
