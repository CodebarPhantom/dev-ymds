@extends('layouts.main')

@section('content')
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="flex items-center gap-2.5">
                    <a class="btn text-center btn-sm btn-primary" href="{{ route('workforce.employee.index') }}">
                        <i class="ki-filled ki-left"></i></i>Kembali
                    </a>
                </div>
                <div class="flex items-center gap-2.5">
                    <a class="btn btn-sm text-center btn-warning"
                        href="{{ route('workforce.employee.edit', $data['employee']['id']) }}">
                        <i class="ki-filled ki-notepad-edit"></i>{{ $data['editPageTitle'] }}
                    </a>
                </div>
            </div>

        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid gap-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header grid gap-5">
                    <h3 class="card-title">
                        Data Diri
                    </h3>
                </div>
                <div class="card-body grid gap-5">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Karyawan Aktif?
                            </label>
                            <div class="flex gap-12">
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input disabled class="radio" name="status" type="radio" value="1"
                                        {{ $data['employee']['is_active'] == true ? 'checked' : '' }} />
                                    Active
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap ">
                                    <input disabled class="radio" name="status" type="radio" value="0"
                                        {{ $data['employee']['is_active'] == false ? 'checked' : '' }} />
                                    Inactive
                                </label>
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Jumlah Cuti
                            </label>
                            <input readonly="true" class="input" name="leave" placeholder="Jumlah Cuti"
                                type="number" value="{{ $data['employee']['leave'] }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Status Karyawan
                            </label>
                            <select class="select" readonly="true" name="employee_status">
                                <option value="" selected="">{{ $data['employee']['employee_status'] }}</option>

                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tanggal Mulai Kerja
                            </label>
                            <input class="input" readonly name="start_work_date" placeholder="Tanggal Mulai Kerja" type="date"  value="{{ $data['employee']['start_work_date'] }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Nama
                        </label>
                        <input class="input" readonly="true" name="name" type="text"
                            value="{{ $data['employee']['name'] }}" />
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Kode Karyawan
                        </label>
                        <input class="input" readonly="true" name="employee_code" type="text"
                            value="{{ $data['employee']['employee_code'] }}" />
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Nomor KTP
                        </label>
                        <input class="input" readonly="true" name="no_ktp" type="text"
                            value="{{ $data['employee']['no_ktp'] }}" />
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Nomor HP
                        </label>
                        <input class="input" readonly="true" name="phone" type="text"
                            value="{{ $data['employee']['phone'] }}" />
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Divisi
                            </label>
                            <select class="select" readonly="true" name="division_id" required>
                                <option value="" selected="">{{ $data['employee']['division']['name'] }}</option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Departemen
                            </label>
                            <select class="select" readonly="true" name="department_id" required>
                                <option value="" selected="">{{ $data['employee']['department']['name'] }}
                                </option>
                            </select>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Jabatan
                            </label>
                            <select class="select" readonly="true" name="role_id" required>
                                <option value="" selected="">{{ $data['employee']['role']['name'] }}</option>
                            </select>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Atasan
                            </label>
                            <select class="select" readonly="true" name="role_id" required>
                                <option value="" selected="">
                                    {{ $data['employee']['reportTo']['name'] ?? 'Vacant' }}</option>
                            </select>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tempat Lahir
                            </label>
                            <input class="input" readonly="true" name="pob" type="text"
                                value="{{ $data['employee']['pob'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tanggal Lahir
                            </label>
                            <input class="input" readonly="true" name="dob" type="date"
                                value="{{ $data['employee']['dob'] }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Status Perkawinan
                            </label>
                            <select class="select" readonly="true" name="residence_status" required>
                                <option value="" selected="">{{ $data['employee']['residence_status'] }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Agama
                            </label>
                            <select class="select" readonly="true" name="religion" required>
                                <option value="" selected="">{{ $data['employee']['religion'] }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Jenis Kelamin
                            </label>
                            <select class="select" readonly="true" name="gender" required>
                                <option value="" selected="">{{ $data['employee']['gender'] }}</option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Jumlah Tanggungan
                            </label>
                            <input class="input" readonly="true" name="number_of_dependents" type="number" readonly="true"
                                value="{{ $data['employee']['number_of_dependents'] }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Alamat
                        </label>
                        <textarea readonly="true" class="textarea" name="address" rows="3" type="text" value="" />{{ $data['employee']['address'] }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header grid gap-5">
                    <h3 class="card-title">
                        Metode Penggajian
                    </h3>
                </div>
                <div class="card-body grid gap-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Metode
                            </label>
                            <select class="select" readonly="true" name="salary_method">
                                <option value="Transfer">
                                    {{ $data['employee']['salary_method'] }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Gaji
                            </label>
                            <input class="input text-right" name="salary_value" type="number" readonly="true"
                                value="{{ $data['employee']['salary_value'] }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Bank
                            </label>
                            <select class="select" readonly="true" name="bank_id" required>
                                <option value="" selected="">{{ $data['employee']['bank']['name'] }}</option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nomor Rekening
                            </label>
                            <input readonly="true" class="input text-right" name="bank_account_number" type="input"
                                value="{{ $data['employee']['bank_account_number'] }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Penghasilan Tidak Kena Pajak (PTKP)
                            </label>
                            <select class="select" name="ptkp_id" readonly="true" required>
                                    <option value="" selected="">
                                        {{ $data['employee']['ptkp']['tax_status'] }} - Rp.{{ number_format($data['employee']['ptkp']['annual_limit']) }}
                                    </option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nomor Pokok Wajib Pajak (NPWP)
                            </label>
                            <input class="input text-right" readonly="true"  name="npwp_account_number" placeholder="xxxxxxxxxx"
                                type="input" value="{{ $data['employee']['npwp_account_number'] }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                No. BPJS Ketenagakerjaan
                            </label>
                            <input readonly="true" class="input text-right" name="bpjs_tk_account_number" type="input"
                                value="{{ $data['employee']['bpjs_tk_account_number'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                No. BPJS Kesehatan
                            </label>
                            <input readonly="true" class="input text-right" name="bpjs_ks_account_number" type="input"
                                value="{{ $data['employee']['bpjs_ks_account_number'] }}" />
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header flex flex-wrap items-center lg:items-end justify-between gap-5 ">
                    <h3 class="card-title ">
                        Riwayat Perkerjaan
                    </h3>
                </div>
                <div class="card-body grid gap-5" id="work-history-container">
                    <!-- New fields will be added here dynamically -->
                    <div class="flex gap-2 items-center">
                        <label class="form-label text-center">
                            Nama Perusahaan
                        </label>
                        <label class="form-label text-center">
                            Jabatan
                        </label>
                        <label class="form-label text-center">
                            Tgl. Mulai
                        </label>
                        <label class="form-label text-center">
                            Tgl. Selesai
                        </label>
                        <label class="form-label text-center">
                            Alasan Berhenti
                        </label>
                        <label class="form-label text-center">
                            Gaji
                        </label>
                    </div>
                    @foreach ($data['employee']['workHistories'] as $workHistories)
                        <div class="flex gap-3 items-center">
                            <input readonly="true" value="{{ $workHistories->company_name }}" type="text"
                                name="company_name[]" placeholder="Perusahaan"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $workHistories->role_name }}" type="text" name="role_name[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $workHistories->start_date }}" type="date" name="start_date[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $workHistories->end_date }}" type="date" name="end_date[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $workHistories->reason }}" type="text" name="reason[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $workHistories->salary }}" type="number" name="salary[]"
                                class="input w-full border border-gray-300 p-2">
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header card-header flex flex-wrap items-center lg:items-end justify-between gap-5">
                    <h3 class="card-title">
                        Riwayat Pendidikan
                    </h3>
                </div>
                <div class="card-body grid gap-5" id="education-history-container">
                    <!-- New rows will be added here -->
                    <div class="flex gap-2 items-center">
                        <label class="form-label text-center">
                            Nama Institusi
                        </label>
                        <label class="form-label text-center">
                            Kota
                        </label>
                        <label class="form-label text-center">
                            Tahun Mulai
                        </label>
                        <label class="form-label text-center">
                            Tahun Selesai
                        </label>
                        <label class="form-label text-center">
                            Jurusan
                        </label>
                    </div>
                    @foreach ($data['employee']['educationHistories'] as $educationHistories)
                        <div class="flex gap-3 items-center">
                            <input readonly="true" value="{{ $educationHistories->education_name }}" type="text"
                                name="education_name[]" class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $educationHistories->city }}" type="text" name="city[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $educationHistories->start_year }}" type="number"
                                name="start_year[]" class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $educationHistories->end_year }}" type="number" name="end_year[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $educationHistories->major }}" type="text" name="major[]"
                                class="input w-full border border-gray-300 p-2">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header card-header flex flex-wrap items-center lg:items-end justify-between gap-5">
                    <h3 class="card-title">
                        Perjanjian Kerja
                    </h3>
                </div>
                <div class="card-body grid gap-5" id="employee-agreement-container">
                    <div class="flex gap-2 items-center">
                        <label class="form-label text-center">
                            Nama Perjanjian Kerja
                        </label>
                        <label class="form-label text-center">
                            Tanggal Mulai
                        </label>
                        <label class="form-label text-center">
                            Tanggal Selesai
                        </label>
                    </div>
                    @foreach ($data['employee']['employeeAgreements'] as $employeeAgreements)
                        <div class="flex gap-3 items-center">
                            <input readonly="true" value="{{ $employeeAgreements->name }}" type="text" name="name[]"
                                class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $employeeAgreements->start_date }}" type="date"
                                name="start_date[]" class="input w-full border border-gray-300 p-2">
                            <input readonly="true" value="{{ $employeeAgreements->end_date }}" type="date" name="end_date[]"
                                class="input w-full border border-gray-300 p-2">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header card-header flex flex-wrap items-center lg:items-end justify-between gap-5">
                    <h3 class="card-title">Berkas Pendukung</h3>
                    <input type="hidden" id="uploaded-file-ids-employee" name="uploaded_file_ids">

                </div>

                @php
                    function formatFileSize($size)
                    {
                        $sizeInMB = $size / (1024 * 1024);
                        if ($sizeInMB < 1) {
                            return number_format($size / 1024, 2) . ' KB';
                        } else {
                            return number_format($sizeInMB, 2) . ' MB';
                        }
                    }
                    use Carbon\Carbon;
                @endphp

                <div class="card-body grid gap-2.5 lg:gap-5" id="support-files-container">
                    @foreach ($data['employee']['files'] as $file)
                        @php
                            switch ($file->extension) {
                                case 'pdf':
                                    $iconPath = asset('assets/media/file-types/pdf.svg');
                                    break;
                                case 'docx':
                                case 'doc':
                                    $iconPath = asset('assets/media/file-types/word.svg');
                                    break;
                                case 'xlsx':
                                case 'xls':
                                    $iconPath = asset('assets/media/file-types/xls.svg');
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                    $iconPath = asset('assets/media/file-types/image.svg');
                                    break;
                                default:
                                    $iconPath = asset('assets/media/file-types/mail.svg');
                                    break;
                            }
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="flex items-center grow gap-2.5">
                                <img src="{{ $iconPath }}">
                                <div class="flex flex-col">
                                    <a href="{{ url('/' . $file->path) }}"
                                        class="text-sm font-medium text-gray-900 cursor-pointer hover:text-primary mb-px"
                                        target="_blank">
                                        {{ $file->name }}
                                    </a>
                                    <span class="text-xs text-gray-700">
                                        @php
                                            $dateTime = Carbon::parse($file->created_at); // Replace $file->created_at with your actual date field
                                            $formattedDateTime = $dateTime->format('d M Y, H:i');
                                        @endphp
                                        {{ formatFileSize($file->size) }} {{ $formattedDateTime }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>



        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header grid gap-5">
                    <h3 class="card-title">
                        Riwayat Kesehatan
                    </h3>
                </div>
                <div class="card-body grid gap-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tinggi Badan
                            </label>
                            <input class="input text-right" name="body_height" type="number" readonly="true"
                                value="{{ $data['employee']['body_height'] }}" />
                        </div>

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Berat Badan
                            </label>
                            <input class="input text-right" name="body_weight" type="number" readonly="true"
                                value="{{ $data['employee']['body_weight'] }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Golongan Darah
                        </label>
                        <select class="select" readonly="true" name="blood_group">
                            <option value="" selected="">{{ $data['employee']['blood_group'] }}</option>
                        </select>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Riwayat Kesehatan
                        </label>
                        <textarea readonly="true" class="textarea" name="health_history" rows="3" type="text" value="" />{{ $data['employee']['health_history'] }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 mt-5 mx-auto">
            <div class="card pb-2.5">
                <div class="card-header grid gap-5">
                    <h3 class="card-title">
                        Kontak Darurat
                    </h3>
                </div>
                <div class="card-body grid gap-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nama Kontak
                            </label>
                            <input class="input" readonly="true" name="emergency_contact_name" type="text"
                                value="{{ $data['employee']['emergency_contact_name'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Hubungan Kekerabatan
                            </label>
                            <input class="input" readonly="true" name="emergency_contact_relation" type="text"
                                value="{{ $data['employee']['emergency_contact_relation'] }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Alamat
                        </label>
                        <textarea readonly="true" class="textarea" name="emergency_contact_relation_address" rows="3" type="text"
                            value="" />{{ $data['employee']['emergency_contact_relation_address'] }}</textarea>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            No. Handphone
                        </label>
                        <input class="input" readonly="true" name="emergency_contact_relation_phone" type="text"
                            value="{{ $data['employee']['emergency_contact_relation_phone'] }}" />
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- End of Container -->
@endsection

@push('javascript')
@endpush
