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
    .cover .badge { display:inline-block; background:#D97706; color:#fff; font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:5px 16px; border-radius:20px; margin-bottom:32px; }
    .cover .tagline { font-size:13pt; color:rgba(255,255,255,0.7); margin-bottom:48px; font-style:italic; }
    .cover .bar { display:block; width:50px; height:4px; background:#D97706; margin:32px auto; }
    .cover .author { font-size:11pt; color:rgba(255,255,255,0.45); }
    .toc { page-break-after:always; padding-top:20px; }
    .toc h2 { color:#D97706; font-size:18pt; border-bottom:2px solid #D97706; padding-bottom:6px; margin-bottom:20px; }
    .toc li { list-style:none; padding:7px 0; border-bottom:1px dotted #ccc; font-size:10pt; }
    .toc .n { display:inline-block; width:26px; color:#D97706; font-weight:700; }
    .section { page-break-before:always; }
    .section-hdr { background:#1A0D33; padding:16px 22px; border-radius:8px; margin-bottom:24px; }
    .section-hdr .num { font-size:8pt; letter-spacing:2px; text-transform:uppercase; color:#D97706; margin-bottom:4px; }
    .section-hdr h2 { margin:0; font-size:18pt; font-weight:700; color:#fff; }
    .section p { margin:0 0 10px; }
    .brand-block { background:#FFFBEB; border-left:4px solid #D97706; padding:12px 16px; margin:0 0 12px; border-radius:0 6px 6px 0; }
    .brand-block strong { color:#92400E; display:block; font-size:9pt; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
    .quote-block { background:#FEF3C7; border-radius:8px; padding:14px 18px; margin:0 0 12px; font-style:italic; color:#78350F; font-size:12pt; }
    footer { position:fixed; bottom:-50px; left:0; right:0; text-align:center; font-size:8pt; color:#999; }
</style>
</head>
<body>
<footer>{{ $title }} · Brand Messaging System</footer>
<div class="cover">
    <div class="badge">Brand Messaging System</div>
    <h1>{{ $title }}</h1>
    @if(!empty($tagline))<div class="tagline">{{ $tagline }}</div>@endif
    <span class="bar"></span>
    <div class="author">by {{ $author }}</div>
</div>
<div class="toc">
    <h2>Your Brand Bible</h2>
    <ul>
        @foreach($sections as $i => $s)
        <li><span class="n">{{ $i + 1 }}</span>{{ $s['title'] }}</li>
        @endforeach
    </ul>
</div>
@foreach($sections as $i => $s)
<div class="section">
    <div class="section-hdr">
        <div class="num">Section {{ $i + 1 }}</div>
        <h2>{{ $s['title'] }}</h2>
    </div>
    @foreach(preg_split("/\n\s*\n/", trim($s['body'])) as $para)
        @php $p = trim($para); @endphp
        @if($p !== '')
            @if(preg_match('/^(POSITIONING|MISSION|VISION|BRAND PROMISE|CORE VALUE|TAGLINE|ELEVATOR|OBJECTION|BIO|SCRIPT|PERSONA|VOICE|TONE|WRITING RULE|WORDS TO USE|WORDS TO AVOID)/', strtoupper($p)))
                <div class="brand-block">{!! nl2br(e($p)) !!}</div>
            @elseif(str_starts_with($p, '"') || str_starts_with($p, '"'))
                <div class="quote-block">{!! nl2br(e($p)) !!}</div>
            @else
                <p>{!! nl2br(e($p)) !!}</p>
            @endif
        @endif
    @endforeach
</div>
@endforeach
</body>
</html>
