<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 80px 60px 80px 60px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.65; color: #1A1A2E; }
    .cover { page-break-after:always; text-align:center; padding-top:160px; background:#1A0D33; margin:-80px -60px; padding-left:60px; padding-right:60px; min-height:100vh; }
    .cover h1 { font-size:28pt; font-weight:700; color:#fff; margin:0 0 16px; line-height:1.2; }
    .cover .badge { display:inline-block; background:#DC2626; color:#fff; font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:5px 16px; border-radius:20px; margin-bottom:32px; }
    .cover .tagline { font-size:13pt; color:rgba(255,255,255,0.7); margin-bottom:48px; }
    .cover .bar { display:block; width:50px; height:4px; background:#DC2626; margin:32px auto; }
    .cover .author { font-size:11pt; color:rgba(255,255,255,0.45); }
    .toc { page-break-after:always; padding-top:20px; }
    .toc h2 { color:#DC2626; font-size:18pt; border-bottom:2px solid #DC2626; padding-bottom:6px; margin-bottom:20px; }
    .toc li { list-style:none; padding:7px 0; border-bottom:1px dotted #ccc; font-size:10pt; }
    .toc .n { display:inline-block; width:26px; color:#DC2626; font-weight:700; }
    .section { page-break-before:always; }
    .section-hdr { background:#7F1D1D; padding:14px 20px; border-radius:6px; margin-bottom:22px; }
    .section-hdr .num { font-size:8pt; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,0.6); margin-bottom:4px; }
    .section-hdr h2 { margin:0; font-size:18pt; font-weight:700; color:#fff; }
    .section p { margin:0 0 10px; text-align:justify; }
    .copy-label { font-size:8pt; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#DC2626; margin:14px 0 4px; }
    .copy-box { background:#FFF5F5; border:1px solid #FCA5A5; border-radius:6px; padding:10px 14px; margin:0 0 10px; }
    footer { position:fixed; bottom:-50px; left:0; right:0; text-align:center; font-size:8pt; color:#999; }
</style>
</head>
<body>
<footer>{{ $title }} · Sales Funnel Copy Pack</footer>
<div class="cover">
    <div class="badge">Sales Funnel Copy Pack</div>
    <h1>{{ $title }}</h1>
    @if(!empty($tagline))<div class="tagline">{{ $tagline }}</div>@endif
    <span class="bar"></span>
    <div class="author">by {{ $author }}</div>
</div>
<div class="toc">
    <h2>Your Complete Funnel</h2>
    <ul>
        @foreach($sections as $i => $s)
        <li><span class="n">{{ $i + 1 }}</span>{{ $s['title'] }}</li>
        @endforeach
    </ul>
</div>
@foreach($sections as $i => $s)
<div class="section">
    <div class="section-hdr">
        <div class="num">{{ $i === 0 ? 'How to Use' : 'Section '.($i) }}</div>
        <h2>{{ $s['title'] }}</h2>
    </div>
    @foreach(preg_split("/\n\s*\n/", trim($s['body'])) as $para)
        @php $p = trim($para); @endphp
        @if($p !== '')
            @if(preg_match('/^(HEADLINE|SUBHEADLINE|BODY COPY|SUBJECT LINE|PREVIEW TEXT|SEND TIMING|CTA|EMAIL \d|LANDING PAGE|SALES PAGE|LEAD MAGNET|STEP \d)/', strtoupper($p)))
                <div class="copy-label">{{ strtok($p, "\n") }}</div>
                <div class="copy-box">{!! nl2br(e(trim(substr($p, strpos($p, "\n") + 1)))) !!}</div>
            @else
                <p>{!! nl2br(e($p)) !!}</p>
            @endif
        @endif
    @endforeach
</div>
@endforeach
</body>
</html>
