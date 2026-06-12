<title>{{ $fullTitle() }}</title>
@if ($description())
<meta name="description" content="{{ $description() }}">
@endif
<meta name="robots" content="{{ $robots() }}">
<meta property="og:title" content="{{ $fullTitle() }}">
@if ($description())
<meta property="og:description" content="{{ $description() }}">
@endif
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $ogUrl() }}">
<meta property="og:site_name" content="{{ $siteName() }}">
@if ($imageUrl())
<meta property="og:image" content="{{ $imageUrl() }}">
@endif
@if ($canonical)
<link rel="canonical" href="{{ $canonical }}">
@endif
