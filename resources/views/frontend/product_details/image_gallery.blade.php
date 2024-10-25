<div class="sticky-top z-3 row gutters-10">
    @php
        $photos = [];
        $videos = [];
    @endphp
    @if ($detailedProduct->photos != null)
        @php
            $photos = explode(',', $detailedProduct->photos);
        @endphp
    @endif

    @if ($detailedProduct->video_provider == 'youtube' && $detailedProduct->video_link != null)
        @php
            $videos = explode(',', $detailedProduct->video_link);
        @endphp
    @endif

    <!-- Gallery Images -->
    <div class="col-12">
        <div class="aiz-carousel product-gallery arrow-inactive-transparent arrow-lg-none"
            data-nav-for='.product-gallery-thumb' data-fade='true' data-auto-height='true' data-arrows='true'>
            @if ($detailedProduct->digital == 0)
                @foreach ($detailedProduct->stocks as $key => $stock)
                    @if ($stock->image != null)
                        <div class="carousel-box img-zoom rounded-0 image-box">
                            <img class="img-fluid h-auto lazyload mx-auto"
                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ uploaded_asset($stock->image) }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    @endif
                @endforeach
            @endif

            @foreach ($photos as $key => $photo)
                <div class="carousel-box img-zoom rounded-0 image-box">
                    <img class="img-fluid h-auto lazyload mx-auto"
                        src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($photo) }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>
            @endforeach

            <!-- Video Frame Placeholder -->
            <div class="carousel-box img-zoom rounded-0 video-frame" style="display:none;">
                <div class="video-container">
                    <iframe class="embed-responsive-item" src="" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Thumbnail Images -->
    <div class="col-12 mt-3 d-none d-lg-block">
        <div class="aiz-carousel half-outside-arrow product-gallery-thumb" data-items='7' data-nav-for='.product-gallery'
            data-focus-select='true' data-arrows='true' data-vertical='false' data-auto-height='true'>

            @if ($detailedProduct->digital == 0)
                @foreach ($detailedProduct->stocks as $key => $stock)
                    @if ($stock->image != null)
                        <div class="carousel-box c-pointer rounded-0 image-thumbnail" data-variation="{{ $stock->variant }}">
                            <img class="lazyload mw-100 size-60px mx-auto border p-1"
                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ uploaded_asset($stock->image) }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    @endif
                @endforeach
            @endif

            @foreach ($photos as $key => $photo)
                <div class="carousel-box c-pointer rounded-0 image-thumbnail">
                    <img class="lazyload mw-100 size-60px mx-auto border p-1"
                        src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($photo) }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>
            @endforeach

            <!-- Video Thumbnails -->
            @foreach ($videos as $key => $video)
                <div class="carousel-box c-pointer rounded-0 video-thumbnail" data-video-id="{{ get_url_params($video, 'v') }}">
                    <img class="lazyload mw-100 size-60px mx-auto border p-1"
                        src="https://img.youtube.com/vi/{{ get_url_params($video, 'v') }}/hqdefault.jpg"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .video-frame {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
    }
    
    .video-frame iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    function showVideo(videoId) {
        const videoFrame = document.querySelector('.video-frame iframe');
        videoFrame.src = `https://www.youtube.com/embed/${videoId}`;
        document.querySelector('.video-frame').style.display = 'block';

        // Hide all image boxes
        document.querySelectorAll('.image-box').forEach(function (box) {
            box.style.display = 'none';
        });
    }

    function showImage() {
        document.querySelector('.video-frame').style.display = 'none';

        // Show all image boxes
        document.querySelectorAll('.image-box').forEach(function (box) {
            box.style.display = 'block';
        });
    }

    // Event listener for video thumbnails
    document.querySelectorAll('.video-thumbnail').forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            const videoId = this.getAttribute('data-video-id');
            showVideo(videoId);
        });
    });

    // Event listener for image thumbnails
    document.querySelectorAll('.image-thumbnail').forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            showImage();
        });
    });
});

</script>