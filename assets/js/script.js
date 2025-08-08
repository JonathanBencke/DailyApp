/**
 * Gerenciador de abas da interface
 */
function switchTab(tabId) {
    // Esconde todos os conteúdos de aba
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove classe ativa de todas as abas
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Ativa a aba selecionada
    document.getElementById(tabId).classList.add('active');
    
    // Ativa o botão da aba selecionada
    const clickedTab = event.target;
    clickedTab.classList.add('active');
}

/**
 * Validação de formulários e outras inicializações
 */
document.addEventListener('DOMContentLoaded', function() {
    // Validar formulário de férias
    const vacationForms = document.querySelectorAll('form');
    vacationForms.forEach(form => {
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && actionInput.value === 'add_vacation') {
            form.addEventListener('submit', function(event) {
                const startDate = document.getElementById('vacationStart').value;
                const endDate = document.getElementById('vacationEnd').value;
                
                if (startDate > endDate) {
                    alert('A data de início deve ser anterior à data de fim das férias.');
                    event.preventDefault();
                }
            });
        }
    });
    
    // Auto-seleção de abas baseada em parâmetros PHP
    const urlParams = new URLSearchParams(window.location.search);
    const lastAction = sessionStorage.getItem('lastAction');
    
    if (lastAction === 'add_vacation' || lastAction === 'remove_vacation') {
        // Simula o clique na aba de férias
        const vacationsTab = document.querySelector('.tab[onclick="switchTab(\'vacations-tab\')"]');
        if (vacationsTab) {
            vacationsTab.click();
        }
        sessionStorage.removeItem('lastAction');
    }
    
    // Salva a ação atual para usar depois do reload
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const actionInput = form.querySelector('input[name="action"]');
            if (actionInput) {
                sessionStorage.setItem('lastAction', actionInput.value);
            }
        });
    });
    
    // Confirmação para remoção de itens
    const removeForms = document.querySelectorAll('form');
    removeForms.forEach(form => {
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && (actionInput.value === 'remove_holiday' || actionInput.value === 'remove_vacation')) {
            form.addEventListener('submit', function(event) {
                const confirmMessage = actionInput.value === 'remove_holiday' 
                    ? 'Tem certeza que deseja remover este feriado?' 
                    : 'Tem certeza que deseja remover este período de férias?';
                    
                if (!confirm(confirmMessage)) {
                    event.preventDefault();
                }
            });
        }
    });
    
    // Validação de datas do formulário de equipe
    const startDateInput = document.getElementById('startDate');
    const startPersonSelect = document.getElementById('startPerson');
    
    if (startDateInput && startPersonSelect) {
        const resetForm = startDateInput.closest('form');
        resetForm.addEventListener('submit', function(event) {
            if (!startDateInput.value || !startPersonSelect.value) {
                alert('Por favor, preencha tanto a data quanto a pessoa.');
                event.preventDefault();
            }
        });
    }
    
    // Validação do formulário de equipe
    const teamTextarea = document.getElementById('team');
    if (teamTextarea) {
        const teamForm = teamTextarea.closest('form');
        teamForm.addEventListener('submit', function(event) {
            const teamValue = teamTextarea.value.trim();
            if (!teamValue) {
                alert('A lista de apresentadores não pode estar vazia.');
                event.preventDefault();
                return;
            }
            
            const members = teamValue.split(',').map(m => m.trim()).filter(m => m);
            if (members.length < 2) {
                alert('É necessário ter pelo menos 2 membros na equipe.');
                event.preventDefault();
            }
        });
    }
});

/**
 * Funções utilitárias
 */

// Função para destacar o dia atual no calendário
function highlightToday() {
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    
    const calendarRows = document.querySelectorAll('.calendar-table tbody tr');
    calendarRows.forEach(row => {
        const dateCell = row.querySelector('td:first-child');
        if (dateCell) {
            const cellDate = dateCell.textContent.trim();
            // Converte dd/mm/yyyy para yyyy-mm-dd para comparação
            const dateParts = cellDate.split('/');
            if (dateParts.length === 3) {
                const cellDateStr = `${dateParts[2]}-${dateParts[1].padStart(2, '0')}-${dateParts[0].padStart(2, '0')}`;
                if (cellDateStr === todayStr) {
                    row.classList.add('calendar-today');
                }
            }
        }
    });
}

// Função para auto-ajustar altura do textarea da equipe
function autoResizeTextarea() {
    const teamTextarea = document.getElementById('team');
    if (teamTextarea) {
        teamTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Ajusta altura inicial
        teamTextarea.style.height = 'auto';
        teamTextarea.style.height = (teamTextarea.scrollHeight) + 'px';
    }
}

// Chama as funções de inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    highlightToday();
    autoResizeTextarea();
});

// Função para mostrar feedback visual ao usuário
function showFeedback(message, type = 'success') {
    const feedback = document.createElement('div');
    feedback.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 15px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 1000;
        transition: opacity 0.3s;
        ${type === 'success' ? 'background-color: #4CAF50;' : 'background-color: #d9534f;'}
    `;
    feedback.textContent = message;
    
    document.body.appendChild(feedback);
    
    setTimeout(() => {
        feedback.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(feedback);
        }, 300);
    }, 3000);
}
