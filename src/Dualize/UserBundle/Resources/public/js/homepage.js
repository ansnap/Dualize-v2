$(document).ready(function() {
    if (typeof (_USER_ID) !== 'undefined') { // If logged user
        /* Recent posts*/
        function onPostRecieve(topicUri, msg) {
            if (topicUri === 'forum_posts') {
                $(msg).addClass('new').prependTo($('.homepage .forum-posts'));
                msg = $('.homepage .forum-posts .new');

                if ($(window).scrollTop() !== 0 || $('.homepage .forum-posts textarea').length > 0) {
                    $(window).scrollTop($(window).scrollTop() + msg.outerHeight(true) - 1);
                }

                // focus - for highlight effect
                msg.focus().removeClass('new');
            }
        }

        subscribeToSubject('forum_posts', onPostRecieve); // New messages

        function loadMorePosts() {
            var posts = $('.homepage .forum-posts');

            $(document).on('scroll.loadMore', function() { // loadMore - it's just namespace to remove event handler
                if ($(window).scrollTop() + $(window).height() > $(document).height() - 30
                        && posts.find('> li.last').length !== 1) {

                    var url = posts.data('action-url');
                    var offset = posts.find('> li').length;

                    if (offset) {
                        url += '/' + offset;
                    }

                    $(document).off('scroll.loadMore');
                    posts.append('<li class="loading"></li>');

                    $.get(url, function(data) {
                        posts.append(data).find('.loading').remove();
                        loadMorePosts();
                    });
                }
            });
        }

        loadMorePosts();

        // Reply
        $(document).on('click', '.homepage .forum-posts [href="#reply"]', function() {
            var url = $(this).data('action-url');
            var formWrap = $(this).closest('.post-controls').siblings('.reply-form');
            formWrap.html('').addClass('loading');

            $.get(url, function(data) {
                formWrap.append(data).removeClass('loading');
                formWrap.find('textarea').focus().val('[b]' + formWrap.siblings('.user-name').text() + '[/b], ');
                $('<button type="button" class="btn btn-default">Отмена</button>').insertAfter(formWrap.find('.btn-primary')).on('click', function() {
                    $('#bbcode-panel').hide().prependTo('body');
                    formWrap.html('');
                });

                formWrap.on('submit', function() {
                    var error = false;
                    var btn = formWrap.find('.btn-primary');

                    btn.button('loading');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formWrap.find('form').serialize(),
                        success: function(data) {
                            if (data === 'Success') {
                                formWrap.text('Ваше сообщение размещено');
                            } else {
                                error = true;
                            }
                        },
                        error: function(msg) {
                            error = true;
                        },
                        complete: function(msg) {
                            if (error) {
                                formWrap.text('Ошибка отправки сообщения. Повторите позже');
                            }
                            btn.button('reset');
                        }
                    });

                    return false;
                });
            });

            return false;
        });

        /* Who is online */
        $('.whoisonline').tooltip({
            selector: 'img'
        });

        var expanded = false;

        $('.whoisonline span').on('click', function() {
            $('.whoisonline a').removeClass('hidden');
            $(this).hide();
            expanded = true;
        });

        function whoIsOnlineUpdate() {
            var url = $('.whoisonline').data('action-url');

            $('.whoisonline').load(url);

            if (expanded) {
                $('.whoisonline span').trigger('click');
            }
        }

        setInterval(whoIsOnlineUpdate, 3 * 60 * 1000);
    }
});
