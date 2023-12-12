<script>
    function postClientId(clientId) {
        let data = new FormData();
        data.append('client_id', clientId);
        let xhr = new XMLHttpRequest();
        xhr.open('POST', "{{ url(config('ga4-event-tracking.http_uri'))}}", true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.send(data);
    }

    function collectClientId() {
        if (typeof ga !== 'undefined') {
            ga(function () {
                let clientId = ga.getAll()[0].get('clientId');
                if (clientId !== @json(app('ga4-event-tracking.client-id'))) {
                    postClientId(clientId);
                }
            });
        } else if (typeof gtag !== 'undefined') {
            gtag('get', @json(config('ga4-event-tracking.measurement_id')), 'client_id', function (clientId) {
                if (clientId !== @json(app('ga4-event-tracking.client-id'))) {
                    postClientId(clientId);
                }
            });
        }
    }

    collectClientId();
</script>
