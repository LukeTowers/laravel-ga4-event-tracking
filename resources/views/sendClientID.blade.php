<script>
    function ga4PostId(id, value) {
        let data = new FormData();
        data.append(id, value);
        let xhr = new XMLHttpRequest();
        xhr.open('POST', "{{ url(config('ga4-event-tracking.http_uri'))}}", true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.send(data);
    }

    function postClientId(clientId) {
        ga4PostId('client_id', clientId);
    }

    function postSessionId(sessionId) {
        ga4PostId('session_id', sessionId);
    }

    function collectClientId() {
        if (typeof ga !== 'undefined') {
            ga(function () {
                let clientId = ga.getAll()[0].get('clientId');
                if (clientId !== @json(app('ga4-event-tracking.client-id'))) {
                    postClientId(clientId);
                }
                let sessionId = ga.getAll()[0].get('sessionId');
                if (sessionId !== @json(app('ga4-event-tracking.session-id'))) {
                    postSessionId(sessionId);
                }
            });
        } else if (typeof gtag !== 'undefined') {
            gtag('get', @json(config('ga4-event-tracking.measurement_id')), 'client_id', function (clientId) {
                if (clientId !== @json(app('ga4-event-tracking.client-id'))) {
                    postClientId(clientId);
                }
            });
            gtag('get', @json(config('ga4-event-tracking.measurement_id')), 'session_id', function (sessionId) {
                if (sessionId !== @json(app('ga4-event-tracking.session-id'))) {
                    postSessionId(sessionId);
                }
            });
        }
    }

    collectClientId();
</script>
