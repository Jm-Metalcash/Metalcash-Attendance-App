<x-mail::message>
@if (! empty($greeting))
# {{ $greeting }}
@else
# @lang('notifications.greeting')
@endif

{{-- Intro Lines --}}
@lang('notifications.reset_password_line_1')

{{-- Action Button --}}
@isset($actionText)
<x-mail::button :url="$actionUrl" color="primary">
    @lang('notifications.reset_password_action')
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@lang('notifications.reset_password_line_2')

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('notifications.salutation')<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang('notifications.subcopy', ['actionText' => $actionText]) 
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
