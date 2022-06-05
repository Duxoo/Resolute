let rc_popup = $('.js-popup');
let rc_content = $('#js-content, .js-popup');
let rc_search_params = new URLSearchParams(window.location.search);
let rc_module = rc_search_params.get('module');
let rc_form = $(".js-form");
let rc_current_element = null;
let rc_current_form = null;
let rc_counter = 100;
let rc_data_tables = [];
let rc_tab_reload = false;

(function( $ ) {
    $.fn.fSortable = function(callback) {
        let table = $(this);
        table.find('tbody').sortable( {
            items: "> tr",
            handle: ".js-sort",
            update: function(event, ui) {
                let itemsArray = new Array();
                table.find('.js-sort').each(function(index) {
                    itemsArray.push($(this).attr('data-item'));
                });
                callback(itemsArray);
            }
        });
    };
})(jQuery);

function dataTablesReload() {
    for (let i = 0; i < rc_data_tables.length; i++) {
        if (rc_data_tables[i].data('action')) {
            rc_data_tables[i].fnReloadAjax();
        }
    }
}

function setPopup(title, content_data, re_init) {
    rc_popup.find('.js-popup-header').text(title);
    rc_popup.find('.js-popup-content').html(content_data).promise().done(function(){
        if (re_init) {
            init();
        }
    });
    rc_popup.bPopup(function () {
        $(window).resize();
    });
}

function sortableInit() {
    $('.js-sortable').each(function () {
        let table = $(this);
        table.fSortable(function(items) {
            if (table.data('sort-action')) {
                $.post(table.data('sort-action'), {items: items});
            }
        });
    });
}

function dataTablesInit() {
    $('.js-data-table:not(.dataTable)').each(function () {
        let action = $(this).data('action') ? $(this).data('action') : false;
        let min_length = $(this).data('min-length') ? $(this).data('min-length') : 20;
        let max_length = $(this).data('max-length') ? $(this).data('max-length') : 50;
        rc_data_tables[rc_data_tables.length] = $(this).dataTable({
            "processing": true,
            "responsive": true,
            "stateSave": true,
            "order": [[ 0, "desc" ]],
            "serverSide": action ? action : false,
            "lengthMenu": [[min_length, max_length], [min_length, max_length]],
            "ajax": action,
            "language":{
                "sLengthMenu": "Показывать _MENU_ записей",
                "sZeroRecords": "Нет записей, удовлетворяющих условиям поиска",
                "sInfo": "Отображаются записи с _START_ до _END_ из _TOTAL_",
                "sInfoEmpty": "Отображаются записи с 0 до 0 из 0",
                "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
                "sSearch": "Поиск: ",
                "processing": "Обработка...",
                "oPaginate": { "sNext": ">>", "sPrevious": "<<" }
            }
        });
    });
}

function selectTwoInit() {
    $('.js-select-two:not(.select2-hidden-accessible)').each(function () {
        let that = $(this);
        $(this).select2({
            placeholder: that.data('placeholder'),
            ajax: {
                url: that.data('action'),
                data: function (params) {
                    let val = '';
                    let current_id = that.data('id') ? that.data('id') : that.val();
                    let dependent_id = that.data('dependent') ? that.data('dependent') : null;
                    if (dependent_id) {
                        dependent_id = $('#' + dependent_id).val();
                    }
                    $('[data-group="'+that.data('group')+'"]').each(function () {
                        if ($(this).val() !== 0 && $(this).val() !== null) {
                            if (val.length > 0) {
                                val = val+'+';
                            }
                            val = val+$(this).val();
                        }
                    });
                    return {
                        search: params.term,
                        group_id: val,
                        current_id: current_id,
                        dependent_id: dependent_id,
                        type: 'public'
                    }
                },
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                },
                dataType: 'json'
            }
        });
    });
}

function datepickerInit() {
    $('.js-timepicker').each(function () {
        $(this).timepicker({
            timeFormat: $(this).data('format'),
            interval: 15,
            change: function () {
                $(this).trigger("change")
            }
        });
    });
    $('.js-datepicker').each(function () {
        $(this).datepicker( {
            changeMonth: true,
            changeYear: true,
            dateFormat: $(this).data('format')
        });
        if ($(this).val() === '') {
            $(this).last().datepicker("setDate", new Date());
        }
    });
}

function redactorInit() {
    $('.js-redactor').each(function () {
        if (!$(this).closest('.redactor-box').length) {
            $(this).redactor();
        }
    });
}

function init() {
    dataTablesReload();
    dataTablesInit();
    selectTwoInit();
    datepickerInit();
    redactorInit();
    sortableInit();
}

function hashLoad (hash) {
    let tab = $(".js-tab");
    if (tab.length) {
        if (!hash.length) {
            hash = "main";
            let set = true;
            $('.js-tab-content').each(function () {
                if ($(this).find('.js-tab-content').length === 0 && set) {
                    hash = $(this).data('tab');
                    set = false;
                }
            });
        }
        tab.removeClass("rc-active-tab");
        let active_tab = $(".js-tab[data-tab='" + hash + "']");
        active_tab.addClass("rc-active-tab");
        let tab_block = $('.js-tab-content[data-tab="' + hash + '"]');
        let module = active_tab.closest('.js-tab-block').data('module');
        let obj = {"tab": hash, "type": module ? module : rc_module};
        let id = rc_search_params.get('id');
        if (id) {
            obj.id = parseInt(id);
        }
        if ($.trim(tab_block.html()) === "" || rc_tab_reload) {
            $.get('?module=backend&action=tab', obj, function (Template) {
                tab_block.html(Template).promise().done(function(){
                    init();
                });
                rc_form =  $(".js-form");
            });
            rc_tab_reload = false;
        }
        tab_block.parent().find(">.js-tab-content").hide();
        tab_block.show();
    }
}

function currentHashReload() {
    rc_tab_reload = true;
    let h = window.location.hash.replace('#', '');
    hashLoad(h);
}

function checkForm(form)
{
    let check = true;
    form.find('.rc-required>select, .rc-required>input, .rc-required>textarea').each(function () {
        if ($(this).val() < 0 || $(this).val() === '' || $(this).val() === null) {
            $(this).parent().find('>*').addClass('rc-error-shadow');
            check = false;
        }
    });
    if (!check) {
        let message = 'Есть обязательные поля, которые не заполнены!';
        if (form.closest('.js-popup').length) {
            form.find('.js-form-message').removeClass('rc-success').addClass('rc-error').text(message)
        } else {
            setPopup('Ошибка', '<div class="rc-error">'+message+'</div>');
        }
    }
    return check;
}

$(document).ready(function () {
    // HASH LOAD
    let h = window.location.hash.replace('#', '');
    hashLoad(h);
    init();

    rc_popup = $('.js-popup');
    rc_content = $('#js-content, .js-popup');
    rc_form =  $(".js-form");

    rc_content.off('keyup', 'input[type="number"]').on('keyup', 'input[type="number"]', function () {
        if ($(this).attr('min').length) {
            if ($(this).val().length > 0 && $(this).val() < $(this).attr('min')) {
                $(this).val($(this).attr('min'));
            }
        }
    });

    rc_content.off('click', '.js-add-element').on('click', '.js-add-element', function (e) {
        e.preventDefault();
        let action = $(this).data('action');
        $.post('?module='+rc_module+'&action='+action, getAddParams(action), function (new_element) {
            $('.js-elements[data-type="'+action+'"]').append(new_element).promise().done(function(){
                init();
            });
        });
    });

    rc_content.off('click', '.js-edit-popup').on('click', '.js-edit-popup', function (e) {
        e.preventDefault();
        let that = $(this);
        let data = {
            data: {id: that.closest('.js-row').data('id')},
            method: 'edit',
            type: that.data('type')};
        $.get('?module=backend&action=popup', data, function (Template) {
            setPopup(that.data('title'), Template, true);
        });
    });

    rc_content.off('change', ".js-tab-content .js-form").on('change', ".js-tab-content .js-form", function (e) {
        let tab_content = $(this).closest('.js-tab-content');
        $(".js-tab[data-tab='" + tab_content.data('tab') + "']").addClass("rc-tab-changed");
    });

    rc_content.off('change', '.js-checkbox').on('change', '.js-checkbox', function () {
        let that = $(this);
        let checked = that.prop('checked') ? 1 : 0;
        let data = {
            id: that.closest('.js-content').data('id'),
            child_id: that.closest('.js-row').data('id'),
            type: that.closest('.js-content').data('type'),
            child_type: that.closest('.js-parent').data('type'),
            on: checked};
        $.get('?module=backend&action=checkbox', data, function (Json) {
            if (Json.data.error) {
                that.prop('checked', !checked);
                setPopup('Ошибка!', '<div class="rc-error">'+Json.data.message+'</div>');
            } else {
                setPopup('Успех!', '<div class="rc-success">'+Json.data.message+'</div>');
            }
        }, 'JSON');
    });

    rc_content.off('change', '.js-row-field').on('change', '.js-row-field', function () {
        let that = $(this);
        let data = {
            id: that.closest('.js-content').data('id'),
            child_id: that.closest('.js-row').data('id'),
            type: that.closest('.js-content').data('type'),
            child_type: that.closest('.js-parent').data('type'),
            field: that.attr('name')};
        if (that.attr('type') === 'checkbox' && !that.prop('checked')) {
            if (that.data('default')) {
                data.value = that.data('default');
            } else {
                data.value = 0;
            }
        } else {
            data.value = that.val();
        }
        $.get('?module=backend&action=rowChange', data, function (Json) {
            if (Json.data.error) {
                if (that.attr('type') === 'checkbox') {
                    that.prop('checked', false);
                }
                setPopup('Ошибка!', '<div class="rc-error">'+Json.data.message+'</div>');
            } else {
                setPopup('Успех!', '<div class="rc-success">'+Json.data.message+'</div>');
            }
        }, 'JSON');
    });

    rc_content.off('submit', ".js-form").on('submit', ".js-form", function (e) {
        e.preventDefault();
        let form = $(this);
        if (checkForm(form)) {
            let url = form.attr('action');
            let tab_content = form.closest('.js-tab-content');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize().replace(/[^&]+=\.?(?:&|$)/g, ''),
                success: function(Json) {
                    if (Json.data.error) {
                        if (form.closest('.js-popup').length > 0) {
                            form.find('.js-form-message').removeClass('rc-success').addClass('rc-error').text(Json.data.message);
                        } else {
                            setPopup('Ошибка!', '<div class="rc-error">'+Json.data.message+'</div>');
                        }
                    } else {
                        if (Json.data.reload) {
                            window.location.href = "?module=" + rc_module + "&action=edit&id="+Json.data.id;
                        } else {
                            if (form.closest('.js-popup').length > 0) {
                                form.find('.js-form-message').removeClass('rc-error').addClass('rc-success').text(Json.data.message);
                            } else {
                                $(".rc-active-tab").removeClass("rc-tab-changed");
                                let form_content = form.closest('.js-content');
                                if (form_content.data('id') && form_content.data('type')) {
                                    let method = '';
                                    if (form.data('method')) {
                                        method = '&method='+form.data('method');
                                    }
                                    $.get('?module=backend&action=form', 'id='+form_content.data('id')+'&type='+form_content.data('type')+method, function (Template) {
                                        form_content.html(Template).promise().done(function(){
                                            init();
                                        });
                                    });
                                }
                                if (tab_content.length) {
                                    $(".js-tab[data-tab='" + tab_content.data('tab') + "']").removeClass("rc-tab-changed");
                                }
                                setPopup('Успех!', '<div class="rc-success">'+Json.data.message+'</div>');
                            }
                        }
                    }
                }
            });
        }
    });

    // вызов попапа для подтверждения удаления
    rc_content.off('click', '.js-delete').on('click', '.js-delete', function () {
        let that = $(this);
        let parent = that.closest('.js-content');
        let data = {
            data: {
                id: parent.data('id'),
            },
            type: parent.data('type'),
            method: 'delete'
        };
        $.get('?module=backend&action=popup', data, function (Template) {
            setPopup('Вы подтвержаете удаление?', Template);
        });
    });

    // вызов попапа для подтверждения удаления зависимой сущности
    rc_content.off('click', '.js-delete-dependent').on('click', '.js-delete-dependent', function (e) {
        e.preventDefault();
        let that = $(this);
        let parent = that.closest('.js-content');
        rc_current_element = that.closest('.js-dependent');
        let data = {
            data: {
                id: parent.data('id'),
                dependent_id: rc_current_element.data('id')
            },
            type: parent.data('type'),
            method: that.data('type') + 'Delete'
        };
        if (rc_current_element.data('id')) {
            $.get('?module=backend&action=popup', data, function (Template) {
                setPopup('Вы подтвержаете удаление?', Template);
            });
        } else {
            rc_current_element.remove();
        }
    });

    rc_content.off('click', '.js-add-popup').on('click', '.js-add-popup', function () {
        let that = $(this);
        let parent = that.closest('.js-content');
        rc_current_form = null;
        if (that.closest('form.js-form').length > 0 && !that.data('reload')) {
            rc_current_form = that.closest('form.js-form');
        }
        let data = {
            data: {
                id: parent.data('id')
            },
            type: parent.data('type'),
            method: that.data('type') + 'Add'
        };
        $.get('?module=backend&action=popup', data, function (Template) {
            setPopup('Добавить', Template, true);
        });
    });

    rc_content.off('click', '.js-required-control').on('click', '.js-required-control', function () {
        let that = $(this);
        that.closest('.js-required-parent').find('.js-required-child').removeClass('rc-required');
        that.closest('.js-required-parent').find('.js-required-content[data-tab="'+that.data('tab')+'"] .js-required-child').addClass('rc-required');
    });

    rc_popup.off("submit", ".js-popup-add-form").on("submit", ".js-popup-add-form", function (e) {
        e.preventDefault();
        let form = $(this);
        if (checkForm(form)) {
            $.post($(this).attr('action'), $(this).serialize(), function (Json) {
                if (Json.data.error) {
                    form.find('.js-form-message').removeClass('rc-success').addClass('rc-error');
                } else {
                    form.find('.js-form-message').removeClass('rc-error').addClass('rc-success');
                    rc_popup.bPopup().close();
                    if (rc_current_form) {
                        rc_current_form.submit();
                    } else {
                        currentHashReload();
                    }
                }
                form.find('.js-form-message').html(Json.data.message);
            }, 'JSON');
        }
    });

    rc_content.off('click', '.js-add').on('click', '.js-add', function (e) {
        e.preventDefault();
        let content = $(this).closest('.js-parent');
        let parent = $(this).closest('.js-parent');
        let data = {
            type: $(this).data('type'),
            data: {
                key: rc_counter++,
                id: content.data('id'),
                type: content.data('type')
            }
        };
        $.get('?module=backend&action=addTemplate', data, function (Template) {
            parent.find('.js-parent-content').append(Template).promise().done(function(){
                init();
            });
        });
    });

    // удаление
    rc_popup.off('submit', '.js-delete-form').on('submit', '.js-delete-form', function (e) {
        e.preventDefault();
        let that = $(this);
        $.post($(this).attr('action'), $(this).serialize(), function (jData) {
            if (jData.data.error) {
                setPopup('Ошибка', '<div class="rc-error">'+jData.data.message+'</div>');
            } else {
                if (that.hasClass("js-dependence")) {
                    if (rc_current_element.closest('tr').length) {
                        rc_current_element.closest('tr').remove();//TODO
                    } else {
                        rc_current_element.remove();
                    }
                    rc_popup.bPopup().close();
                } else {
                    window.location.href = "?module=" + rc_module;
                }
            }
        }, 'JSON')
    });

    rc_popup.off("click", ".js-popup-close").on("click", ".js-popup-close", function () {
        rc_popup.bPopup().close();
    });

    rc_content.off('click', '.js-tab').on('click', '.js-tab', function () {
        let tab = $(this).data("tab");
        let tab_block = $(this).closest('.js-tab-block');
        if (tab_block.data('tab-type')) {
            tab_block.parent().find('>.js-tab-content').hide();
            tab_block.parent().find('>.js-tab-content[data-tab="'+tab+'"]').show();
        } else {
            window.location.hash = tab;
            hashLoad(tab);
        }
    });

    //MENU
    $('.js-menu :not(.js-menu-close)').click(function () {
        if (!$(this).closest('.js-menu').parent().hasClass('rc-menu-open')) {
            $(this).closest('.js-menu').parent().addClass('rc-menu-open');
            $('.js-menu-element:not(.rc-background-hover-c-opacity)').find('.js-menu-children').show();
        }
    });
    $('.js-menu-element').click(function () {
        $('.js-menu-children').hide();
        $(this).find('.js-menu-children').show();
    });
    $('.js-menu-close').click(function () {
        $('.js-menu').parent().removeClass('rc-menu-open');
        $('.js-menu-children').hide();
    });

    let rc_checked_unit_id = 0;
    rc_content.off('change', '.js-ingredient-list').on('change', '.js-ingredient-list', function () {
        let ingredient_id = $(this).val();
        let unit_list = $(this).closest('.js-dependent').find('.js-unit');
        if (unit_list.val() > 0) {
            rc_checked_unit_id = unit_list.val();
        }
        $.get('?module=product&action=ingredientUnit', 'ingredient_id='+ingredient_id+'&unit_id='+rc_checked_unit_id, function (Template) {
            unit_list.html(Template);
        });
    });
    $('.js-menu-fixed').click(function () {
        let main = $('.js-main');
        main.toggleClass('rc-menu-opened');
        if (main.hasClass('rc-menu-opened')) {
            $('.js-opened-menu-text').text('Свернуть меню');
        } else {
            $('.js-opened-menu-text').text('Развернуть меню');
        }
    });
});