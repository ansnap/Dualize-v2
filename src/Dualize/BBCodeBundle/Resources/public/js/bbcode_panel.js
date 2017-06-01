/**
 * BBcode text editor panel
 */
(function($) {
    $.fn.bbcode_panel = function(image_upload_url, preview_url) {

        return this.each(function() {
            var textArea = $(this);
            var form = textArea.parents('form');

            if (textArea.siblings('#bbcode-panel').length === 0) {
                // Reset preview if changed to another textarea
                $('#bbcode-panel').siblings('textarea').show();
                $('#bbcode-panel').find('button.preview').button('reset').removeClass('active');
                $('#bbcode-preview').text('');

                $('#bbcode-panel').insertBefore(textArea).outerWidth(textArea.outerWidth()).show();
                $('#bbcode-panel *').off(); // recursive remove event handlers
            } else {
                return;
            }

            // Send on Ctrl + Enter
            textArea.on('keydown', function(e) {
                if (e.ctrlKey && e.keyCode === 13) {
                    form.submit();
                }
            });

            // Increase textarea on typing
            textArea.on('keyup focus', function(e) {
                while (textArea.height() < 180 && textArea.innerHeight() < textArea[0].scrollHeight) {
                    textArea.height(textArea.height() + 3);
                }
            });

            // Prevent double sending and prevent removing panel with form
            form.on('submit', function() {
                $(this).find('button[type="submit"]').button('loading');
                $('#bbcode-panel').hide().prependTo('body');
            });

            // Button clicks
            $('#bbcode-panel').find('button[data-open]').on('click', function() {
                // Simple buttons
                wrapText($(this).data('open'), $(this).data('close'));
            });

            $('#bbcode-panel').find('.dropdown-menu span').on('click', function() {

                if ($(this).parents('[data-open]').length) {
                    // Font size, color
                    var openTag = $(this).parents('[data-open]').data('open');
                    var closeTag = $(this).parents('[data-open]').data('close');
                    var value = '';

                    if ($(this).is('[data-value]')) {
                        value = $(this).data('value');

                    } else if ($(this).is('[style]')) {
                        value = rgb2Hex($(this).css('background-color'));

                    } else if ($(this).is('.auto')) {
                        value = rgb2Hex($('body').css('color'));
                    }

                    wrapText(openTag + value, closeTag);

                } else if ($(this).is('[data-value]')) {
                    // Smileys (without space if first symbol in texarea or has previous space)
                    var start = textArea[0].selectionStart;
                    var text = (start === 0 || textArea.val().charAt(start - 1) === ' ' ? '' : ' ') + $(this).data('value') + ' ';
                    insertText(text);
                }

            });

            // Text functions
            function wrapText(openTag, closeTag) {
                textArea.focus();
                var start = textArea[0].selectionStart;
                var end = textArea[0].selectionEnd;
                var selectedText = textArea.val().substring(start, end);
                var replacement = '[' + openTag + ']' + selectedText + '[' + closeTag + ']';

                textArea.val(textArea.val().substring(0, start) + replacement + textArea.val().substring(end));

                // Set cursor position and selection
                if (start === end) {
                    var pos = start + openTag.length + 2 + selectedText.length; // +2 - square brackets

                    textArea[0].selectionStart = pos;
                    textArea[0].selectionEnd = pos;
                } else {
                    textArea[0].selectionStart = start;
                    textArea[0].selectionEnd = start + replacement.length;
                }
            }

            function insertText(text) {
                textArea.focus();
                var pos = textArea[0].selectionStart;
                textArea.val(textArea.val().substring(0, pos) + text + textArea.val().substring(pos));

                var new_pos = pos + text.length;
                textArea[0].selectionStart = new_pos;
                textArea[0].selectionEnd = new_pos;
            }

            function rgb2Hex(rgb) {
                rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
                function hex(x) {
                    return ('0' + parseInt(x).toString(16)).slice(-2);
                }
                return '#' + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
            }

            /* General */
            $('#bbcode-panel .modal').on('hidden.bs.modal', function(e) {
                $('.text-danger').text('');
            });

            /* Image */
            $('#bbcode-image-modal button[data-loading-text]').on('click', function() {
                $(this).siblings('input').trigger('click');
            });

            $('#bbcode-image-modal input[type="file"]').on('change', function() {
                $('#bbcode-image-modal .text-danger').text('');

                if (!$(this)[0].value.match(/.*\.(?:png|jpg|jpeg|gif)/i)) {
                    $('#bbcode-image-modal .text-danger').text('Неверный формат файла. Допустимы: jpg, jpeg, gif, png');
                    return;
                }

                var btn = $(this).siblings('button[data-loading-text]');
                btn.button('loading');

                var data = new FormData();
                data.append('image', $(this)[0].files[0]);
                var error = false;

                $.ajax({
                    url: image_upload_url,
                    type: 'POST',
                    data: data,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(msg) {
                        if (msg.indexOf('http') === 0) {
                            $('#bbcode-image-modal').modal('hide');

                            insertText(msg);
                        } else {
                            error = true;
                        }
                    },
                    error: function(msg) {
                        error = true;
                    },
                    complete: function(msg) {
                        if (error) {
                            $('#bbcode-image-modal .text-danger').text('Произошла ошибка. Пожалуйста, повторите запрос');
                        }
                        $('#bbcode-image-modal input').val('');
                        btn.button('reset');
                    }
                });
            });

            /* URL */
            $('#bbcode-url-modal').on('show.bs.modal', function(e) {
                $('#bbcode-url-address').val('');
                $('#bbcode-url-text').val('');
            });

            $('#bbcode-url-modal').on('shown.bs.modal', function(e) {
                $('#bbcode-url-address').focus();
            });

            // Prevent form submission when "Enter" pressed
            $('#bbcode-url-modal input').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    $('#bbcode-url-modal .confirm').trigger('click');
                    return false;
                }
            });

            $('#bbcode-url-modal .confirm').on('click', function() {
                var link = $('#bbcode-url-address').val();
                var text = $('#bbcode-url-text').val();

                if (link.match(/^https?:\/\/.+\..+/i)) {
                    $('#bbcode-url-modal').modal('hide');

                    if (text) {
                        insertText('[url=' + link + ']' + text + '[/url]');
                    } else {
                        insertText('[url]' + link + '[/url]');
                    }
                } else {
                    $('#bbcode-url-modal .text-danger').text('Ссылка указана неверно');
                }
            });

            /* Preview */
            $('#bbcode-panel').find('button.preview').on('click', function() {
                var btn = $(this);
                $('#bbcode-preview').text('');

                if (btn.hasClass('active')) {
                    textArea.show().focus();
                    return;
                }

                btn.button('loading');

                var error = false;

                $.ajax({
                    url: preview_url,
                    type: 'POST',
                    data: {text: textArea.val()},
                    success: function(msg) {
                        if (msg.indexOf('<div>') === 0) {
                            $('#bbcode-preview').html(msg);

                        } else {
                            error = true;
                        }
                    },
                    error: function(msg) {
                        error = true;
                    },
                    complete: function(msg) {
                        if (error) {
                            $('#bbcode-preview').text('Не удалось получить данные. Пожалуйста повторите запрос позже');
                        }
                        btn.button('reset');
                        textArea.hide();
                    }
                });

            });
        });
    };
}(jQuery));
