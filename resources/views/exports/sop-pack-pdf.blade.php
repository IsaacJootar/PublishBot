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

    .sop {
        page-break-before: always;
        border: 1px solid #E4E0F0;
        border-radius: 8px;
        padding: 22px 24px;
    }
    .sop-num-badge {
        display: inline-block;
        background: {{ $brandColor }};
        color: #fff;
        font-size: 9pt;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 14px;
        margin-bottom: 14px;
    }
    .sop h2 {
        color: {{ $brandColor }};
        font-size: 20pt;
        font-weight: 700;
        margin: 0 0 18px;
        line-height: 1.2;
        border-bottom: 2px solid {{ $brandColor }};
        padding-bottom: 10px;
    }
    .field-label {
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: {{ $brandColor }};
        font-weight: 700;
        margin: 16px 0 4px;
    }
    .field-value { margin: 0 0 10px; line-height: 1.6; color: #333; }
    .steps-list { margin: 6px 0 0; padding-left: 22px; }
    .steps-list li { margin-bottom: 8px; padding-left: 4px; line-height: 1.6; }
    .mistakes-list { margin: 6px 0 0; padding-left: 22px; }
    .mistakes-list li { margin-bottom: 6px; line-height: 1.55; color: #555; }
    .notes { font-style: italic; color: #777; margin-top: 16px; padding-top: 12px; border-top: 1px dashed #ddd; font-size: 10pt; line-height: 1.55; }
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
        @foreach($sops as $i => $sop)
        <li><span class="num">{{ $i + 1 }}</span> {{ $sop['title'] }}</li>
        @endforeach
    </ul>
</div>

@foreach($sops as $i => $sop)
<div class="sop">
    <span class="sop-num-badge">SOP {{ $i + 1 }} of {{ count($sops) }}</span>
    <h2>{{ $sop['title'] }}</h2>

    {{-- If Claude used structured labels, show them. Otherwise render raw body. --}}
    @if(!empty($sop['raw_body']))
    <div style="line-height:1.65;white-space:pre-wrap;font-size:11pt;color:#333;">{{ $sop['raw_body'] }}</div>
    @else

    @if(! empty($sop['purpose']))
    <p class="field-label">Purpose</p>
    <p class="field-value">{{ $sop['purpose'] }}</p>
    @endif

    @if(! empty($sop['when_to_use']))
    <p class="field-label">When to use</p>
    <p class="field-value">{{ $sop['when_to_use'] }}</p>
    @endif

    @if(! empty($sop['need_before']))
    <p class="field-label">What you need before starting</p>
    <p class="field-value" style="white-space:pre-wrap;">{{ $sop['need_before'] }}</p>
    @endif

    @if(! empty($sop['steps']))
    <p class="field-label">Steps</p>
    <ol class="steps-list">
        @foreach($sop['steps'] as $step)
        <li>{{ $step }}</li>
        @endforeach
    </ol>
    @endif
    @endif

    @if(! empty($sop['mistakes']))
    <p class="field-label">Common mistakes to avoid</p>
    <ul class="mistakes-list">
        @foreach($sop['mistakes'] as $m)
        <li>{{ $m }}</li>
        @endforeach
    </ul>
    @endif

    @if(! empty($sop['notes']))
    <div class="notes"><strong style="color:#92400E;font-style:normal;">Notes:</strong> {{ $sop['notes'] }}</div>
    @endif
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
