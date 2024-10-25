<div class="aiz-category-menu bg-white rounded-0 border-top" id="category-sidebar" style="width:270px;">
    <ul class="list-unstyled categories no-scrollbar mb-0 text-left">
        @foreach (get_level_zero_categories()->take(12) as $key => $category)
            @php
                $category_name = $category->getTranslation('name');
            @endphp
            <li class="category-nav-element border border-top-0" data-id="{{ $category->id }}">
                <a href="{{ route('products.category', $category->slug) }}"
                    class="text-truncate text-dark px-4 fs-14 d-block hov-column-gap-1">
                    <img class="cat-image lazyload mr-2 opacity-60" src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ isset($category->catIcon->file_name) ? my_asset($category->catIcon->file_name) : static_asset('assets/img/placeholder.jpg') }}" width="16" alt="{{ $category_name }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    <span class="cat-name has-transition">{{ $category_name }}</span>
                </a>
                <div class="test">
                    <div class="sub-cat-menu more c-scrollbar-light border p-4 shadow-none">
                        <div class="c-preloader text-center absolute-center">
                            <i class="las la-spinner la-spin la-3x opacity-70"></i>
                        </div>
                    </div>
                </div>

            </li>
        @endforeach
    </ul>
</div>

<style>

    .category-nav-element {
      position: relative;
    }
  
    .test {
      padding: 10px;
      position: absolute;
      top: 0;
      left: 100%; /* Position the submenu to the right of the parent element */
      z-index: 1000;
      background-color: #fff;
      display: none;
      max-height: 400px; /* Maximum height of the submenu before scrollbar appears */
      overflow-y: auto; /* Enable vertical scrollbar */
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for better visual separation */
      width: 700px; /* Adjust as needed */
    }
  
    .category-nav-element:hover .test {
      display: block;
    }
  
    .sub-category-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex; /* Display submenu items in a row */
      flex-wrap: wrap; /* Allow items to wrap */
    }
  
    .sub-category-list li {
      flex: 0 0 33.33%; /* Three columns, adjust width as needed */
      padding: 4px; /* Adjust spacing between items */
      box-sizing: border-box; /* Ensure padding doesn't affect width */
    }
  
    .sub-category-list li a {
      display: block;
      color: #333;
      text-decoration: none;
      transition: all 0.3s ease;
      padding: 8px; /* Padding around the link */
    }
  
    .sub-category-list li a:hover {
      text-decoration: underline;
    }
  
    /* Custom scrollbar styles */
    .test::-webkit-scrollbar {
      width: 10px; /* Width of the scrollbar */
    }
  
    .test::-webkit-scrollbar-track {
      background: #f1f1f1; /* Track color */
    }
  
    .test::-webkit-scrollbar-thumb {
      background: #888; /* Thumb color */
      border-radius: 5px; /* Rounded corners */
    }
  
    .test::-webkit-scrollbar-thumb:hover {
      background: #555; /* Hover state of the thumb */
    }
</style>

