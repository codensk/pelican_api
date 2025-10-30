<b>{{ $appName }}</b> ({{ $level_name }})
Env: {{ $appEnv }} [{{ $datetime->format('Y-m-d H:i:s') }}]
{{ url($context['uri'] ?? '' ) }}
{{ str_replace('Array', '', print_r($context['request'] ?? null, true)) }}
{{ $formatted }}
