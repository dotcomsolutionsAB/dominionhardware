<div class="bg-white mb-4 border p-3 p-sm-4">
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a href="#tab_description" data-toggle="tab" class="nav-link active" style="font-size: medium; font-weight: 700;">{{ translate('Description') }}</a>
        </li>
        <li class="nav-item">
            <a href="#tab_specifications" data-toggle="tab" class="nav-link" style="font-size: medium; font-weight: 700;">{{ translate('Specifications') }}</a>
        </li>
        {{-- @if ($detailedProduct->pdf != null)
        <li class="nav-item">
            <a href="#tab_downloads" data-toggle="tab" class="nav-link">{{ translate('Downloads') }}</a>
        </li>
        @endif --}}
    </ul>

    <!-- Tab Content -->
    <div class="tab-content pt-0">
        <!-- Description Tab Content -->
        <div class="tab-pane fade show active" id="tab_description">
            <div class="py-5" style="background-color: #f3f3f3; padding: 15px; border: 5px solid #fff;">
                <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                    <?php echo $detailedProduct->getTranslation('description'); ?>
                </div>
            </div>
        </div>

        <!-- Specifications Tab Content -->
        <div class="tab-pane fade" id="tab_specifications">
            <div class="py-5">
                <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                    {!! $detailedProduct->getTranslation('product_specification') !!}
                    <!-- Additional content can be added here -->
                </div>
            </div>
        </div>
        <!-- Video -->
        <!-- Uncomment the following code if you want to display the video tab -->
        {{-- <div class="tab-pane fade" id="tab_default_2">
            <div class="py-5">
                <div class="embed-responsive embed-responsive-16by9">
                    @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ get_url_params($detailedProduct->video_link, 'v') }}"></iframe>
                    @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                        <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                    @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                        <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    @endif
                </div>
            </div>
        </div> --}}
    </div>
</div>
