@if(!empty($videoCategory?->video_path))
    @php $videoModalId = 'categoryVideoModal' . $videoCategory->id; @endphp
    <style>
        .qw-video-cta { background: linear-gradient(135deg, #111 0%, #2b2410 100%); border: 1.5px solid #D4AF37; cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; }
        .qw-video-cta:hover, .qw-video-cta:focus-visible { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(212, 175, 55, .35); outline: none; }
        .qw-video-cta-icon { position: relative; width: 52px; height: 52px; flex: 0 0 52px; border-radius: 50%; background: #D4AF37; color: #111; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .qw-video-cta-icon::before, .qw-video-cta-icon::after { content: ""; position: absolute; inset: 0; border-radius: 50%; border: 2px solid #D4AF37; animation: qwVideoPulse 1.8s ease-out infinite; }
        .qw-video-cta-icon::after { animation-delay: .9s; }
        .qw-video-cta-icon i { margin-left: 3px; }
        .qw-video-cta-badge { background: #dc3545; color: #fff; font-size: .62rem; letter-spacing: .5px; animation: qwVideoBlink 1.4s ease-in-out infinite; }
        @keyframes qwVideoPulse { 0% { transform: scale(1); opacity: .9; } 100% { transform: scale(1.7); opacity: 0; } }
        @keyframes qwVideoBlink { 0%, 100% { opacity: 1; } 50% { opacity: .55; } }
        @media (prefers-reduced-motion: reduce) { .qw-video-cta-icon::before, .qw-video-cta-icon::after, .qw-video-cta-badge { animation: none; } }
    </style>
    <div class="qw-video-cta rounded-4 shadow-sm mb-3 p-2 p-sm-3 d-flex align-items-center gap-3 text-white"
         role="button" tabindex="0" aria-label="Watch {{ $videoCategory->name }} video"
         data-bs-toggle="modal" data-bs-target="#{{ $videoModalId }}"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
        <span class="qw-video-cta-icon shadow"><i class="fa-solid fa-play"></i></span>
        <div class="flex-grow-1 min-w-0">
            <span class="qw-video-cta-badge badge rounded-pill fw-bold mb-1"><i class="fa-solid fa-video me-1"></i>VIDEO</span>
            <div class="fw-bold" style="font-size: .95rem; color: #F3E5AB;">Watch this collection video</div>
            <div class="small text-white-50">Tap to see the products in action before you shop</div>
        </div>
        <span class="btn btn-warning btn-sm rounded-pill fw-bold px-3 d-none d-sm-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-circle-play"></i> Play Video
        </span>
    </div>

    <div class="modal fade" id="{{ $videoModalId }}" tabindex="-1" aria-label="{{ $videoCategory->name }} video" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-black border-0 rounded-4 overflow-hidden">
                <div class="modal-header border-0 py-2">
                    <h6 class="modal-title text-white fw-bold mb-0">{{ $videoCategory->name }}</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" oncontextmenu="return false;">
                    <video class="w-100 d-block" style="max-height: 75vh;" controls playsinline preload="none"
                           controlsList="nodownload noremoteplayback noplaybackrate" disablePictureInPicture disableRemotePlayback
                           oncontextmenu="return false;" ondragstart="return false;">
                        <source src="{{ $videoCategory->video_stream_url }}">
                        Your browser does not support video playback.
                    </video>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById(@js($videoModalId));
            if (!modal) return;
            var video = modal.querySelector('video');
            modal.addEventListener('shown.bs.modal', function () { video.play().catch(function () {}); });
            modal.addEventListener('hidden.bs.modal', function () { video.pause(); });
        })();
    </script>
@endif
