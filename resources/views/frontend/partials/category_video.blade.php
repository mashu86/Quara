@if(!empty($videoCategory?->video_path))
    @php $videoModalId = 'categoryVideoModal' . $videoCategory->id; @endphp
    <div class="mb-3">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#{{ $videoModalId }}">
            <i class="fa-solid fa-circle-play text-warning fs-5"></i> Play Video
        </button>
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
