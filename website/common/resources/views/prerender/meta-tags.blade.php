@foreach($meta->getAll() as $tag)
    @if ($tag['nodeName'] === 'meta')
        <meta {!!$meta->tagToString($tag)!!} class="dst">
    @elseif ($tag['nodeName'] === 'link')
        <link {!!$meta->tagToString($tag)!!} class="dst">
    @elseif ($tag['nodeName'] === 'title')
        <title class="dst">{{$tag['_text']}}</title>
    @elseif ($tag['nodeName'] === 'script')
        <script class="dst" type="application/ld+json">{!! is_array($tag['_text']) ? json_encode($tag['_text'], JSON_UNESCAPED_SLASHES) : $tag['_text'] !!}</script>
    @endif
@endforeach
