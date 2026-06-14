<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 80px 60px 80px 60px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.65; color: #1A1A2E; }
    .cover { page-break-after:always; text-align:center; padding-top:160px; background:#1A0D33; margin:-80px -60px; padding-left:60px; padding-right:60px; min-height:100vh; }
    .cover h1 { font-size:30pt; font-weight:700; color:#fff; margin:0 0 16px; line-height:1.2; }
    .cover .badge { display:inline-block; background:#F59E0B; color:#1A0D33; font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:5px 16px; border-radius:20px; margin-bottom:32px; }
    .cover .tagline { font-size:13pt; color:rgba(255,255,255,0.7); margin-bottom:48px; }
    .cover .bar { display:block; width:50px; height:4px; background:#F59E0B; margin:32px auto; }
    .cover .author { font-size:11pt; color:rgba(255,255,255,0.45); }
    .notion-note { background:#FEF3C7; border:2px solid #F59E0B; border-radius:8px; padding:16px 20px; margin:30px 0; }
    .notion-note h3 { color:#92400E; margin:0 0 8px; font-size:13pt; }
    .notion-note p { color:#78350F; margin:0 0 6px; font-size:10pt; }
    .notion-note ol { color:#78350F; margin:6px 0 0; padding-left:18px; font-size:10pt; }
    .toc { page-break-after:always; padding-top:20px; }
    .toc h2 { color:#F59E0B; font-size:18pt; border-bottom:2px solid #F59E0B; padding-bottom:6px; margin-bottom:20px; }
    .toc li { list-style:none; padding:7px 0; border-bottom:1px dotted #ccc; font-size:10pt; }
    .toc .n { display:inline-block; width:26px; color:#F59E0B; font-weight:700; }
    .section { page-break-before:always; }
    .section-hdr { background:#78350F; color:#fff; padding:14px 20px; border-radius:6px; margin-bottom:22px; }
    .section-hdr .num { font-size:8pt; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:4px; }
    .section-hdr h2 { margin:0; font-size:17pt; font-weight:700; color:#fff; }
    .section p { margin:0 0 10px; text-align:justify; }
    .db-block { background:#FEF9EE; border-left:3px solid #F59E0B; padding:10px 14px; margin:0 0 10px; border-radius:4px; font-size:10pt; }
    footer { position:fixed; bottom:-50px; left:0; right:0; text-align:center; font-size:8pt; color:#999; }
</style>
</head>
<body>
<footer>{{ $title }} · Notion Business OS Specification</footer>
<div class="cover">
    <div class="badge">Notion Business OS</div>
    <h1>{{ $title }}</h1>
    @if(!empty($tagline))<div class="tagline">{{ $tagline }}</div>@endif
    <span class="bar"></span>
    <div class="author">by {{ $author }}</div>
</div>
<div class="toc">
    <h2>What's Inside</h2>
    <div class="notion-note">
        <h3>🟧 Your action needed — Build in Notion</h3>
        <p>This PDF is your complete build specification. To sell this as a Notion template:</p>
        <ol>
            <li>Use this spec to build the workspace in your own Notion account (3-5 hours)</li>
            <li>Click Share → Publish to web → Allow duplicate as template → Copy link</li>
            <li>List the duplicate link on Gumroad, Etsy, or Selar</li>
            <li>One build. Unlimited sales. Update anytime — the link always shares the latest version.</li>
            <li>Alternative: Hire a Notion builder on Fiverr ($50-$150) to build from this spec.</li>
        </ol>
    </div>
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
            @if(str_starts_with($p, 'Database:') || str_starts_with($p, 'Property:') || str_starts_with($p, 'View:'))
                <div class="db-block">{!! nl2br(e($p)) !!}</div>
            @else
                <p>{!! nl2br(e($p)) !!}</p>
            @endif
        @endif
    @endforeach
</div>
@endforeach
</body>
</html>
