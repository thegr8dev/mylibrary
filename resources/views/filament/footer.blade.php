<p align="center">
    &copy; {{ date('Y') }} | {{ App\Models\Settings::first()?->site_title ?? config('app.name') }} | All
    rights
    reserved
</p>
