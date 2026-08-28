/* Daily Manager — app.js */
$(function () {

    // ===== Logout =====
    $('#btn-logout').on('click', function (e) {
        e.preventDefault();
        $.ajax({ url: '/api/auth.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ action: 'logout' }) })
            .always(function () { window.location.href = '/index.php'; });
    });

    // ===== Nav Toggle (mobile) =====
    $('#navToggle').on('click', function () {
        $('.nav-links').toggleClass('open');
    });

    // ===== Helpers =====
    function formatDate(d) {
        if (!d) return '';
        var parts = d.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    function showAlert(selector, msg, type) {
        $(selector).removeClass('alert-error alert-success alert-warning').addClass('alert-' + (type || 'error')).text(msg).show();
    }

    function hideAlert(selector) { $(selector).hide(); }

    // ===== Dashboard =====
    if (typeof PAGE !== 'undefined' && PAGE === 'dashboard') {
        var today = new Date();
        var weekdays = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        var months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $('#today-date').text(weekdays[today.getDay()] + ', ' + today.getDate() + ' de ' + months[today.getMonth()] + ' de ' + today.getFullYear());

        var escapeHtml = function (value) {
            return $('<span>').text(value || '').html();
        };

        var initialsFor = function (name) {
            return String(name || '').trim().split(/\s+/).filter(Boolean).slice(0, 2).map(function (part) {
                return part.charAt(0).toUpperCase();
            }).join('') || '?';
        };

        var statusCard = function (icon, title, description, action) {
            return '<div class="card daily-empty-state">'
                + '<div class="daily-state-icon" aria-hidden="true">' + icon + '</div>'
                + '<h3>' + title + '</h3><p>' + description + '</p>'
                + (action || '') + '</div>';
        };

        var loadPresenter = function () {
            $.get('/api/presenter.php', function (res) {
                var html = '';
                if (!res.is_workday) {
                    html = statusCard('&#9789;', 'Sem daily hoje', 'Hoje não é dia útil configurado para este time.');
                } else if (res.is_holiday) {
                    html = statusCard('&#10022;', 'Feriado', 'Hoje é feriado. A rotação continua no próximo dia útil.');
                } else if (!res.configured) {
                    html = statusCard('&#9881;', 'Rotação não configurada', 'Defina data de início e primeiro apresentador para iniciar a agenda.', '<a class="btn btn-primary" href="/pages/settings.php">Configurar rotação</a>');
                } else if (!res.presenter) {
                    html = statusCard('&#9675;', 'Sem apresentador disponível', 'Verifique membros ativos e ausências cadastradas para hoje.');
                } else {
                    var presenterName = escapeHtml(res.presenter.name);
                    var initials = escapeHtml(initialsFor(res.presenter.name));
                    html = '<div class="card presenter-card">'
                        + '<div class="presenter-card-topline"><span class="presenter-status-dot"></span>Daily ativa</div>'
                        + '<div class="presenter-avatar" aria-hidden="true">' + initials + '</div>'
                        + '<p class="presenter-label">Responsável pela daily</p>'
                        + '<p class="presenter-name">' + presenterName + '</p>'
                        + '<p class="presenter-message">Sua vez de conduzir a conversa.</p>'
                        + '<button class="btn presenter-swap-btn" id="btn-skip"><span aria-hidden="true">&#8644;</span> Trocar apresentador</button>'
                        + '</div>';
                }
                $('#daily-status').html(html);

                // Trocar — avança a rotação permanentemente para o próximo membro
                $('#btn-skip').on('click', function () {
                    $(this).prop('disabled', true);
                    $.ajax({ url: '/api/presenter.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ action: 'swap' }) })
                        .done(function () { loadPresenter(); })
                        .fail(function (xhr) {
                            alert((xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Erro');
                            $('#btn-skip').prop('disabled', false);
                        });
                });

                // Semana atual — strip horizontal
                var pad2 = function (n) { return String(n).padStart(2, '0'); };
                var shortDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                var todayDate = new Date();
                var todayStr = todayDate.getFullYear() + '-' + pad2(todayDate.getMonth() + 1) + '-' + pad2(todayDate.getDate());
                var dow = todayDate.getDay();
                var monday = new Date(todayDate);
                monday.setDate(todayDate.getDate() + (dow === 0 ? -6 : 1 - dow));
                var weekDates = [];
                for (var wi = 0; wi < 5; wi++) {
                    var wd = new Date(monday);
                    wd.setDate(monday.getDate() + wi);
                    weekDates.push(wd.getFullYear() + '-' + pad2(wd.getMonth() + 1) + '-' + pad2(wd.getDate()));
                }
                var nextMap = {};
                (res.next_presenters || []).forEach(function (p) { nextMap[p.date] = p.member; });
                var stripHtml = weekDates.map(function (dateStr) {
                    var isPast = dateStr < todayStr;
                    var isToday = dateStr === todayStr;
                    var cls = 'week-card' + (isToday ? ' wc-today' : isPast ? ' wc-past' : '');
                    var dt = new Date(dateStr + 'T12:00:00');
                    var dp = dateStr.split('-');
                    var member = isToday ? res.presenter : nextMap[dateStr];
                    var nameHtml = member
                        ? '<span class="wc-avatar" aria-hidden="true">' + escapeHtml(initialsFor(member.name)) + '</span><span class="wc-name">' + escapeHtml(member.name) + '</span>'
                        : '<span class="wc-empty" aria-label="Sem apresentador">—</span>';
                    return '<div class="' + cls + '"' + (isToday ? ' aria-current="date"' : '') + '><div class="wc-head"><span class="wc-day">' + shortDays[dt.getDay()] + '</span>'
                        + (isToday ? '<span class="wc-today-badge">Hoje</span>' : '') + '</div>'
                        + '<div class="wc-date">' + dp[2] + '/' + dp[1] + '</div><div class="wc-member">' + nameHtml + '</div></div>';
                }).join('');
                $('#week-strip').html(stripHtml);
                $('#next-presenters-section').show();
            }).fail(function () {
                $('#daily-status').html('<div class="alert alert-error">Erro ao carregar dados.</div>');
            });
        };

        loadPresenter();
    }

    // ===== Members =====
    if (typeof PAGE !== 'undefined' && PAGE === 'members') {
        var loadMembers = function () {
            $.get('/api/members.php', function (members) {
                if (!members.length) {
                    $('#members-list').html('<li class="loading">Nenhum membro cadastrado.</li>');
                    return;
                }
                var html = members.map(function (m, i) {
                    return '<li class="member-item' + (!m.active ? ' inactive' : '') + '" data-id="' + m.id + '">'
                        + '<span class="member-drag">⠿</span>'
                        + '<span class="member-order">' + (i + 1) + '</span>'
                        + '<span class="member-name">' + $('<span>').text(m.name).html() + '</span>'
                        + '<span class="badge ' + (m.active ? 'badge-active' : 'badge-inactive') + '">' + (m.active ? 'Ativo' : 'Inativo') + '</span>'
                        + '<span class="member-actions">'
                        + '<button class="btn btn-sm btn-secondary btn-toggle-active" data-id="' + m.id + '" data-active="' + m.active + '">' + (m.active ? 'Desativar' : 'Ativar') + '</button>'
                        + '<button class="btn btn-sm btn-danger btn-delete-member" data-id="' + m.id + '" data-name="' + $('<span>').text(m.name).html() + '">Remover</button>'
                        + '</span>'
                        + '</li>';
                }).join('');
                $('#members-list').html(html);
                initSortable();
            });
        };

        var initSortable = function () {
            $('#members-list').sortable({
                handle: '.member-drag',
                placeholder: 'member-item',
                update: function () {
                    var order = $('#members-list .member-item').map(function () { return $(this).data('id'); }).get();
                    $.ajax({ url: '/api/members.php', method: 'PUT', contentType: 'application/json', data: JSON.stringify({ order_list: order }) })
                        .done(function () { loadMembers(); });
                }
            });
        };

        loadMembers();

        // Add member
        $('#btn-add-member').on('click', function () { $('#add-member-form').slideToggle(); $('#new-member-name').focus(); });
        $('#btn-cancel-member').on('click', function () { $('#add-member-form').slideUp(); });

        $('#btn-save-member').on('click', function () {
            var name = $('#new-member-name').val().trim();
            hideAlert('#member-form-error');
            if (!name) { showAlert('#member-form-error', 'Informe o nome do membro'); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/members.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ name: name }) })
                .done(function () { $('#new-member-name').val(''); $('#add-member-form').slideUp(); loadMembers(); })
                .fail(function (xhr) { showAlert('#member-form-error', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); })
                .always(function () { btn.prop('disabled', false); });
        });

        // Toggle active
        $(document).on('click', '.btn-toggle-active', function () {
            var id = $(this).data('id');
            var active = $(this).data('active') === true || $(this).data('active') === 'true';
            $.ajax({ url: '/api/members.php', method: 'PUT', contentType: 'application/json', data: JSON.stringify({ id: id, active: !active }) })
                .done(function () { loadMembers(); });
        });

        // Delete member
        $(document).on('click', '.btn-delete-member', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            if (!confirm('Remover "' + name + '"? As ausências deste membro também serão excluídas.')) return;
            $.ajax({ url: '/api/members.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id: id }) })
                .done(function () { loadMembers(); });
        });
    }

    // ===== Absences =====
    if (typeof PAGE !== 'undefined' && PAGE === 'absences') {
        // Set default dates
        var todayStr = new Date().toISOString().split('T')[0];
        $('#absence-start').val(todayStr);
        $('#absence-end').val(todayStr);

        // Load members for select
        $.get('/api/members.php', function (members) {
            var active = members.filter(function (m) { return m.active; });
            var opts = active.map(function (m) { return '<option value="' + m.id + '">' + $('<span>').text(m.name).html() + '</option>'; }).join('');
            $('#absence-member').html(opts || '<option value="">Nenhum membro ativo</option>');
        });

        function loadAbsences() {
            $.get('/api/absences.php', function (absences) {
                if (!absences.length) {
                    $('#absences-body').html('<tr><td colspan="4" class="loading">Nenhuma ausência cadastrada.</td></tr>');
                    return;
                }
                var rows = absences.map(function (a) {
                    return '<tr>'
                        + '<td>' + $('<span>').text(a.member_name).html() + '</td>'
                        + '<td>' + formatDate(a.start_date) + '</td>'
                        + '<td>' + formatDate(a.end_date) + '</td>'
                        + '<td><button class="btn btn-sm btn-danger btn-del-absence" data-id="' + a.id + '">Remover</button></td>'
                        + '</tr>';
                }).join('');
                $('#absences-body').html(rows);
            });
        }

        loadAbsences();

        $('#btn-save-absence').on('click', function () {
            hideAlert('#absence-form-error');
            var memberId = $('#absence-member').val();
            var start = $('#absence-start').val();
            var end = $('#absence-end').val();
            if (!memberId) { showAlert('#absence-form-error', 'Selecione um membro'); return; }
            if (!start || !end) { showAlert('#absence-form-error', 'Informe as datas'); return; }
            if (start > end) { showAlert('#absence-form-error', 'Data inicial não pode ser maior que a final'); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/absences.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ member_id: memberId, start_date: start, end_date: end }) })
                .done(function () { loadAbsences(); })
                .fail(function (xhr) { showAlert('#absence-form-error', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); })
                .always(function () { btn.prop('disabled', false); });
        });

        $(document).on('click', '.btn-del-absence', function () {
            var id = $(this).data('id');
            if (!confirm('Remover esta ausência?')) return;
            $.ajax({ url: '/api/absences.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id: id }) })
                .done(function () { loadAbsences(); });
        });
    }

    // ===== Holidays =====
    if (typeof PAGE !== 'undefined' && PAGE === 'holidays') {
        // Load workdays
        $.get('/api/workdays.php', function (data) {
            var days = data.days || [1, 2, 3, 4, 5];
            $('#workdays-form input[type=checkbox]').each(function () {
                $(this).prop('checked', days.indexOf(parseInt($(this).val())) !== -1);
            });
        });

        $('#btn-save-workdays').on('click', function () {
            var days = [];
            $('#workdays-form input[type=checkbox]:checked').each(function () { days.push(parseInt($(this).val())); });
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/workdays.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ days: days }) })
                .done(function () { showAlert('#workdays-msg', 'Dias úteis salvos!', 'success'); setTimeout(function () { hideAlert('#workdays-msg'); }, 2000); })
                .fail(function () { showAlert('#workdays-msg', 'Erro ao salvar', 'error'); })
                .always(function () { btn.prop('disabled', false); });
        });

        var loadHolidays = function () {
            $.get('/api/holidays.php', function (holidays) {
                if (!holidays.length) {
                    $('#holidays-body').html('<tr><td colspan="3" class="loading">Nenhum feriado cadastrado.</td></tr>');
                    return;
                }
                var rows = holidays.map(function (h) {
                    return '<tr>'
                        + '<td>' + formatDate(h.date) + '</td>'
                        + '<td>' + $('<span>').text(h.name).html() + '</td>'
                        + '<td><button class="btn btn-sm btn-danger btn-del-holiday" data-id="' + h.id + '">Remover</button></td>'
                        + '</tr>';
                }).join('');
                $('#holidays-body').html(rows);
            });
        };

        loadHolidays();

        $('#btn-add-holiday').on('click', function () {
            hideAlert('#holiday-form-error');
            var date = $('#holiday-date').val();
            var name = $('#holiday-name').val().trim();
            if (!date) { showAlert('#holiday-form-error', 'Informe a data'); return; }
            if (!name) { showAlert('#holiday-form-error', 'Informe o nome do feriado'); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/holidays.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ date: date, name: name }) })
                .done(function () { $('#holiday-date').val(''); $('#holiday-name').val(''); loadHolidays(); })
                .fail(function (xhr) { showAlert('#holiday-form-error', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); })
                .always(function () { btn.prop('disabled', false); });
        });

        $(document).on('click', '.btn-del-holiday', function () {
            var id = $(this).data('id');
            if (!confirm('Remover este feriado?')) return;
            $.ajax({ url: '/api/holidays.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id: id }) })
                .done(function () { loadHolidays(); });
        });

        // ---- Importar feriados nacionais ----
        var fetchedHolidays = [];

        var closeImportModal = function () {
            $('#modal-import').hide();
            fetchedHolidays = [];
        };

        $('#btn-fetch-holidays').on('click', function () {
            var year = $('#import-year').val();
            var btn = $(this).prop('disabled', true).text('Buscando...');
            $('#import-status').hide();

            $.get('/api/import_holidays.php', { year: year })
                .done(function (res) {
                    fetchedHolidays = res.holidays || [];
                    if (!fetchedHolidays.length) {
                        $('#import-status').html('<div class="alert alert-warning">Nenhum feriado encontrado para ' + year + '.</div>').show();
                        return;
                    }

                    // Montar lista no modal
                    $('#modal-import-year').text(year);
                    hideAlert('#import-modal-error');

                    var items = fetchedHolidays.map(function (h, idx) {
                        var disabled = h.imported ? ' disabled' : '';
                        var checked  = h.imported ? ' checked' : ' checked'; // pré-seleciona todos não importados
                        if (h.imported) checked = ' checked'; // já importado: checked mas disabled
                        var cls = h.imported ? ' already-imported' : '';
                        var badge = h.imported ? '<span class="ih-badge">já importado</span>' : '';
                        return '<label class="import-holiday-item' + cls + '">'
                            + '<input type="checkbox" class="ih-check"' + checked + disabled + ' data-idx="' + idx + '">'
                            + '<span class="ih-date">' + formatDate(h.date) + '</span>'
                            + '<span class="ih-name">' + $('<span>').text(h.name).html() + '</span>'
                            + badge
                            + '</label>';
                    }).join('');

                    $('#import-holiday-list').html(items);
                    $('#modal-import').css('display', 'flex');
                })
                .fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Erro ao buscar feriados. Verifique se o servidor tem acesso à internet.';
                    $('#import-status').html('<div class="alert alert-error">' + $('<span>').text(msg).html() + '</div>').show();
                })
                .always(function () {
                    btn.prop('disabled', false).html(
                        '<svg width="15" height="15" viewBox="0 0 15 15" fill="none" style="flex-shrink:0"><path d="M7.5 1v2M7.5 12v2M1 7.5h2M12 7.5h2M3.1 3.1l1.4 1.4M10.5 10.5l1.4 1.4M3.1 11.9l1.4-1.4M10.5 4.5l1.4-1.4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="7.5" cy="7.5" r="3" stroke="currentColor" stroke-width="1.4"/></svg> Buscar Feriados'
                    );
                });
        });

        // Selecionar/Desmarcar todos (apenas os não importados)
        $('#btn-select-all').on('click', function () {
            $('#import-holiday-list .ih-check:not(:disabled)').prop('checked', true);
        });
        $('#btn-deselect-all').on('click', function () {
            $('#import-holiday-list .ih-check:not(:disabled)').prop('checked', false);
        });

        // Confirmar importação
        $('#btn-confirm-import').on('click', function () {
            hideAlert('#import-modal-error');
            var selected = [];
            $('#import-holiday-list .ih-check:checked:not(:disabled)').each(function () {
                var idx = parseInt($(this).data('idx'));
                if (fetchedHolidays[idx]) selected.push(fetchedHolidays[idx]);
            });

            if (!selected.length) {
                showAlert('#import-modal-error', 'Selecione ao menos um feriado para importar.', 'error');
                return;
            }

            var btn = $(this).prop('disabled', true).text('Importando...');
            $.ajax({
                url: '/api/import_holidays.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ holidays: selected })
            })
            .done(function (res) {
                closeImportModal();
                loadHolidays();
                var msg = res.added + ' feriado(s) importado(s) com sucesso!';
                if (res.skipped) msg += ' (' + res.skipped + ' ignorado(s) por já existirem)';
                $('#import-status').html('<div class="alert alert-success">' + msg + '</div>').show();
                setTimeout(function () { $('#import-status').fadeOut(); }, 4000);
            })
            .fail(function (xhr) {
                showAlert('#import-modal-error', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro ao importar', 'error');
            })
            .always(function () {
                btn.prop('disabled', false).html(
                    '<svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M2.5 7.5l3.5 3.5 6.5-6.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg> Importar Selecionados'
                );
            });
        });

        // Fechar modal
        $('#btn-close-import, #btn-cancel-import').on('click', closeImportModal);
        $('#modal-import').on('click', function (e) {
            if ($(e.target).is('#modal-import')) closeImportModal();
        });
    }

    // ===== History =====
    if (typeof PAGE !== 'undefined' && PAGE === 'history') {
        var historyOffset = 0;
        var historyLimit = 30;
        var historyTotal = 0;
        var weekdayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        var monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        var calNow = new Date();
        var calYear = calNow.getFullYear();
        var calMonth = calNow.getMonth();

        var pad = function (n) { return String(n).padStart(2, '0'); };

        var getWeekdayName = function (dateStr) {
            var d = new Date(dateStr + 'T12:00:00');
            return weekdayNames[d.getDay()];
        };

        var escAttr = function (s) { return $('<span>').text(s).html().replace(/"/g, '&quot;'); };

        var buildCalendar = function () {
            var monthStr = calYear + '-' + pad(calMonth + 1);
            $('#cal-month-label').text(monthNames[calMonth] + ' ' + calYear);

            $.get('/api/history.php', { month: monthStr, limit: 200 }, function (res) {
                var dateMap = {};
                res.items.forEach(function (h) {
                    if (!dateMap[h.date]) dateMap[h.date] = [];
                    dateMap[h.date].push(h);
                });

                var todayStr = calNow.getFullYear() + '-' + pad(calNow.getMonth() + 1) + '-' + pad(calNow.getDate());
                var firstDay = new Date(calYear, calMonth, 1).getDay();
                var daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
                var html = '';

                for (var i = 0; i < firstDay; i++) {
                    html += '<div class="cal-day cal-empty"></div>';
                }

                for (var d = 1; d <= daysInMonth; d++) {
                    var dateStr = calYear + '-' + pad(calMonth + 1) + '-' + pad(d);
                    var entries = dateMap[dateStr] || [];
                    var cls = 'cal-day';
                    var tooltip = '';

                    if (dateStr === todayStr) cls += ' cal-today';
                    if (entries.length) {
                        var hasPresented = entries.some(function (e) { return e.action === 'presented'; });
                        cls += hasPresented ? ' cal-has-presented' : ' cal-has-skipped';
                        tooltip = entries.map(function (e) {
                            return e.member_name + (e.action === 'presented' ? ' ✓' : ' ⏭');
                        }).join(', ');
                    }

                    html += '<div class="' + cls + '"'
                        + (tooltip ? ' data-tooltip="' + escAttr(tooltip) + '"' : '')
                        + '><span class="cal-day-num">' + d + '</span></div>';
                }

                $('#cal-grid').html(html);
            });
        };

        $('#cal-prev').on('click', function () {
            calMonth--;
            if (calMonth < 0) { calMonth = 11; calYear--; }
            buildCalendar();
        });
        $('#cal-next').on('click', function () {
            calMonth++;
            if (calMonth > 11) { calMonth = 0; calYear++; }
            buildCalendar();
        });

        buildCalendar();

        // ---- Tabela de histórico ----
        var loadHistory = function (append) {
            $.get('/api/history.php', { limit: historyLimit, offset: historyOffset }, function (res) {
                historyTotal = res.total;
                if (!res.items.length && !append) {
                    $('#history-body').html('');
                    $('#history-empty').show();
                    $('#history-load-more').hide();
                    return;
                }
                var rows = res.items.map(function (h) {
                    var badge = h.action === 'presented'
                        ? '<span class="badge badge-presented">✓ Apresentou</span>'
                        : '<span class="badge badge-skipped">⏭ Pulado</span>';
                    return '<tr>'
                        + '<td>' + formatDate(h.date) + '</td>'
                        + '<td>' + getWeekdayName(h.date) + '</td>'
                        + '<td>' + $('<span>').text(h.member_name).html() + '</td>'
                        + '<td>' + badge + '</td>'
                        + '</tr>';
                }).join('');
                if (append) {
                    $('#history-body').append(rows);
                } else {
                    $('#history-body').html(rows);
                    $('#history-empty').hide();
                }
                historyOffset += res.items.length;
                if (historyOffset < historyTotal) {
                    $('#history-load-more').show();
                } else {
                    $('#history-load-more').hide();
                }
            }).fail(function () {
                $('#history-body').html('<tr><td colspan="4" class="loading">Erro ao carregar histórico.</td></tr>');
            });
        };

        loadHistory(false);
        $('#btn-load-more').on('click', function () { loadHistory(true); });
    }

    // ===== Retro =====
    if (typeof PAGE !== 'undefined' && PAGE === 'retro') {
        var retroMembers = [];
        var retroData = { categories: [], items: [] };

        var loadRetroMembers = function () {
            $.get('/api/members.php', function (members) {
                retroMembers = members.filter(function (m) { return m.active; });
            });
        };

        var memberOptions = function () {
            var opts = '<option value="">— Nenhum —</option>';
            retroMembers.forEach(function (m) {
                opts += '<option value="' + m.id + '" data-name="' + $('<div>').text(m.name).html() + '">' + $('<div>').text(m.name).html() + '</option>';
            });
            return opts;
        };

        var renderItem = function (item, readOnly) {
            var meta = item.member_name ? '@' + item.member_name : '';
            return '<div class="retro-item" data-item-id="' + item.id + '">' +
                '<div class="retro-item-body">' +
                '<div class="retro-item-text">' + $('<div>').text(item.text).html() + '</div>' +
                (meta ? '<div class="retro-item-meta">' + $('<div>').text(meta).html() + '</div>' : '') +
                '</div>' +
                (!readOnly ? '<button class="btn btn-sm btn-danger btn-delete-item" data-id="' + item.id + '" title="Remover">×</button>' : '') +
                '</div>';
        };

        var renderBoard = function (data, readOnly) {
            retroData = data;
            var $board = $('#retro-board');
            if (!data.categories.length) {
                $board.html('');
                $('#retro-board-empty').show();
                return;
            }
            $('#retro-board-empty').hide();

            var html = '';
            data.categories.forEach(function (cat) {
                var catItems = data.items.filter(function (i) { return i.category_id === cat.id; });
                var itemsHtml = catItems.map(function (i) { return renderItem(i, readOnly); }).join('');
                html += '<div class="retro-column" data-cat-id="' + cat.id + '">' +
                    '<div class="retro-col-header">' +
                    '<span class="retro-col-title">' + $('<div>').text(cat.name).html() + '</span>' +
                    (!readOnly ? '<button class="btn btn-sm btn-danger btn-delete-category" data-id="' + cat.id + '" title="Remover categoria">×</button>' : '') +
                    '</div>' +
                    '<div class="retro-items">' + itemsHtml + '</div>' +
                    (!readOnly ?
                        '<div class="retro-add-section">' +
                        '<button class="retro-add-btn btn-show-add-form" data-cat-id="' + cat.id + '">+ Adicionar item</button>' +
                        '<div class="retro-inline-form">' +
                        '<textarea class="form-control retro-item-text-input" placeholder="Descreva o item..." rows="3"></textarea>' +
                        '<select class="form-control retro-item-member-select">' + memberOptions() + '</select>' +
                        '<div class="form-row">' +
                        '<button class="btn btn-primary btn-sm btn-save-item" data-cat-id="' + cat.id + '">Salvar</button>' +
                        '<button class="btn btn-secondary btn-sm btn-cancel-item">Cancelar</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>'
                        : '') +
                    '</div>';
            });
            $board.html(html);
        };

        var showActiveBoard = function () {
            $('#retro-session-header').hide();
            $('#btn-new-category, #btn-new-retro').show();
        };

        var showSessionHeader = function (session) {
            var d = session.date ? session.date.split('-') : [];
            var dateStr = d.length === 3 ? d[2] + '/' + d[1] + '/' + d[0] : session.date;
            $('#retro-session-title').text(session.title);
            $('#retro-session-date').text(dateStr);
            $('#retro-session-header').show();
            $('#btn-new-category, #btn-new-retro').hide();
        };

        var loadRetro = function () {
            showActiveBoard();
            $('#retro-board').html('<div class="loading">Carregando...</div>');
            $.get('/api/retro.php')
                .done(function (res) { renderBoard(res, false); })
                .fail(function () { $('#retro-board').html('<p style="color:var(--danger)">Erro ao carregar</p>'); });
        };

        var renderSessionItem = function (s) {
            var d = s.date ? s.date.split('-') : [];
            var dateStr = d.length === 3 ? d[2] + '/' + d[1] + '/' + d[0] : s.date;
            var count = s.item_count || 0;
            return '<div class="retro-session-row" data-session-id="' + s.id + '">' +
                '<div>' +
                '<span class="retro-session-row-title">' + $('<div>').text(s.title).html() + '</span>' +
                '<span class="retro-session-meta" style="margin-left:10px">' + dateStr + '</span>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:12px">' +
                '<span class="retro-session-meta">' + count + ' ' + (count === 1 ? 'item' : 'itens') + '</span>' +
                '<span style="color:var(--gray-400)">›</span>' +
                '</div>' +
                '</div>';
        };

        var loadSessions = function () {
            $.get('/api/retro.php?type=sessions', function (sessions) {
                var $section = $('#retro-sessions-section');
                var $list    = $('#retro-sessions-list');
                if (!sessions.length) { $section.hide(); return; }
                $list.html(sessions.map(renderSessionItem).join(''));
                $section.show();
            });
        };

        var loadRetroSession = function (id) {
            $('#retro-board').html('<div class="loading">Carregando...</div>');
            $('#retro-board-empty').hide();
            $.get('/api/retro.php?session_id=' + id)
                .done(function (res) {
                    showSessionHeader(res.session);
                    renderBoard(res, true);
                })
                .fail(function () { $('#retro-board').html('<p style="color:var(--danger)">Erro ao carregar sessão</p>'); });
        };

        // New category form toggle
        $('#btn-new-category').on('click', function () {
            $('#retro-new-category-form').show();
            $('#retro-cat-name').focus();
        });
        $('#btn-cancel-category').on('click', function () {
            $('#retro-new-category-form').hide();
            $('#retro-cat-name').val('');
        });

        $('#btn-save-category').on('click', function () {
            var name = $('#retro-cat-name').val().trim();
            if (!name) return;
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/retro.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ type: 'category', name: name }) })
                .done(function () {
                    $('#retro-new-category-form').hide();
                    $('#retro-cat-name').val('');
                    loadRetro();
                })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.error) || 'Erro ao criar categoria'); })
                .always(function () { btn.prop('disabled', false); });
        });

        // Show inline add form
        $(document).on('click', '.btn-show-add-form', function () {
            var $section = $(this).closest('.retro-add-section');
            $(this).hide();
            $section.find('.retro-inline-form').css('display', 'flex');
            $section.find('.retro-item-text-input').focus();
        });

        // Cancel inline form
        $(document).on('click', '.btn-cancel-item', function () {
            var $section = $(this).closest('.retro-add-section');
            $section.find('.retro-inline-form').hide();
            $section.find('.retro-item-text-input').val('');
            $section.find('.retro-item-member-select').val('');
            $section.find('.btn-show-add-form').show();
        });

        // Save item
        $(document).on('click', '.btn-save-item', function () {
            var $section = $(this).closest('.retro-add-section');
            var catId = $(this).data('cat-id');
            var text = $section.find('.retro-item-text-input').val().trim();
            var $sel = $section.find('.retro-item-member-select');
            var memberId = $sel.val();
            var memberName = $sel.find('option:selected').data('name') || '';
            if (!text) { $section.find('.retro-item-text-input').focus(); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/retro.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ type: 'item', category_id: catId, text: text, member_id: memberId, member_name: memberName }) })
                .done(function () { loadRetro(); loadSessions(); })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.error) || 'Erro ao salvar item'); })
                .always(function () { btn.prop('disabled', false); });
        });

        // Delete item
        $(document).on('click', '.btn-delete-item', function () {
            var id = $(this).data('id');
            if (!confirm('Remover este item?')) return;
            $.ajax({ url: '/api/retro.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ type: 'item', id: id }) })
                .done(function () { loadRetro(); loadSessions(); })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); });
        });

        // Delete category
        $(document).on('click', '.btn-delete-category', function () {
            var id = $(this).data('id');
            if (!confirm('Remover esta categoria e todos os seus itens?')) return;
            $.ajax({ url: '/api/retro.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ type: 'category', id: id }) })
                .done(function () { loadRetro(); })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); });
        });

        // Enter key on category name input
        $('#retro-cat-name').on('keydown', function (e) {
            if (e.key === 'Enter') $('#btn-save-category').trigger('click');
            if (e.key === 'Escape') $('#btn-cancel-category').trigger('click');
        });

        // Open "Encerrar Retro" modal
        $('#btn-new-retro').on('click', function () {
            var today = new Date().toISOString().split('T')[0];
            $('#retro-session-date-input').val(today);
            $('#retro-session-title-input').val('');
            $('#modal-new-retro').show();
            $('#retro-session-title-input').focus();
        });

        $('#btn-cancel-new-retro').on('click', function () {
            $('#modal-new-retro').hide();
        });

        // Close modal on overlay click
        $('#modal-new-retro').on('click', function (e) {
            if ($(e.target).is('#modal-new-retro')) $(this).hide();
        });

        // Confirm new retro session
        $('#btn-confirm-new-retro').on('click', function () {
            var title = $('#retro-session-title-input').val().trim();
            var date  = $('#retro-session-date-input').val();
            if (!title) { $('#retro-session-title-input').focus(); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/retro.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ type: 'session', title: title, date: date }) })
                .done(function () {
                    $('#modal-new-retro').hide();
                    loadRetro();
                    loadSessions();
                })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.error) || 'Erro ao encerrar retro'); })
                .always(function () { btn.prop('disabled', false); });
        });

        // Enter key on session title input
        $('#retro-session-title-input').on('keydown', function (e) {
            if (e.key === 'Enter') $('#btn-confirm-new-retro').trigger('click');
            if (e.key === 'Escape') $('#btn-cancel-new-retro').trigger('click');
        });

        // Click on past session → read-only view
        $(document).on('click', '.retro-session-row', function () {
            var id = $(this).data('session-id');
            loadRetroSession(id);
        });

        // Back to active board
        $('#btn-back-to-active').on('click', function () {
            loadRetro();
        });

        loadRetroMembers();
        loadRetro();
        loadSessions();
    }

    // ===== Code Review =====
    if (typeof PAGE !== 'undefined' && PAGE === 'code-review') {
        var crMembers = [];

        var loadCrMembers = function () {
            $.get('/api/members.php', function (members) {
                crMembers = members.filter(function (m) { return m.active; });
                var opts = '<option value="">— Nenhum —</option>';
                crMembers.forEach(function (m) {
                    opts += '<option value="' + m.id + '">' + $('<div>').text(m.name).html() + '</option>';
                });
                $('#cr-author-select').html(opts);
            });
        };

        var loadCrHistory = function () {
            $.get('/api/code_review.php', function (reviews) {
                if (!reviews.length) {
                    $('#cr-history-body').html('<tr><td colspan="4" class="loading">Nenhum sorteio realizado ainda.</td></tr>');
                    return;
                }
                var rows = reviews.map(function (r) {
                    var d = r.date ? r.date.split('-') : [];
                    var dateStr = d.length === 3 ? d[2] + '/' + d[1] + '/' + d[0] : (r.date || '');
                    var pr = r.pr_title ? $('<span>').text(r.pr_title).html() : '<span style="color:var(--gray-500)">—</span>';
                    var author = r.author_name ? $('<span>').text(r.author_name).html() : '<span style="color:var(--gray-500)">—</span>';
                    var reviewer = $('<span>').text(r.reviewer_name).html();
                    return '<tr><td>' + dateStr + '</td><td>' + pr + '</td><td>' + author + '</td><td><strong>' + reviewer + '</strong></td></tr>';
                }).join('');
                $('#cr-history-body').html(rows);
            }).fail(function () {
                $('#cr-history-body').html('<tr><td colspan="4" class="loading">Erro ao carregar histórico.</td></tr>');
            });
        };

        var startAnimation = function (names, finalName, onDone) {
            var $el = $('#cr-result-name');
            var duration = 1800;
            var interval = 80;
            var elapsed = 0;
            $('#cr-result-card').show();
            $el.addClass('cr-spinning');

            var timer = setInterval(function () {
                elapsed += interval;
                // Slow down in the last 600ms
                if (elapsed > duration - 600) {
                    interval = 180;
                }
                if (elapsed >= duration) {
                    clearInterval(timer);
                    $el.removeClass('cr-spinning').text(finalName);
                    if (onDone) onDone();
                    return;
                }
                var randomName = names[Math.floor(Math.random() * names.length)];
                $el.text(randomName);
            }, interval);
        };

        $('#btn-draw-cr').on('click', function () {
            hideAlert('#cr-draw-msg');
            var authorId = $('#cr-author-select').val();
            var prTitle  = $('#cr-pr-title').val().trim();
            var btn = $(this).prop('disabled', true).text('Sorteando...');
            $('#cr-result-card').removeClass('cr-result-card--winner');

            $.ajax({
                url: '/api/code_review.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'draw', pr_title: prTitle, author_id: authorId })
            })
            .done(function (res) {
                var allNames = crMembers.map(function (m) { return m.name; });
                if (allNames.length === 0) allNames = [res.reviewer.name];
                startAnimation(allNames, res.reviewer.name, function () {
                    $('#cr-result-card').css('display', 'flex').addClass('cr-result-card--winner');
                    loadCrHistory();
                });
            })
            .fail(function (xhr) {
                showAlert('#cr-draw-msg', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro ao sortear', 'error');
                $('#cr-result-card').hide();
            })
            .always(function () {
                btn.prop('disabled', false).text('🎲 Sortear Revisor');
            });
        });

        loadCrMembers();
        loadCrHistory();
    }

    // ===== Settings =====
    if (typeof PAGE !== 'undefined' && PAGE === 'settings') {
        $('#btn-save-password').on('click', function () {
            hideAlert('#settings-msg');
            var curr = $('#current-password').val();
            var next = $('#new-password').val();
            var confirm = $('#confirm-password').val();
            if (!curr || !next) { showAlert('#settings-msg', 'Preencha todos os campos', 'error'); return; }
            if (next.length < 4) { showAlert('#settings-msg', 'Nova senha muito curta (mín. 4 caracteres)', 'error'); return; }
            if (next !== confirm) { showAlert('#settings-msg', 'As senhas não coincidem', 'error'); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({ url: '/api/settings.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ current_password: curr, new_password: next }) })
                .done(function () {
                    showAlert('#settings-msg', 'Senha alterada com sucesso!', 'success');
                    $('#current-password,#new-password,#confirm-password').val('');
                })
                .fail(function (xhr) { showAlert('#settings-msg', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro', 'error'); })
                .always(function () { btn.prop('disabled', false); });
        });

        // Rotation config — load members then load current config
        var loadRotationMembers = function (currentMemberId) {
            $.get('/api/members.php', function (members) {
                var active = members.filter(function (m) { return m.active; });
                if (!active.length) {
                    $('#rotation-member').html('<option value="">Nenhum membro ativo</option>');
                    return;
                }
                var opts = active.map(function (m, i) {
                    var sel = m.id === currentMemberId ? ' selected' : '';
                    return '<option value="' + m.id + '"' + sel + '>' + (i + 1) + '. ' + $('<span>').text(m.name).html() + '</option>';
                }).join('');
                $('#rotation-member').html(opts);
            });
        };

        $.get('/api/settings.php', function (cfg) {
            if (cfg.anchor_date) $('#rotation-date').val(cfg.anchor_date);
            loadRotationMembers(cfg.anchor_member_id || '');
        }).fail(function () { loadRotationMembers(''); });

        $('#btn-save-rotation').on('click', function () {
            hideAlert('#rotation-msg');
            var date     = $('#rotation-date').val();
            var memberId = $('#rotation-member').val();
            if (!date)     { showAlert('#rotation-msg', 'Selecione a data de início', 'error'); return; }
            if (!memberId) { showAlert('#rotation-msg', 'Selecione o primeiro apresentador', 'error'); return; }
            var btn = $(this).prop('disabled', true);
            $.ajax({
                url: '/api/settings.php', method: 'POST', contentType: 'application/json',
                data: JSON.stringify({ action: 'rotation', anchor_date: date, anchor_member_id: memberId })
            })
                .done(function () { showAlert('#rotation-msg', 'Rotação configurada com sucesso!', 'success'); })
                .fail(function (xhr) { showAlert('#rotation-msg', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro', 'error'); })
                .always(function () { btn.prop('disabled', false); });
        });
    }

});
