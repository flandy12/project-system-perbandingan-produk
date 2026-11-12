@props(['name'])
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
@if($name === 'facebook')
<path d="M13.5 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.8 1.8-1.8H17V2.4c-.3 0-1.4-.2-2.6-.2-2.6 0-4.4 1.6-4.4 4.5v2.8H7V13h3v9z"/>
@elseif($name === 'instagram')
<rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2"/>
@elseif($name === 'youtube')
<path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.7 4.6 12 4.6 12 4.6s-5.7 0-7.5.5a3 3 0 0 0-2.1 2.1A31 31 0 0 0 2 12a31 31 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.8.5 7.5.5 7.5.5s5.7 0 7.5-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 22 12a31 31 0 0 0-.4-4.8Z"/><path d="m10 8.7 5.5 3.3-5.5 3.3Z" fill="white"/>
@else
<path d="M18.9 2H22l-6.8 7.8L23 22h-6.3l-4.9-7.5L5.2 22H2l8.4-9.6L1 2h6.5l4.4 6.8L18.9 2zm-1.1 18h1.7L6.6 4H4.8l13 16z"/>
@endif
</svg>
