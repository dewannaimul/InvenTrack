@component('mail::message')
{{ $bodyMessage }}

The invoice is attached to this email as a PDF.

Thanks,<br>
{{ \App\Models\CompanySetting::current()->name }}
@endcomponent
