@props(['title', 'breadcrumbs' => []])
<header class="ui-page-header">
    @if(count($breadcrumbs))<nav aria-label="Breadcrumb"><ol>@foreach($breadcrumbs as $crumb)<li>@if(!$loop->last && isset($crumb['url']))<a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>@else<span aria-current="page">{{ $crumb['label'] }}</span>@endif</li>@endforeach</ol></nav>@endif
    <h1>{{ $title }}</h1>
</header>
