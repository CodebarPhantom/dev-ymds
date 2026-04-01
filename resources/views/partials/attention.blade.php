<div class="container-fixed">
    <div class="grid pb-2.5 mx-auto">
        @if ($errors->any())
        <!--begin:: Alert-->
        <div class="flex flex-col justify-center gap-1 rounded-lg p-4 bg-danger-light">
            <h3 class="text-md leading-none font-semibold text-gray-900">
                Attention Required!
            </h3>
            @foreach ($errors->all() as $error)
                <p class="text-gray-700 text-2sm font-normal">
                    {{ $error }}
                </p>
            @endforeach
        </div>
        <!--end:: Alert-->
        @endif
    </div>
</div>
<div class="container-fixed">
    <div class="grid  pb-2.5 mx-auto">
        @if(session('flashMessageSuccess'))
        <!--begin:: Alert-->
        <div class="flex flex-col justify-center gap-1 rounded-lg p-4 bg-success-light">
            <h3 class="text-md leading-none font-semibold text-gray-900">
                Task was successful!
            </h3>

            @foreach (session('messages', []) as $index => $message)
                <p class="text-gray-700 text-2sm font-normal">
                    {{ $message }}
                </p>
            @endforeach
        </div>
        <!--end:: Alert-->
        @endif
    </div>
</div>


<div id="error-api-attention" class="container-fixed hidden">

    <div class="grid pb-2.5 mx-auto">
        <!--begin:: Alert-->
        <div class="flex flex-col justify-center gap-1 rounded-lg p-4 bg-danger-light">
            <h3 class="text-md leading-none font-semibold text-gray-900">
                Attention Required!
            </h3>
            <div id="error-container">

            </div>
        </div>
    </div>

</div>
