
<div class="header pb-6 pt-5 pt-lg-5 d-flex align-items-center" >
    <!-- Mask -->
    <span class="mask bg-gradient-gray opacity-8"></span>
    <!-- Header container -->
    <div class="container-fluid d-flex align-items-center">
        <div class="row">
            <div class="col-md-12 {{ $class ?? '' }}">
                <h3 class="display-3 text-white">{{ $title }}</h3>
                @if (isset($description) && $description)
                    <p class="text-white mt-0 mb-5">{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
</div> 