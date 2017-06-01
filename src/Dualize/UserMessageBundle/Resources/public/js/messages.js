/* Messages page */
var current_dialog_id = 0;
var get_messages_url;
var new_message_url;
var mark_read_url;

function messagesInit(get_messages_path, new_message_path, mark_read_path) {
    get_messages_url = get_messages_path;
    new_message_url = new_message_path;
    mark_read_url = mark_read_path;

    // Load list of messages
    getMessages();
    $(window).on('hashchange', function() {
        getMessages();
    });

    // Add message
    $('.messages').on('submit', 'form[name="message"]', function() {
        newMessage();
        return false;
    });
}

var get_messages_xhr;

function getMessages() {
    // Abort previous request
    if (get_messages_xhr && get_messages_xhr.readyState !== 4) {
        get_messages_xhr.abort();
    }

    current_dialog_id = parseInt(window.location.hash.substring(1));

    $('.dialogs li[data-dialog-id]').removeClass('active');
    $('.dialogs .list-group-item[href="#' + current_dialog_id + '"]').parents('li[data-dialog-id]').addClass('active'); // add active class to dialog

    // If no dialog selected
    if (isNaN(current_dialog_id)) {
        $('.messages .message-info').show();
        $('.messages .message-list').html('').hide();
        return;
    }

    $('.messages .message-info').hide();
    $('.messages .message-list').html('').show().addClass('loading');
    var error = false;

    get_messages_xhr = $.ajax({
        url: get_messages_url + '/' + current_dialog_id,
        type: 'GET',
        success: function(msg) {
            if ($.trim(msg).match(/^\<div[^]+div\>$/)) {
                $('.messages .message-list').html(msg);

                sendOnCtrlEnter('form[name="message"]');
                expandOnTyping('form[name="message"]');
                $('form[name="message"] textarea').focus();

                $('form[name="message"]').find('textarea').keydown(function(e) {
                    if (typeof (sess) !== 'undefined') {
                        sess.publish('typing:' + _USER_ID, JSON.stringify({
                            recipientId: $('.user-is-typing').data('recipient-id'),
                            dialogId: current_dialog_id
                        }));
                    }
                });

                $('.message-counter').text($('.messages .view-messages').data('message-counter')); // update counter
                $('.dialogs .list-group-item[href="#' + current_dialog_id + '"]').parents('[data-dialog-id]').removeClass('new'); // mark dialog as read

                // Update page view
                setElementSizes();
                $(window).scrollTop($(document).height());

                loadMoreMessages();
            } else {
                error = true;
            }
        },
        error: function(msg) {
            error = true;
        },
        complete: function(msg) {
            $('.messages .message-list').removeClass('loading');
            if (error === true) {
                $('.messages .message-list').prepend('<div class="alert alert-danger">Ошибка получения сообщений. Пожалуйста обновите страницу</div>');
            }
        }
    });
}

// Load more messages on scroll
function loadMoreMessages() {
    var has_more_messages = true;

    $('.messages .media').each(function() {
        if ($(this).hasClass('first')) {
            has_more_messages = false;
        }
    });

    // Prevent run scroll event when message area cleared
    $(window).off('hashchange.loadMore').on('hashchange.loadMore', function() {
        $(document).off('scroll.loadMore');
    });

    $(document).on('scroll.loadMore', function() { // loadMore - it's just namespace to remove event handler
        if ($(document).scrollTop() < 1 && has_more_messages) {
            $(document).off('scroll.loadMore');
            $('.messages .loader').addClass('loading');

            var offset = $('.messages .media').length;
            var url = get_messages_url + '/' + current_dialog_id + '/' + offset;

            $.get(url, function(data) {
                // Get position of top message to scroll there after HTML append
                var first_message = $('.messages .media').first().addClass('separated');
                var first_message_offset = $(first_message).offset().top;

                $('.messages .loader').after(data).removeClass('loading');
                $(document).scrollTop($(first_message).offset().top - first_message_offset);

                // Recursive call
                loadMoreMessages();
            });
        }
    });
}

function newMessage() {
    $('.messages .alert-danger').remove(); // clear error messages

    // Check length
    var msg_length = $('form[name="message"] textarea').val().length;

    if (msg_length < 1 || msg_length > 8192) { // length also defined in Message.php
        errorMessage(msg_length < 1 ? 'Сообщение не может быть пустым' : 'Превышена максимальная длина сообщения в 8192 символа');
        return false;
    }

    $('form[name="message"] button').attr('disabled', 'disabled'); // temporary block send button
    var error = false;

    $.ajax({
        url: new_message_url + '/' + current_dialog_id,
        type: 'POST',
        data: $('form[name="message"]').serialize(),
        success: function(msg) {
            if ($.trim(msg).match(/^\<li[^]+li\>$/)) { // check if response contains valid markup
                $('form[name="message"] textarea').val('');
                addNewMessage(msg);
            } else {
                error = true;
            }
        },
        error: function(msg) {
            error = true;
        },
        complete: function(msg) {
            $('form[name="message"] button').removeAttr('disabled'); // unblock submit button
            if (error === true) {
                errorMessage('Ошибка отправки сообщения. Повторите пожалуйста запрос');
            }
        }
    });
}

// Add new message and notify user
var original_title = $('title').text();

function addNewMessage(msg, dialog_id) {
    if (typeof dialog_id !== 'undefined') {
        // from realtime server, i.e. from remote user

        // Add flag to <title>
        var event_mark = '[!] ';
        if (!tabIsActive && $('title').text().indexOf(event_mark) === -1) {
            $('title').prepend(event_mark);
        }
        $(document).on(visibilityChange, function() {
            if (!document[hidden]) {
                $('title').text(original_title);
            }
        });
    } else {
        // from current page, from yourself
        dialog_id = current_dialog_id;
    }

    var dialog_selector = '.dialogs li[data-dialog-id="' + dialog_id + '"]';

    // If messages page is opened
    if ($('.dialogs').length) {
        if ($(dialog_selector).length === 0) {
            // add new dialog
            if ($('.dialogs .media-list').length) {
                $('.dialogs .media-list').prepend(msg);
            } else {
                $('.dialogs > div').remove();
                $('.dialogs').append('<ul class="media-list">' + msg + '</ul>');
            }

            // Update page view
            setElementSizes();
        } else {
            // update existing dialog
            var message_content = $(msg).find('.content').text().substring(0, 80); // length also defined in dialog.html.twig
            $(dialog_selector).find('.last-message').text(message_content + (message_content.length === 80 ? '...' : '')); // update last message in dialog
        }

        if ($(msg).hasClass('sent')) { // update arrow
            $(dialog_selector).find('[class*="glyphicon-arrow-"]').removeClass('glyphicon-arrow-left').addClass('glyphicon-arrow-right');
        } else {
            $(dialog_selector).find('[class*="glyphicon-arrow-"]').removeClass('glyphicon-arrow-right').addClass('glyphicon-arrow-left');
        }
    }

    // If dialog is opened
    if ($('.view-messages').length) {
        if (dialog_id === current_dialog_id) {
            $('.messages .media-list').append(msg); // add new message to the list
            $(window).scrollTop($(document).height());
            $('.messages').trigger('newMessageAdded'); // to reset texarea size

            // Mark as read
            markAsRead();
        } else {
            $(dialog_selector).addClass('new');
        }
    }
}

// Mark recieved messages as read
var read_timeout_id = null;
var mark_read_xhr;
var is_mark_read_queue = false;

function markAsRead() {
    if ($('.messages .media.recieved.new').length === 0) {
        return;
    }

    if (tabIsActive) {
        timing();
    } else {
        $(document).on(visibilityChange, function() {
            if (!document[hidden]) {
                timing();
            }
        });
    }

    function timing() {
        if (read_timeout_id !== null) {
            clearTimeout(read_timeout_id);
        }

        read_timeout_id = setTimeout(function() {
            read_timeout_id = null;

            // Wait untill another AJAX requests will be finished and cancel all pending AJAX requests except one
            if (mark_read_xhr && mark_read_xhr.readyState !== 4) {
                if (!is_mark_read_queue) {
                    is_mark_read_queue = true;
                    $.when(mark_read_xhr).done(function() {
                        request();
                        is_mark_read_queue = false;
                    });
                }
            } else {
                request();
            }
        }, 3000);
    }

    function request() {
        var message_ids = [];
        $('.messages .media.recieved.new').each(function() {
            message_ids.push($(this).data('message-id'));
        });

        if (message_ids.length === 0) {
            return;
        }

        var error = false;

        mark_read_xhr = $.ajax({
            url: mark_read_url + '/' + current_dialog_id,
            type: 'POST',
            data: {message_ids: JSON.stringify(message_ids)},
            success: function(msg) {
                if (msg === 'Messages marked as read') {
                    // Mark messages read
                    $('.messages .media.recieved.new').each(function() {
                        if ($.inArray($(this).data('message-id'), message_ids) !== -1) {
                            $(this).removeClass('new');
                        }
                    });

                    // Update message counter
                    updateMessageCounter('-', message_ids.length);
                } else {
                    error = true;
                }
            },
            error: function(msg) {
                error = true;
            },
            complete: function(msg) {
                $('.messages .alert-danger').remove();

                if (error === true) {
                    errorMessage('Ошибка соединения с сервером. Пожалуйста обновите страницу');
                }
            }
        });
    }

}

/* Function to send messages from user profile - run in DualizeUserbundle:Profile:view.html.twig */
function profileMessagesInit(profile_message_url) {
    // Click on button 'Write message'
    $('a[href="#new-profile-message"]').click(function() {
        // Load message form
        $('#new-profile-message .modal-body').text('').addClass('loading').load(profile_message_url, function() {
            $(this).removeClass('loading');
            sendOnCtrlEnter('form[name="message"]');
            $('form[name="message"] textarea').focus();

            // Send message to server
            $('form[name="message"]').submit(function() {
                $('form[name="message"] button').attr('disabled', 'disabled');
                var error = false;

                $.ajax({
                    url: profile_message_url,
                    type: 'POST',
                    data: $('form[name="message"]').serialize(),
                    success: function(msg) {
                        if (msg === 'Message sent') {
                            $('.profile-message').text('Ваше сообщение успешно отправлено');
                        } else {
                            error = true;
                        }
                    },
                    error: function(msg) {
                        error = true;
                    },
                    complete: function(msg) {
                        $('form[name="message"] button').removeAttr('disabled');
                        if (error === true) {
                            $('.profile-message').text('Ошибка отправки сообщения. Пожалуйста, обновите страницу и повторите запрос');
                        }
                    }
                });
                return false;
            });
        });
    });
}

function sendOnCtrlEnter(form_selector) {
    $(form_selector).find('textarea').keydown(function(e) {
        if (e.ctrlKey && e.keyCode === 13) {
            $(form_selector).submit();
        }
    });
}

// Increase textarea
function expandOnTyping(form_selector) {
    var textarea = $(form_selector).find('textarea');
    var max_height = 180;
    var orig_height = textarea.height();

    textarea.on('keyup focus', function(e) {
        while (textarea.height() < max_height && textarea.innerHeight() < textarea[0].scrollHeight) {
            textarea.height(textarea.height() + 3);
            $('.messages .message-list').css('padding-bottom', '+=3');
        }
    });

    $('.messages').on('newMessageAdded', formReset);
    textarea.on('blur', formReset);

    function formReset() {
        textarea.height(orig_height);
        $('.messages .message-list').css('padding-bottom', $('.messages .messages-form').outerHeight());
    }
}

function errorMessage(msg) {
    var msg = '<div class="alert alert-danger alert-dismissible">'
            + '<button type="button" class="close" data-dismiss="alert"><span title="Скрыть">&times;</span></button>'
            + msg
            + '</div>';
    $('.messages .messages-form').before(msg);
    $(window).scrollTop($(document).height());
}

/* Subscription to realtime events */
if (typeof _USER_ID !== 'undefined') {
    subscribeToSubject('messages:' + _USER_ID, onMessageRecieve); // New messages
    subscribeToSubject('typing:' + _USER_ID, onTyping); // Recipient is typing
    subscribeToSubject('mark_read:' + _USER_ID, onMarkRead); // Recipient is typing
}

// Update count of messages in navbar
function updateMessageCounter(action, num) {
    var message_count = parseInt($('.message-counter').text());
    switch (action) {
        case '+':
            if (isNaN(message_count)) {
                $('.message-counter').text(num);
            } else {
                $('.message-counter').text(message_count + num);
            }
            break;
        case '-':
            if (message_count - num < 1) {
                $('.message-counter').text('');
            } else {
                $('.message-counter').text(message_count - num);
            }
            break;
    }
}

// Run when recipient send message
function onMessageRecieve(topicUri, msg) {
    if (topicUri.split(':')[0] === 'messages') {
        // Update message counter
        updateMessageCounter('+', 1);

        // - this code is inside messages.js
        var message = JSON.parse(msg);
        addNewMessage(message.contentHTML, message.dialogId);

        // Play sound
        $('#new-message-sound').trigger('play');

        $('.user-is-typing').hide();
        clearTimeout(typing_timeout_id);
        typing_timeout_id = null;
    }
}

// Run when recipient is typing
var typing_timeout_id = null;
function onTyping(topicUri, event) {
    if (topicUri.split(':')[0] === 'typing' && $('.view-messages').length && event === current_dialog_id) {

        if (typing_timeout_id === null) {
            $('.user-is-typing').show();
            $(window).scrollTop($(document).height());
        } else {
            clearTimeout(typing_timeout_id);
        }

        typing_timeout_id = setTimeout(function() {
            $('.user-is-typing').hide();
            typing_timeout_id = null;
        }, 3000);
    }
}

function onMarkRead(topicUri, event) {
    if (topicUri.split(':')[0] === 'mark_read' && $('.view-messages').length) {
        $('.messages .media.sent[data-message-id="' + event + '"]').removeClass('new');
    }
}

/* Current state of browser tab */
var tabIsActive = true;

// Set the name of the hidden property and the change event for visibility
var hidden, visibilityChange;
if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support 
    hidden = "hidden";
    visibilityChange = "visibilitychange";
} else if (typeof document.mozHidden !== "undefined") {
    hidden = "mozHidden";
    visibilityChange = "mozvisibilitychange";
} else if (typeof document.msHidden !== "undefined") {
    hidden = "msHidden";
    visibilityChange = "msvisibilitychange";
} else if (typeof document.webkitHidden !== "undefined") {
    hidden = "webkitHidden";
    visibilityChange = "webkitvisibilitychange";
}

$(document).on(visibilityChange, function() {
    if (document[hidden]) {
        tabIsActive = false;
    } else {
        tabIsActive = true;
    }
});

/* Styling message page */
$(document).ready(function() {
    if ($('.dialogs > .media-list').length) {
        setElementSizes();
        $(window).resize(setElementSizes);
        $('.dialogs > .media-list').height($(window).height() - $('.dialogs > .media-list').offset().top);
    }
});

function setElementSizes() {
    $('.dialogs > .media-list').width($('.dialogs').width());
    $('.messages .messages-form, .messages h4').width($('.messages').width());
    $('.messages .message-list').css('padding-bottom', $('.messages .messages-form').outerHeight());
}
