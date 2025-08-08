<?php
session_start();

// Incluir arquivos de funções
require_once 'includes/auth.php';
require_once 'includes/holidays.php';
require_once 'includes/vacations.php';
require_once 'includes/presenter.php';
require_once 'includes/forms.php';
require_once 'includes/utils.php';

// Configurações de autenticação
$authFile = 'auth_config.json';
$defaultAuth = [
    'username' => 'gd',
    'password' => password_hash('gd', PASSWORD_DEFAULT)
];

// Inicializar autenticação
$authConfig = initializeAuth($authFile, $defaultAuth);

// Processar login e logout
$loginError = processLogin($authConfig);
processLogout();

// Verificar autenticação
if (!isAuthenticated()) {
    include_once 'templates/login.php';
    exit;
}

// ==============================================
// CONFIGURAÇÕES E INICIALIZAÇÃO
// ==============================================

// Configurações iniciais
$configFile = 'daily_config.json';
$holidaysFile = 'holidays.json';
$vacationsFile = 'vacations.json';

// Inicializar configurações
$config = initializeConfig($configFile);
$holidays = ensureHolidaysFile($holidaysFile);
$vacations = ensureVacationsFile($vacationsFile);

// Processar formulários
list($config, $holidays, $vacations) = processFormSubmissions($config, $holidays, $vacations, $configFile, $holidaysFile, $vacationsFile);

// ==============================================
// CÁLCULOS DE APRESENTADORES
// ==============================================

$currentPresenter = getCurrentPresenter($config, $holidays, $vacations);

// Validação adicional para garantir que o apresentador atual seja válido
if ($currentPresenter === null || !in_array($currentPresenter, $config['team'])) {
    $currentPresenter = $config['team'][0] ?? 'N/A';
}

$presentationDates = getPresentationDates($config, $currentPresenter, $holidays, $vacations);

// Obter contexto dos apresentadores (anterior, atual, próximo)
$presenters = getPresentersContext($config, $currentPresenter, $holidays, $vacations);

// ==============================================
// PREPARAÇÃO DE DADOS PARA A VIEW
// ==============================================

// Data atual e traduções
$dataAtual = new DateTime();
$dateTranslation = getPortugueseDate($dataAtual);
$diaSemana = $dateTranslation['dayName'];
$mes = $dateTranslation['monthName'];

// Verificações de status
$today = new DateTime();
$isNotBusinessDay = !isBusinessDay($today, $holidays);
$isFeriado = isHoliday($today, $holidays);
$feriado = $isFeriado ? $holidays[$today->format('Y-m-d')] : '';
$currentOnVacation = isOnVacation($currentPresenter, $today, $vacations);

// Preparar dados para templates
$orderedTeam = getOrderedTeam($config, $currentPresenter);

// Ordenar feriados e férias para exibição
$sortedHolidays = $holidays;
ksort($sortedHolidays);

$sortedVacations = [];
foreach ($config['team'] as $member) {
    if (isset($vacations[$member])) {
        $sortedVacations[$member] = $vacations[$member];
    }
}

// Incluir template principal
include_once 'templates/main.php';