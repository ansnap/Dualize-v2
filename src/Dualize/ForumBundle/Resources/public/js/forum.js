$(document).ready(function() {

    // Reply and quote
    function goToForm(anchor, target, handler) {
        if (typeof target === 'undefined') {
            target = anchor;
        }

        if ($(target).length === 0) {
            target = $('.navigation').eq(1);
        }

        $('[href="' + anchor + '"]').on('click', function() {
            var link = $(this);
            var completeCalled = false;

            $('html, body').animate({
                scrollTop: $(target).offset().top
            }, 200, function() {
                if (!completeCalled) {
                    completeCalled = true;

                    if ($(target).is('input, textarea')) {
                        $(target).focus();
                    }

                    if (typeof handler !== 'undefined') {
                        handler(link);
                    }
                }
            });
            return false;
        });
    }

    goToForm('#topic_new_title');
    goToForm('#post_content');
    goToForm('#reply', '#post_content', insertName);
    goToForm('#quote', '#post_content', insertQuote);

    function insertName(link) {
        var name = $(link).closest('.post').find('.user-name').text();
        $('#post_content').val($('#post_content').val() + '[b]' + name + '[/b], ');
    }

    function insertQuote(link) {
        var msg = $(link).closest('.post').find('.message').clone();
        var name = $(link).closest('.post').find('.user-name').text();

        msg.find('.bbcode-quote').remove();
        msg = msg.text().trim().replace(/\n{3,}/g, '\n\n');
        msg = '[quote=' + name + ']' + msg + '[/quote]\n';

        $('#post_content').val($('#post_content').val() + msg);
    }

    // Edit post
    $('[href="#edit-post"]').on('click', function() {
        var msg = $(this).closest('.content').find('.message');
        var msgHTML = msg.html();
        var url = $(this).data('action-url');
        msg.html('').addClass('loading');

        msg.load(url, function() {
            msg.removeClass('loading');
            formHandler();
        });

        function formHandler() {
            $('<button type="button" class="btn btn-default">Отмена</button>').insertAfter(msg.find('button')).on('click', function() {
                $('#bbcode-panel').hide().prependTo('body');
                msg.html(msgHTML);
            });

            msg.find('textarea').focus();

            msg.find('form').on('submit', function() {
                $.post(url, $(this).serialize(), function(data) {
                    msg.html(data);

                    if ($(data).find('textarea').length) {
                        formHandler();
                    }
                });

                return false;
            });
        }

        return false;
    });

    // Topic and forum actions
    $('#topic, #forum').find('.subactions .alert .close').on('click', function() {
        $(this).parent().hide();
    });

    $('#topic, #forum').find('.dropdown-menu a').on('click', function() {
        $(this).closest('.btn-group').find('> button').dropdown('toggle');
    });

    // Get array of selected item IDs
    function getSelectedItems() {
        var ids = [];

        $('#forum .topic, #topic .post').each(function() {
            // Additional selector if post, because it can contain another checkboxes
            if ($(this).find(($(this).is('.post') ? '.meta ' : '') + 'input[type="checkbox"]').prop('checked')) {
                ids.push($(this).attr('id').substr(1));
            }
        });

        if (ids.length === 0) {
            $('.subactions .alert').show().find('span').text('Сначала отметьте сообщения/темы для удаления/перемещения');
        }

        return ids;
    }

    // Delete posts
    $('[href="#delete-posts"]').on('click', function() {
        var menuBtn = $(this).closest('.btn-group').find('> button');
        var url = $(this).data('action-url');

        $('.subactions .alert').hide();

        var post_ids = getSelectedItems();

        if (post_ids.length > 0) {
            menuBtn.button('loading');

            var error = false;

            $.ajax({
                url: url,
                type: 'POST',
                data: {posts: JSON.stringify(post_ids)},
                success: function(data) {
                    if (data === 'Posts deleted') {
                        for (var id in post_ids) {
                            $('#topic .post#p' + post_ids[id]).remove();
                        }
                    } else if (data.indexOf('Topic deleted') === 0) {
                        // Topic removed, redirect to forum
                        var redirect = data.split('|')[1];
                        window.location.href = redirect;
                    } else {
                        error = true;
                    }
                },
                error: function(msg) {
                    error = true;
                },
                complete: function(msg) {
                    if (error) {
                        $('.subactions .alert').show().find('span').text('Ошибка удаления сообщений. Повторите пожалуйста запрос');
                    }
                    menuBtn.button('reset');
                }
            });
        }

        return false;
    });

    // Yes/No actions
    function actionConfirm(href, handler, hasCheckbox) {
        $('[href="' + href + '"]').on('click', function() {
            if (hasCheckbox && getSelectedItems().length === 0) {
                return false;
            }

            var url = $(this).data('action-url');
            var container = $('.subactions .confirm-action');

            $('.subactions .alert').hide();
            container.show().find('> p span').text($(this).text());

            container.find('button').eq(0).on('click', function() {
                var btn = $(this);
                btn.button('loading');

                handler(url, container, btn);
            });

            container.find('button').eq(1).on('click', function() {
                container.hide();
            });

            return false;
        });
    }

    actionConfirm('#delete-topic', function(url) {
        window.location.href = url;
    });

    actionConfirm('#delete-topics', deleteTopics, true);

    function deleteTopics(url, container, btn) {
        var topic_ids = getSelectedItems();

        if (topic_ids.length > 0) {
            var error = false;

            $.ajax({
                url: url,
                type: 'POST',
                data: {topics: JSON.stringify(topic_ids)},
                success: function(data) {
                    if (data === 'Success') {
                        for (var id in topic_ids) {
                            $('#forum .topic#t' + topic_ids[id]).remove();
                        }
                    } else {
                        error = true;
                    }
                },
                error: function(msg) {
                    error = true;
                },
                complete: function(msg) {
                    if (error) {
                        $('.subactions .alert').show().find('span').text('Ошибка удаления тем. Повторите пожалуйста запрос');
                    }
                    container.hide();
                    btn.button('reset');
                }
            });
        }
    }

    // Actions with loading additional content
    function actionExtra(href, handler, hasCheckbox) {
        $('[href="' + href + '"]').on('click', function() {
            if (hasCheckbox && getSelectedItems().length === 0) {
                return false;
            }

            var url = $(this).data('action-url');
            var container = $('.subactions .extra-action');
            var menuBtn = $(this).closest('.btn-group').find('> button');

            $('.subactions .alert').hide();
            menuBtn.button('loading');

            container.load(url, function() {
                menuBtn.button('reset');

                container.find('button').eq(0).on('click', function() {
                    if (typeof handler !== 'undefined') {
                        // Ajax request
                        var confirmBtn = $(this);

                        handler(url, container, confirmBtn);

                        return false;
                    } else {
                        // HTTP request
                        $(this).button('loading');
                    }
                });

                container.find('button').eq(1).on('click', function() {
                    container.html('');
                });
            });

            return false;
        });
    }

    actionExtra('#move-topic');
    actionExtra('#rename-topic');
    actionExtra('#move-topics', moveTopics, true);

    function moveTopics(url, container, confirmBtn) {
        var topic_ids = getSelectedItems();

        if (topic_ids.length > 0) {
            var forum_id = container.find('select :selected').val();

            confirmBtn.button('loading');

            var error = false;

            $.ajax({
                url: url + forum_id,
                type: 'POST',
                data: {topics: JSON.stringify(topic_ids)},
                success: function(data) {
                    if (data === 'Success') {
                        for (var id in topic_ids) {
                            $('#forum .topic#t' + topic_ids[id]).remove();
                        }
                    } else {
                        error = true;
                    }
                },
                error: function(msg) {
                    error = true;
                },
                complete: function(msg) {
                    if (error) {
                        $('.subactions .alert').show().find('span').text('Ошибка перемещения тем. Повторите пожалуйста запрос');
                    }
                    container.html('');
                    confirmBtn.button('reset');
                }
            });
        }
    }

});