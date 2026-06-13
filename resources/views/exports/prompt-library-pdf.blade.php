<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 80px 60px 80px 60px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.55; color: #222; }

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
    .cover h1 { font-size: 34pt; font-weight: 700; color: #FFFFFF; margin: 0 0 20px; line-height: 1.2; }
    .cover .subtitle { font-size: 14pt; font-style: italic; color: rgba(255,255,255,0.65); margin-bottom: 60px; }
    .cover .author { font-size: 12pt; color: rgba(255,255,255,0.5); margin-top: 40px; }
    .cover .brand-bar { display: block; width: 60px; height: 4px; background: {{ $brandColor }}; margin: 40px auto; }
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

    .toc { page-break-after: always; padding-top: 20px; }
    .toc h2 { color: {{ $brandColor }}; font-size: 20pt; border-bottom: 2px solid {{ $brandColor }}; padding-bottom: 8px; margin-bottom: 24px; }
    .toc ul { list-style: none; padding: 0; }
    .toc li { padding: 8px 0; border-bottom: 1px dotted #ccc; font-size: 11pt; }
    .toc li .num { display: inline-block; width: 30px; color: {{ $brandColor }}; font-weight: 700; }
    .toc li .count { float: right; color: #888; font-size: 9pt; }

    .category { page-break-before: always; }
    .category-header {
        background: #F8F7FF;
        border-left: 4px solid {{ $brandColor }};
        padding: 14px 18px;
        margin-bottom: 18px;
    }
    .category-header .cat-num { font-size: 9pt; text-transform: uppercase; letter-spacing: 2px; color: {{ $brandColor }}; margin: 0; font-weight: 700; }
    .category-header h2 { color: {{ $brandColor }}; font-size: 18pt; font-weight: 700; margin: 4px 0 0; line-height: 1.2; }

    .prompt {
        border: 1px solid #E4E0F0;
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 16px;
        page-break-inside: avoid;
    }
    .prompt-title {
        font-size: 9pt;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: {{ $brandColor }};
        font-weight: 700;
        margin: 0 0 8px;
    }
    .prompt-label { font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 700; margin: 10px 0 4px; }
    .prompt-use-when { color: #555; font-size: 10pt; font-style: italic; margin: 0 0 8px; }
    .prompt-box {
        background: #F5F5F5;
        border-left: 3px solid {{ $brandColor }};
        font-family: DejaVu Sans Mono, monospace;
        font-size: 9pt;
        padding: 12px 14px;
        margin: 4px 0 8px;
        white-space: pre-wrap;
        color: #222;
        line-height: 1.5;
    }
    .prompt-tip { color: #777; font-style: italic; font-size: 9pt; margin: 6px 0 0; }
</style>
</head>
<body>

<div class="cover">
    @if(isset($productType) && $productType)
    <div class="product-type-badge">{{ $productType }}</div>
    @endif
    <h1>{{ $title }}</h1>
    @if(isset($tagline) && $tagline)
    <div class="subtitle">{{ $tagline }}</div>
    @endif
    <span class="brand-bar"></span>
    <div class="author">by {{ $author }}</div>
</div>

<div class="toc">
    <h2>Contents</h2>
    <ul>
        @foreach($categories as $i => $c)
        <li><span class="num">{{ $i + 1 }}</span> {{ $c['name'] }}<span class="count">{{ count($c['prompts']) }} prompts</span></li>
        @endforeach
    </ul>
</div>

@foreach($categories as $i => $c)
<div class="category">
    <div class="category-header">
        <p class="cat-num">Category {{ $i + 1 }}</p>
        <h2>{{ $c['name'] }}</h2>
    </div>

    @foreach($c['prompts'] as $j => $p)
    <div class="prompt">
        <p class="prompt-title">Prompt {{ $j + 1 }} — {{ $p['title'] }}</p>
        @if(! empty($p['use_when']))
        <p class="prompt-label">Use when</p>
        <p class="prompt-use-when">{{ $p['use_when'] }}</p>
        @endif
        @if(! empty($p['the_prompt']))
        <p class="prompt-label">The prompt</p>
        <div class="prompt-box">{{ $p['the_prompt'] }}</div>
        @endif
        @if(! empty($p['tip']))
        <p class="prompt-tip">Tip: {{ $p['tip'] }}</p>
        @endif
    </div>
    @endforeach
</div>
@endforeach

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $size = 9;
        $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $pdf->page_text(280, 800, $pageText, $font, $size, [0.6, 0.6, 0.6]);
    }
</script>
</body>
</html>
