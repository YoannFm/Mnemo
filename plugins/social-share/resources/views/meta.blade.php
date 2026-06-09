@if(isset($socialShareTitle))
<meta property="og:title" content="{{ $socialShareTitle }}">
<meta property="og:description" content="{{ $socialShareDesc ?? '' }}">
<meta property="og:url" content="{{ $socialShareUrl ?? url()->current() }}">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $socialShareTitle }}">
<meta name="twitter:description" content="{{ $socialShareDesc ?? '' }}">
@endif
