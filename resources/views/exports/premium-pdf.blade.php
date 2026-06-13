<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 80px 60px 80px 60px; }
    body { font-family: DejaVu Serif, serif; font-size: 12pt; line-height: 1.55; color: #222; }
    .cover {
        page-break-after: always;
        text-align: center;
        padding-top: 180px;
        background: #1A0D33;
        margin: -80px -60px;
        padding-left: 60px;
        padding-right: 60px;
        min-height: 100vh;
    }
    .cover h1 {
        font-size: 34pt;
        font-weight: 700;
        color: #FFFFFF;
        margin: 0 0 20px;
        line-height: 1.2;
    }
    .cover .subtitle {
        font-size: 14pt;
        font-style: italic;
        color: rgba(255,255,255,0.65);
        margin-bottom: 60px;
    }
    .cover .author {
        font-size: 12pt;
        color: rgba(255,255,255,0.5);
        margin-top: 40px;
    }
    .cover .brand-bar {
        display: block;
        width: 60px;
        height: 4px;
        background: {{ $brandColor }};
        margin: 40px auto;
    }
    .cover .product-type-badge {
        display: inline-block;
        background: {{ $brandColor }};
        color: #ffffff;
        font-size: 9pt;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 6px 18px;
        border-radius: 20px;
        margin-bottom: 40px;
    }

    .toc { page-break-after: always; padding-top: 30px; }
    .toc h2 { color: {{ $brandColor }}; font-size: 20pt; border-bottom: 2px solid {{ $brandColor }}; padding-bottom: 8px; margin-bottom: 24px; }
    .toc ul { list-style: none; padding: 0; }
    .toc li { padding: 8px 0; border-bottom: 1px dotted #ccc; font-size: 11pt; }
    .toc li .num { display: inline-block; width: 30px; color: {{ $brandColor }}; font-weight: 700; }

    .chapter { page-break-before: always; }
    .chapter h2 {
        color: {{ $brandColor }};
        font-size: 20pt;
        font-weight: 700;
        margin: 0 0 8px;
        line-height: 1.2;
        padding: 16px 20px;
        background: #F8F7FF;
        border-left: 4px solid {{ $brandColor }};
    }
    .chapter .chap-num { font-size: 10pt; text-transform: uppercase; letter-spacing: 2px; color: #999; margin: 0 0 8px; }
    .chapter .chap-divider { display: block; width: 50px; height: 3px; background: {{ $brandColor }}; margin: 12px 0 28px; }
    .chapter p { text-align: justify; margin: 0 0 12px; }

    footer { position: fixed; bottom: -50px; left: 0; right: 0; text-align: center; font-size: 9pt; color: #999; }
</style>
</head>
<body>

<footer>{{ $title }} · Page <span class="pagenum"></span></footer>

<div class="cover">
    @if(isset($productType) && $productType)
    <div class="product-type-badge">{{ $productType }}</div>
    @endif
    <h1>{{ $title }}</h1>
    @if($subtitle)
    <div class="subtitle">{{ $subtitle }}</div>
    @endif
    @if(isset($tagline) && $tagline)
    <div class="subtitle">{{ $tagline }}</div>
    @endif
    <span class="brand-bar"></span>
    <div class="author">by {{ $author }}</div>
</div>

<div class="toc">
    <h2>Contents</h2>
    <ul>
        @foreach($chapters as $i => $c)
        <li><span class="num">{{ $i + 1 }}</span> {{ $c['title'] }}</li>
        @endforeach
    </ul>
</div>

@foreach($chapters as $i => $c)
<div class="chapter">
    <p class="chap-num">Chapter {{ $i + 1 }}</p>
    <h2>{{ $c['title'] }}</h2>
    <span class="chap-divider"></span>
    @foreach(preg_split("/\n\s*\n/", trim($c['body'])) as $para)
    <p>{!! nl2br(e(trim($para))) !!}</p>
    @endforeach
</div>
@endforeach

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Serif", "normal");
        $size = 9;
        $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $pdf->page_text(280, 800, $pageText, $font, $size, [0.6, 0.6, 0.6]);
    }
</script>

</body>
</html>
