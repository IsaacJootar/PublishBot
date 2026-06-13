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

    .sequence { page-break-before: always; }
    .sequence-header {
        background: #F8F7FF;
        border-left: 4px solid {{ $brandColor }};
        padding: 14px 18px;
        margin-bottom: 18px;
    }
    .sequence-header .seq-num { font-size: 9pt; text-transform: uppercase; letter-spacing: 2px; color: {{ $brandColor }}; margin: 0; font-weight: 700; }
    .sequence-header h2 { color: {{ $brandColor }}; font-size: 18pt; font-weight: 700; margin: 4px 0 6px; line-height: 1.2; }
    .sequence-header .seq-meta { font-size: 9pt; color: #666; margin: 0; }

    .email-card {
        border: 1px solid #E4E0F0;
        border-radius: 6px;
        padding: 16px 20px;
        margin-bottom: 18px;
        page-break-inside: avoid;
    }
    .email-badge {
        display: inline-block;
        background: {{ $brandColor }};
        color: #fff;
        font-size: 9pt;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 12px;
        margin-bottom: 6px;
    }
    .email-timing { font-size: 9pt; color: #888; margin: 0 0 12px; font-style: italic; }
    .email-subject {
        background: {{ $brandColor }};
        color: #fff;
        font-weight: 700;
        font-size: 11pt;
        padding: 10px 14px;
        border-radius: 4px;
        margin: 0 0 6px;
    }
    .email-preview { font-style: italic; color: #666; font-size: 10pt; margin: 0 0 14px; padding: 0 14px; }
    .email-label { font-size: 8pt; text-transform: uppercase; letter-spacing: 1.5px; color: {{ $brandColor }}; font-weight: 700; margin: 12px 0 4px; }
    .email-body { color: #222; font-size: 10.5pt; line-height: 1.7; margin: 0 0 12px; white-space: pre-wrap; }
    .email-cta {
        margin-top: 8px;
        padding: 8px 12px;
        background: #F5F4FF;
        border-left: 3px solid {{ $brandColor }};
        font-size: 10pt;
        font-weight: 700;
        color: {{ $brandColor }};
    }
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
        @foreach($sequences as $i => $seq)
        <li><span class="num">{{ $i + 1 }}</span> {{ $seq['name'] }}<span class="count">{{ count($seq['emails']) }} emails</span></li>
        @endforeach
    </ul>
</div>

@foreach($sequences as $i => $seq)
<div class="sequence">
    <div class="sequence-header">
        <p class="seq-num">Sequence {{ $i + 1 }}</p>
        <h2>{{ $seq['name'] }}</h2>
        @if(! empty($seq['trigger']) || ! empty($seq['goal']))
        <p class="seq-meta">
            @if(! empty($seq['trigger']))<strong>Trigger:</strong> {{ $seq['trigger'] }}@endif
            @if(! empty($seq['goal']))<br><strong>Goal:</strong> {{ $seq['goal'] }}@endif
        </p>
        @endif
    </div>

    @foreach($seq['emails'] as $j => $em)
    <div class="email-card">
        <span class="email-badge">Email {{ $em['n'] ?? ($j + 1) }} of {{ $em['of'] ?? count($seq['emails']) }}</span>
        @if(! empty($em['timing']))
        <p class="email-timing">Send: {{ $em['timing'] }}</p>
        @endif
        @if(! empty($em['subject']))
        <p class="email-subject">{{ $em['subject'] }}</p>
        @endif
        @if(! empty($em['preview']))
        <p class="email-preview">{{ $em['preview'] }}</p>
        @endif
        @if(! empty($em['body']))
        <p class="email-label">Body</p>
        <div class="email-body">{{ $em['body'] }}</div>
        @endif
        @if(! empty($em['cta']))
        <div class="email-cta">CTA: {{ $em['cta'] }}</div>
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
