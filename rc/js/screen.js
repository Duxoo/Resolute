let screen_position;

$(document).ready(function () {
    function screenAddInit() {
        $(".js-screen-add-element").click(function () {
            if (checkForm($(this).closest('.js-required-parent'))) {
                let type = parseInt($("[name='screen_type']:checked").val());
                let name_visible = $(".js-name:visible option:selected"); // for select2
                let entity_id = 0;
                let text = "";

                // if select2
                if (type === 3 || type === 2) {
                    text = name_visible.text();
                    entity_id = parseInt(name_visible.val());
                    // if title, not void screen
                } else if (type === 1){
                    text = $("[name='screen_title_text']").val();
                }

                screen_position.data("type", type);
                screen_position.data("entity", entity_id);
                screen_position.data("text", text);
                screen_position.find(".js-screen-name-text").text(text);

                // show/hide icons depends on type
                // not void screen
                if (type !== 0) {
                    screen_position.find(".js-screen-edit").show();
                    screen_position.find(".js-screen-add").hide();
                } else {
                    screen_position.find(".js-screen-edit").hide();
                    screen_position.find(".js-screen-add").show();
                }
                rc_popup.close();
            }
        });
        // fill data from on open edit form
        let screen_position_type = screen_position.data("type");
        let screen_block_type = $("[data-type='" + screen_position_type + "']");
        $("[name='screen_type'][value='" + screen_position_type + "']").prop("checked", true);
        // if select2
        if (screen_position_type === 3 || screen_position_type === 2) {
            screen_block_type.find(".js-name").append("<option value='"+screen_position.data("entity")+"' selected='selected'>"+screen_position.data("text")+"</option>");
            screen_block_type.find(".js-name").trigger("change");
            // if title, not void screen
        } else if (screen_position_type === 1) {
            screen_block_type.find("[name='screen_title_text']").val(screen_position.data("text"));
        }

        $("[name='screen_type']:checked").trigger("change");
    }
    let max_x = 6;
    let min = 1;

    rc_content.off("click", ".js-screen-control-left").on("click", ".js-screen-control-left", function () {
        let li = $(this).closest("li");
        let x = li.data("x");
        if (x > min) {
            x--;
            li.css("width", 16.6666666667 * x + "%");
            li.data("x", x);
        }
        $(".rc-active-tab").addClass("rc-tab-changed");
    });

    rc_content.off("click", ".js-screen-control-top").on("click", ".js-screen-control-top", function () {
        let li = $(this).closest("li");
        let y = li.data("y");
        if (y > min) {
            y--;
            li.css("height", 85 * y);
            li.find(".js-screen-name-block").css("height", (85 * y) - 10);
            li.data("y", y);
        }
        $(".rc-active-tab").addClass("rc-tab-changed");
    });

    rc_content.off("click", ".js-screen-control-right").on("click", ".js-screen-control-right", function () {
        let li = $(this).closest("li");
        let x = li.data("x");
        if (x < max_x) {
            x++;
            li.css("width", 16.6666666667 * x + "%");
            li.data("x", x);
        }
        $(".rc-active-tab").addClass("rc-tab-changed");
    });

    rc_content.off("click", ".js-screen-control-bottom").on("click", ".js-screen-control-bottom", function () {
        let li = $(this).closest("li");
        let y = li.data("y");
        y++;
        li.css("height", 85 * y);
        li.find(".js-screen-name-block").css("height", (85 * y) - 10);
        li.data("y", y);
        $(".rc-active-tab").addClass("rc-tab-changed");
    });

    rc_content.off("click", ".js-screen-add, .js-screen-edit").on("click", ".js-screen-add, .js-screen-edit", function () {
        screen_position = $(this).closest("li");
        $.get('?module=backend&action=popup', {type: "screen", method: "addPosition", data: {id: screen_position.closest('.js-content').data('id')}}, function (Template) {
            setPopup('Укажите назначение', Template);
        }).promise().done(screenAddInit);
        $(".rc-active-tab").addClass("rc-tab-changed");
    });

    rc_content.off("click", ".js-save-screen").on("click", ".js-save-screen",function () {
        let data = "name=" + $("#name").val();
        let id = $("#js_screen_id").val();
        if (id) {
            data += "&id=" + id;
        }
        let i = 0;
        $(".js-screen-position").each(function (index) {
            data += '&p['+index+'][type]=' + $(this).data('type');
            data += '&p['+index+'][name]=' + encodeURIComponent($(this).data('text'));
            data += '&p['+index+'][entity_id]=' + $(this).data('entity');
            data += '&p['+index+'][width]=' + $(this).data('x');
            data += '&p['+index+'][height]=' + $(this).data('y');
            data += '&p['+index+'][sort]=' + i++;
        });
        $.post("?module=screen&action=save", data, function (jData) {
            if (!jData.data.error) {
                window.location.href = "?module=" + rc_module + "&action=edit&id="+jData.data.id;
            }
        });
    });
});