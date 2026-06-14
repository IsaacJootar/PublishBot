<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 80px 60px 80px 60px; }
    body { font-family: DejaVu Serif, serif; font-size: 11pt; line-height: 1.7; color: #1A1A2E; }
    .cover { page-break-after:always; text-align:center; padding-top:160px; background:#1A0D33; margin:-80px -60px; padding-left:60px; padding-right:60px; min-height:100vh; }
    .cover h1 { font-size:28pt; font-weight:700; color:#fff; margin:0 0 16px; line-height:1.2; }
    .cover .badge { display:inline-block; background:#6C3CE1; color:#fff; font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:5px 16px; border-radius:20px; margin-bottom:32px; }
    .cover .tagline { font-size:13pt; color:rgba(255,255,255,0.7); margin-bottom:48px; }
    .cover .bar { display:block; width:50px; height:4px; background:#6C3CE1; margin:32px auto; }
    .cover .author { font-size:11pt; color:rgba(255,255,255,0.45); }
    .cover .report-meta { font-size:10pt; color:rgba(255,255,255,0.4); margin-top:32px; }
    .toc { page-break-after:always; padding-top:20px; }
    .toc h2 { color:#6C3CE1; font-size:18pt; border-bottom:2px solid #6C3CE1; padding-bottom:6px; margin-bottom:20px; }
    .toc li { list-style:none; padding:7px 0; border-bottom:1px dotted #ccc; font-size:10pt; }
    .toc .n { display:inline-block; width:26px; color:#6C3CE1; font-weight:700; }
    .section { page-break-before:always; }
    .section-hdr { background:#F5F4FF; border-left:5px solid #6C3CE1; padding:16px 20px; margin-bottom:22px; border-radius:0 6px 6px 0; }
    .section-hdr .num { font-size:8pt; letter-spacing:2px; text-transform:uppercase; color:#6C3CE1; margin-bottom:4px; font-family:DejaVu Sans,sans-serif; }
    .section-hdr h2 { margin:0; font-size:18pt; font-weight:700; color:#1A0D33; }
    .section p { margin:0 0 12px; text-align:justify; }
    .insight-block { background:#EDE9FF; border-left:4px solid #6C3CE1; padding:12px 16px; margin:14px 0; border-radius:0 6px 6px 0; font-style:italic; color:#3B1F8C; }
    .insight-block strong { display:block; font-style:normal; font-family:DejaVu Sans,sans-serif; font-size:8pt; text-transform:uppercase; letter-spacing:1px; color:#6C3CE1; margin-bottom:4px; }
    footer { position:fixed; bottom:-50px; left:0; right:0; text-align:center; font-size:8pt; color:#999; }
</style>
</head>
<body>
<footer>{{ $title }} · Market Research Report</footer>
<div class="cover">
    <div class="badge">Niche Research Report</div>
    <h1>{{ $title }}</h1>
    @if(!empty($tagline))<div class="tagline">{{ $tagline }}</div>@endif
    <span class="bar"></span>
    <div class="author">by {{ $author }}</div>
    <div class="report-meta">Prepared {{ date('F Y') }}</div>
</div>
<div class="toc">
    <h2>Report Contents</h2>
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
            @if(str_contains($p, '⚡') || str_starts_with($p, 'Key Insight') || str_starts_with($p, '⚡ Key Insight'))
                <div class="insight-block"><strong>⚡ Key Insight</strong>{!! nl2br(e(str_replace(['⚡ Key Insight:', '⚡ Key Insight', '⚡'], '', $p))) !!}</div>
            @else
                <p>{!! nl2br(e($p)) !!}</p>
            @endif
        @endif
    @endforeach
</div>
@endforeach
</body>
</html>
