/**
 * Getting new messages and companion typing 
 */
if (typeof (_USER_ID) !== 'undefined') { // If logged user
    var wsuri = 'ws://' + document.domain + '/websocket'; // The WebSocket URI of the WAMP server
    var sess; // WAMP session object

    // Websocket connection init
    $(window).load(function() {
        ab.connect(
                wsuri,
                function(session) {  // On connect, session starts
                    sess = session;
                    console.log('Connected to server');
                    $(document).trigger('sessinit');
                },
                function(code, reason, detail) {  // On disconnect, session ends
                    sess = null;
                    console.log(reason);
                }
        );
    });

    // Subscribe to topic when sess was initialized
    function subscribeToSubject(subject, handler) {
        if (typeof sess !== 'undefined') {
            sess.subscribe(subject, handler);
        } else {
            $(document).on('sessinit', function() {
                sess.subscribe(subject, handler);
            });
        }
    }
}
