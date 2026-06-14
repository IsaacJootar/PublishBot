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
    .cover .badge { display:inline-block; background:#0891B2; color:#fff; font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:5px 16px; border-radius:20px; margin-bottom:32px; }
    .cover .tagline { font-size:13pt; color:rgba(255,255,255,0.7); margin-bottom:48px; }
    .cover .bar { display:block; width:50px; height:4px; background:#0891B2; margin:32px auto; }
    .cover .author { font-size:11pt; color:rgba(255,255,255,0.45); }
    .toc { page-break-after:always; padding-top:20px; }
    .toc h2 { color:#0891B2; font-size:18pt; border-bottom:2px solid #0891B2; padding-bottom:6px; margin-bottom:20px; }
    .toc li { list-style:none; padding:7px 0; border-bottom:1px dotted #ccc; font-size:10pt; }
    .toc .n { display:inline-block; width:26px; color:#0891B2; font-weight:700; }
    .section { page-break-before:always; }
    .section-hdr { background:#0C4A6E; padding:16px 22px; border-radius:8px; margin-bottom:24px; }
    .section-hdr .num { font-size:8pt; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:4px; }
    .section-hdr h2 { margin:0; font-size:18pt; font-weight:700; color:#fff; }
    .section p { margin:0 0 10px; }
    .persona-field { margin-bottom:10px; }
    .persona-field strong { display:block; font-size:8.5pt; text-transform:uppercase; letter-spacing:1px; color:#0891B2; margin-bottom:3px; }
    .persona-quote { background:#E0F7FA; border-left:4px solid #0891B2; padding:12px 16px; margin:14px 0; border-radius:0 6px 6px 0; font-style:italic; color:#164E63; font-size:12pt; }
    .persona-tag { display:inline-block; background:#E0F7FA; color:#0E7490; border-radius:4px; padding:2px 8px; font-size:9pt; margin:2px 2px 2px 0; }
    footer { position:fixed; bottom:-50px; left:0; right:0; text-align:center; font-size:8pt; color:#999; }
</style>
</head>
<body>
<footer>{{ $title }} · Buyer Persona Pack</footer>
<div class="cover">
    <div class="badge">Buyer Persona Pack</div>
    <h1>{{ $title }}</h1>
    @if(!empty($tagline))<div class="tagline">{{ $tagline }}</div>@endif
    <span class="bar"></span>
    <div class="author">by {{ $author }}</div>
</div>
<div class="toc">
    <h2>Your Persona Profiles</h2>
    <ul>
        @foreach($sections as $i => $s)
        <li><span class="n">{{ $i + 1 }}</span>{{ $s['title'] }}</li>
        @endforeach
    </ul>
</div>
@foreach($sections as $i => $s)
<div class="section">
    <div class="section-hdr">
        <div class="num">{{ $i === 0 ? 'How to Use' : 'Persona Profile' }}</div>
        <h2>{{ $s['title'] }}</h2>
    </div>
    @foreach(preg_split("/\n\s*\n/", trim($s['body'])) as $para)
        @php $p = trim($para); @endphp
        @if($p !== '')
            @if(str_starts_with($p, '"') || str_starts_with($p, '"') || (str_contains($p, '"') && strlen($p) < 300))
                <div class="persona-quote">{!! nl2br(e($p)) !!}</div>
            @elseif(preg_match('/^(DEMOGRAPHICS|DAY IN LIFE|PROFESSIONAL|GOALS|PAIN POINTS|PREVIOUS ATTEMPTS|ONLINE BEHAVIOUR|BUYING PSYCHOLOGY|EXACT LANGUAGE|HOW TO MARKET)/', strtoupper($p)))
                <div class="persona-field"><strong>{{ strtok($p, "\n") }}</strong>{!! nl2br(e(trim(substr($p, strpos($p, "\n") + 1)))) !!}</div>
            @else
                <p>{!! nl2br(e($p)) !!}</p>
            @endif
        @endif
    @endforeach
</div>
@endforeach
</body>
</html>
