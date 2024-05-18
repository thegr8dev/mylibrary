<p align="center">
    &copy; {{ date('Y') }} | {{config('app.name')}} | {!! app(config('settings.settings.site_settings'))?->copyright_text ?? config('app.name') !!}
</p>
