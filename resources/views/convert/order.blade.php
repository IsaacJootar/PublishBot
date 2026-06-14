<x-app-layout>
    <x-slot name="title">Confirm chapter order</x-slot>

    @php $busy = $run->status === "running"; @endphp

    <div style="max-width:680px;">
        <div style="margin-bottom:1rem;">
            <a href="{{ route("convert.index") }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← Convert Draft</a>
        </div>

        <div style="margin-bottom:1.25rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">{{ $run->topic }}</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">{{ count($run->uploaded_files ?? []) }} files uploaded. Claude will infer the reading order.</p>
        </div>

        @if($busy)
        {{-- Inference in progress --}}
        <div class="pai-card" style="text-align:center;padding:2.5rem 1.5rem;">
            <div style="width:40px;height:40px;border:3px solid #F59E0B;border-right-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 1rem;"></div>
            <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0 0 0.3rem;">Analysing your files...</p>
            <p style="font-size:0.82rem;color:#9B93B0;margin:0;">Reading filenames and content to determine the best chapter order.</p>
        </div>
        <script>
        (function() {
            const tick = setInterval(() => {
                fetch("{{ route("convert.status", $run) }}")
                    .then(r => r.json())
                    .then(d => { if (d.status !== "running") { clearInterval(tick); location.reload(); } })
                    .catch(() => {});
            }, 3000);
        })();
        </script>

        @elseif($run->inferred_order)
        {{-- Show inferred order for confirmation --}}
        @php $inf = $run->inferred_order; @endphp

        <div class="pai-card" style="margin-bottom:1.25rem;">
            <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude read your files</span>
            <h2 style="margin:0.5rem 0 0.25rem;font-size:1rem;font-weight:700;color:#0F0A1E;">Proposed chapter order</h2>
            @if(!empty($inf["summary"]))
            <p style="font-size:0.78rem;color:#5C5470;margin:0 0 1rem;">{{ $inf["summary"] }}</p>
            @endif

            <p style="font-size:0.75rem;color:#9B93B0;margin:0 0 0.5rem;">Drag chapters to reorder. Click Confirm when ready.</p>

            <ul id="chapter-list" style="list-style:none;padding:0;margin:0 0 1rem;">
                @foreach($inf["order"] as $item)
                <li draggable="true"
                    data-filename="{{ $item["filename"] }}"
                    style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0.85rem;background:#F5F4FF;border-radius:10px;margin-bottom:0.5rem;cursor:grab;border:1.5px solid transparent;transition:border-color 0.15s,background 0.15s;user-select:none;"
                    onmouseover="this.style.borderColor="#6C3CE1""
                    onmouseout="this.style.borderColor="transparent""
                    ondragstart="dragStart(event)"
                    ondragover="dragOver(event)"
                    ondrop="drop(event)"
                    ondragend="dragEnd(event)">
                    <span style="font-size:0.8rem;color:#9B93B0;flex-shrink:0;margin-top:2px;cursor:grab;">⠿</span>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0 0 0.15rem;">
                            {{ $item["position"] }}. {{ $item["title"] ?? $item["filename"] }}
                        </p>
                        <p style="font-size:0.72rem;color:#9B93B0;margin:0;">{{ $item["filename"] }}</p>
                        @if(!empty($item["reason"]))
                        <p style="font-size:0.7rem;color:#B0AAC0;margin:0.15rem 0 0;font-style:italic;">{{ $item["reason"] }}</p>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route("convert.order.confirm", $run) }}" id="order-form">
                @csrf
                <div id="order-inputs"></div>
                <div style="display:flex;gap:0.6rem;flex-wrap:wrap;">
                    <button type="submit" class="btn-primary" onclick="buildOrderInputs()">Confirm this order → start cleaning</button>
                </div>
            </form>
        </div>

        @else
        <div class="pai-card" style="text-align:center;padding:2rem;">
            <p style="font-size:0.85rem;color:#9B93B0;margin:0;">Waiting for order analysis...</p>
        </div>
        @endif
    </div>

    <script>
    let dragSrc = null;

    function dragStart(e) {
        dragSrc = e.currentTarget;
        e.dataTransfer.effectAllowed = "move";
        setTimeout(() => dragSrc.style.opacity = "0.4", 0);
    }

    function dragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
        e.currentTarget.style.background = "#EDE9FF";
        e.currentTarget.style.borderColor = "#6C3CE1";
        return false;
    }

    function drop(e) {
        e.preventDefault();
        const target = e.currentTarget;
        if (dragSrc === target) return;
        const list = document.getElementById("chapter-list");
        const items = [...list.children];
        const srcIdx = items.indexOf(dragSrc);
        const tgtIdx = items.indexOf(target);
        if (srcIdx < tgtIdx) list.insertBefore(dragSrc, target.nextSibling);
        else list.insertBefore(dragSrc, target);
        renumberItems();
    }

    function dragEnd(e) {
        dragSrc.style.opacity = "1";
        document.querySelectorAll("#chapter-list li").forEach(li => {
            li.style.background = "#F5F4FF";
            li.style.borderColor = "transparent";
        });
    }

    function renumberItems() {
        document.querySelectorAll("#chapter-list li").forEach((li, i) => {
            const title = li.querySelector("p:first-child");
            if (title) title.textContent = (i + 1) + ". " + title.textContent.replace(/^\d+\.\s*/, "");
        });
    }

    function buildOrderInputs() {
        const inputs = document.getElementById("order-inputs");
        inputs.innerHTML = "";
        document.querySelectorAll("#chapter-list li").forEach(li => {
            const inp = document.createElement("input");
            inp.type = "hidden";
            inp.name = "order[]";
            inp.value = li.dataset.filename;
            inputs.appendChild(inp);
        });
    }
    </script>
</x-app-layout>
