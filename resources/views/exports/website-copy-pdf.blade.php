<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 80px 60px 80px 60px; }
    body { font-family: DejaVu Serif, serif; font-size: 11.5pt; line-height: 1.65; color: #1A1A2E; }
    .cover { page-break-after:always; text-align:center; padding-top:160px; background:#1A0D33; margin:-80px -60px; padding-left:60px; padding-right:60px; min-height:100vh; }
    .cover h1 { font-size:30pt; font-weight:700; color:#fff; margin:0 0 16px; line-height:1.2; }
    .cover .badge { display:inline-block; background:#2563EB; color:#fff; font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:5px 16px; border-radius:20px; margin-bottom:32px; }
    .cover .tagline { font-size:13pt; color:rgba(255,255,255,0.7); margin-bottom:48px; }
    .cover .bar { display:block; width:50px; height:4px; background:#2563EB; margin:32px auto; }
    .cover .author { font-size:11pt; color:rgba(255,255,255,0.45); }
    .toc { page-break-after:always; padding-top:20px; }
    .toc h2 { color:#2563EB; font-size:18pt; border-bottom:2px solid #2563EB; padding-bottom:6px; margin-bottom:20px; }
    .toc li { list-style:none; padding:7px 0; border-bottom:1px dotted #ccc; font-size:10pt; }
    .toc .n { display:inline-block; width:26px; color:#2563EB; font-weight:700; }
    .section { page-break-before:always; }
    .section-hdr { border-left:5px solid #2563EB; padding:12px 18px; background:#EFF6FF; margin-bottom:22px; border-radius:0 6px 6px 0; }
    .section-hdr .num { font-size:8pt; letter-spacing:2px; text-transform:uppercase; color:#1D4ED8; margin-bottom:4px; font-family:DejaVu Sans,sans-serif; }
    .section-hdr h2 { margin:0; font-size:18pt; font-weight:700; color:#1E3A8A; }
    .section p { margin:0 0 10px; text-align:justify; }
    .copy-block { background:#F8FAFF; border:1px solid #BFDBFE; border-radius:6px; padding:12px 16px; margin:0 0 12px; }
    .copy-block strong { color:#1D4ED8; font-family:DejaVu Sans,sans-serif; font-size:9pt; text-transform:uppercase; letter-spacing:1px; }
    footer { position:fixed; bottom:-50px; left:0; right:0; text-align:center; font-size:8pt; color:#999; }
</style>
</head>
<body>
<footer>{{ $title }} · Website Copy Pack</footer>
<div class="cover">
    <div class="badge">Website Copy Pack</div>
    <h1>{{ $title }}</h1>
    @if(!empty($tagline))<div class="tagline">{{ $tagline }}</div>@endif
    <span class="bar"></span>
    <div class="author">by {{ $author }}</div>
</div>
<div class="toc">
    <h2>Pages in This Pack</h2>
    <ul>
        @foreach($sections as $i => $s)
        <li><span class="n">{{ $i + 1 }}</span>{{ $s['title'] }}</li>
        @endforeach
    </ul>
</div>
@foreach($sections as $i => $s)
<div class="section">
    <div class="section-hdr">
        <div class="num">{{ $i === 0 ? 'Usage Guide' : 'Page '.($i) }}</div>
        <h2>{{ $s['title'] }}</h2>
    </div>
    @foreach(preg_split("/\n\s*\n/", trim($s['body'])) as $para)
        @php $p = trim($para); @endphp
        @if($p !== '')
            @if(preg_match('/^(HEADLINE|HERO|SUBHEADLINE|BODY|CTA|META|BIO|ABOUT|MISSION|VALUES|STEPS|FAQ|SERVICE|CONTACT|TAGLINE)/', strtoupper($p)))
                <div class="copy-block">{!! nl2br(e($p)) !!}</div>
            @else
                <p>{!! nl2br(e($p)) !!}</p>
            @endif
        @endif
    @endforeach
</div>
@endforeach
</body>
</html>
