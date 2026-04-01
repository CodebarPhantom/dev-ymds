@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.my-activity.partials.submenu')
    <!-- Container -->
    <form action="{{ route('my-activity.my-permit.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="container-fixed">
            <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-bold leading-none text-gray-900">
                        {{ $data['pageTitle'] }}
                    </h1>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('my-activity.my-permit.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="submit" class="btn btn-sm text-center btn-success">
                            <i class="ki-filled ki-check"></i>{{ $data['pageTitle'] }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        @include('partials.attention')

        <div class="container-fixed">
            <div class="grid gap-5  mx-auto">
                <div class="card pb-2.5">
                    <div class="card-body grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tipe Izin
                            </label>
                            <select id="permitTypeSelect" class="select" name="permit_type" required="">
                                <option value="" selected="">--- Silahkan Pilih ---</option>
                                @foreach ($data['permitTypes'] as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Dari</label>
                            <input class="input" id="startDateInput" name="start_date" placeholder="mm/dd/yyyy"
                                type="datetime-local" value="" />
                        </div>

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Sampai</label>
                            <input class="input" id="endDateInput" name="end_date" placeholder="mm/dd/yyyy"
                                type="datetime-local" value="" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Catatan
                            </label>
                            <textarea class="textarea" name="note" placeholder="Catatan izin" rows="3" type="text" value=""></textarea>
                        </div>
                        <!-- Upload Photo (Hidden by Default) -->
                        <!-- Upload Photo (Hidden by Default) -->
                        <div id="uploadPhotoContainer" class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 hidden">
                            <label class="form-label max-w-56">
                                Upload Surat Sakit <span class="text-red-500">*</span>
                            </label>
                            <input id="photoInput" class="file-input border-gray-300" type="file" name="url_image" />
                        </div>

                        <!-- Error Message for Required Photo -->
                        <p id="photoError" class="text-red-500 text-sm hidden">Surat Sakit wajib diunggah jika memilih
                            "Sakit".</p>

                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
@push('javascript')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const permitTypeSelect = document.getElementById("permitTypeSelect");
            const uploadPhotoContainer = document.getElementById("uploadPhotoContainer");
            const photoInput = document.getElementById("photoInput");
            const photoError = document.getElementById("photoError");
            const startDateInput = document.getElementById('startDateInput');
            const endDateInput = document.getElementById('endDateInput');

            permitTypeSelect.addEventListener("change", function() {
                if (this.value === "2") { // "Sakit" is selected
                    uploadPhotoContainer.classList.remove("hidden");
                    photoInput.setAttribute("required", "required");
                    startDateInput.type = "date";
                    endDateInput.type = "date";
                } else {
                    uploadPhotoContainer.classList.add("hidden");
                    photoInput.removeAttribute("required");
                    photoInput.classList.remove("border-danger", "border-success");
                    photoError.classList.add("hidden");
                    startDateInput.type = "datetime-local";
                    endDateInput.type = "datetime-local";
                }
            });

            photoInput.addEventListener("change", function() {
                if (photoInput.files.length > 0) {
                    photoInput.classList.remove("border-danger");
                    photoInput.classList.add("border-success");
                    photoError.classList.add("hidden");
                } else {
                    photoInput.classList.remove("border-success");
                    photoInput.classList.add("border-danger");
                    if (permitTypeSelect.value === "2") {
                        photoError.classList.remove("hidden");
                    }
                }
            });
        });
    </script>
@endpush
