@extends('layouts.main')

@section('content')
    <!-- Container -->
    {{-- @include('backoffice.config.company.partials.submenu') --}}
    <!-- Container -->
    <form id="employeeForm" action="{{ route('workforce.employee.store') }}" data-employee-id="" method="post">
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
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('workforce.employee.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button id="submit-button" type="submit" class="btn btn-sm text-center btn-success">
                            <i class="ki-filled ki-check"></i>{{ $data['pageTitle'] }}
                        </button>
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
                                        <input checked="true" class="radio " name="is_active" type="radio"
                                            value="1" />
                                        Active
                                    </label>
                                    <label class="form-label flex items-center gap-2.5 text-nowrap ">
                                        <input class="radio" name="is_active" type="radio" value="0" />
                                        Inactive
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jumlah Cuti
                                </label>
                                <input class="input" name="leave" placeholder="Jumlah Cuti" type="number"
                                    value="0" />
                            </div>

                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Status Karyawan
                                </label>
                                <select class="select" name="employee_status">
                                    <option value="Kontrak">
                                        Kontrak
                                    </option>
                                    <option value="Harian">
                                        Harian
                                    </option>
                                    <option value="Tetap">
                                        Tetap
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Tanggal Mulai Kerja
                                </label>
                                <input class="input" name="start_work_date" placeholder="Tanggal Mulai Kerja"
                                    type="date" value="" />
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nama
                            </label>
                            <input class="input" name="name" placeholder="Nama Karyawan" type="text"
                                value="" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Kode Karyawan
                            </label>
                            <input class="input" name="employee_code" placeholder="Kode Karyawan" disabled type="text"
                                value="2204XXXXX (Auto Generate)" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nomor KTP
                            </label>
                            <input class="input" name="no_ktp" placeholder="Nomor KTP" type="text" value="" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nomor HP
                            </label>
                            <input class="input" name="phone" placeholder="Nomor HP" type="text" value="" />
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Divisi
                                </label>
                                <div class="relative inline-block w-full combobox"
                                    data-options='@json($data['divisions'])' data-multiple="false">
                                    <div
                                        class="pill-container flex items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text" placeholder="Search..."
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input id="division_id" type="hidden" class="selected-data" name="division_id" />
                                    <div
                                        class="dropdown-menu absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                        <div class="options-container max-h-40 overflow-y-auto"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Departemen
                                </label>
                                <div class="relative inline-block w-full combobox" id="department_combobox"
                                    data-api="{{ route('api.v1.departement.get-departement-for-select') }}"
                                    data-collection="departements" data-params='{"division_id": ""}'
                                    data-multiple="false">
                                    <div
                                        class="pill-container flex items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text" placeholder="Search..."
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input type="hidden" class="selected-data" name="department_id" value="" />
                                    <div
                                        class="dropdown-menu absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                        <div class="options-container max-h-40 overflow-y-auto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jabatan
                                </label>
                                <select class="select" name="role_id" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    @foreach ($data['roles'] as $role)
                                        <option value="{{ $role->id }}">
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Atasan
                                </label>
                                <select class="select" name="report_to_employee_id">
                                    <option value="" selected="">--- Vacant ---</option>
                                    @foreach ($data['employees'] as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Tempat Lahir
                                </label>
                                <input class="input" name="pob" placeholder="Tempat Lahir" type="text"
                                    value="" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Tanggal Lahir
                                </label>
                                <input class="input" name="dob" placeholder="Tanggal Lahir" type="date"
                                    value="" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Status Perkawinan
                                </label>
                                <select class="select" name="residence_status" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    <option value="Belum Kawin">
                                        Belum Kawin
                                    </option>
                                    <option value="Kawin">
                                        Kawin
                                    </option>
                                    <option value="Cerai Hidup">
                                        Cerai Hidup
                                    </option>
                                    <option value="Cerai Mati">
                                        Cerai Mati
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Agama
                                </label>
                                <select class="select" name="religion" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    <option value="Islam">
                                        Islam
                                    </option>
                                    <option value="Kristen">
                                        Kristen
                                    </option>
                                    <option value="Katolik">
                                        Katolik
                                    </option>
                                    <option value="Hindu">
                                        Hindu
                                    </option>
                                    <option value="Buddha">
                                        Buddha
                                    </option>
                                    <option value="Khonghucu">
                                        Khonghucu
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jenis Kelamin
                                </label>
                                <select class="select" name="gender" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    <option value="Laki-laki">
                                        Laki-laki
                                    </option>
                                    <option value="Perempuan">
                                        Perempuan
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jumlah Tanggungan
                                </label>
                                <input class="input" name="number_of_dependents" placeholder="Jumlah Tanggungan"
                                    type="number" value="" />
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Alamat
                            </label>
                            <textarea class="textarea" name="address" placeholder="Alamat tempat tinggal" rows="3" type="text"
                                value="" /></textarea>
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
                                <select class="select" name="salary_method">
                                    <option value="Transfer">
                                        Transfer
                                    </option>
                                    <option value="Cash">
                                        Cash
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Gaji
                                </label>
                                <input class="input text-right" name="salary_value" placeholder="10000000"
                                    type="number" value="" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Bank
                                </label>
                                <select class="select" name="bank_id" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    @foreach ($data['baseBanks'] as $baseBank)
                                        <option value="{{ $baseBank->id }}">
                                            {{ $baseBank->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Nomor Rekening
                                </label>
                                <input class="input text-right" name="bank_account_number" placeholder="xxxxxxxxxx"
                                    type="input" value="" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Penghasilan Tidak Kena Pajak (PTKP)
                                </label>
                                <select class="select" name="ptkp_id" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    @foreach ($data['basePtkps'] as $basePtkp)
                                        <option value="{{ $basePtkp->id }}">
                                            {{ $basePtkp->tax_status }} - Rp.{{ number_format($basePtkp->annual_limit) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Nomor Pokok Wajib Pajak (NPWP)
                                </label>
                                <input class="input text-right" name="npwp_account_number" placeholder="xxxxxxxxxx"
                                    type="input" value="" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    No. BPJS Ketenagakerjaan
                                </label>
                                <input class="input text-right" name="bpjs_tk_account_number" placeholder="xxxxxxxxxx"
                                    type="input" value="" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    No. BPJS Kesehatan
                                </label>
                                <input class="input text-right" name="bpjs_ks_account_number" placeholder="xxxxxxxxxx"
                                    type="input" value="" />
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
                        <button id="btn-work-history" class="btn btn-sm text-center  bg-[#4b5563] text-white">
                            <i class="ki-filled ki-plus"></i>Tambah Riwayat Pekerjaan
                        </button>
                    </div>
                    <div class="card-body grid gap-5" id="work-history-container">
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
                        <!-- New fields will be added here dynamically -->
                    </div>
                </div>
            </div>
            <div class="grid gap-5 mt-5 mx-auto">
                <div class="card pb-2.5">
                    <div class="card-header card-header flex flex-wrap items-center lg:items-end justify-between gap-5">
                        <h3 class="card-title">
                            Riwayat Pendidikan
                        </h3>
                        <button id="btn-education-history" class="btn btn-sm text-center bg-[#4b5563] text-white">
                            <i class="ki-filled ki-plus"></i>Tambah Riwayat Pendidikan
                        </button>
                    </div>
                    <div class="card-body grid gap-5" id="education-history-container">
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
                        <!-- New rows will be added here -->
                    </div>
                </div>
            </div>
            <div class="grid gap-5 mt-5 mx-auto">
                <div class="card pb-2.5">
                    <div class="card-header card-header flex flex-wrap items-center lg:items-end justify-between gap-5">
                        <h3 class="card-title">
                            Perjanjian Kerja
                        </h3>
                        <button id="btn-employee-agreement" class="btn btn-sm text-center bg-[#4b5563] text-white">
                            <i class="ki-filled ki-plus"></i>Tambah Perjanjian Kerja
                        </button>
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
                        <!-- New rows will be added here -->
                    </div>
                </div>
            </div>
            <div class="grid gap-5 mt-5 mx-auto">
                <div class="card pb-2.5">
                    <div class="card-header card-header flex flex-wrap items-center lg:items-end justify-between gap-5">
                        <h3 class="card-title">Berkas Pendukung</h3>
                        <button id="btn-support-files" class="btn btn-sm text-center bg-[#4b5563] text-white">
                            <i class="ki-filled ki-plus"></i> Tambah Berkas Pendukung
                        </button>

                        <input type="hidden" id="uploaded-file-ids-employee" name="uploaded_file_ids">

                    </div>

                    <div class="card-body grid gap-2.5 lg:gap-5" id="support-files-container">

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
                                <input class="input text-right" name="body_height" placeholder="172" type="number"
                                    value="" />
                            </div>

                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Berat Badan
                                </label>
                                <input class="input text-right" name="body_weight" placeholder="75" type="number"
                                    value="" />
                            </div>
                        </div>

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Golongan Darah
                            </label>
                            <select class="select" name="blood_group">
                                <option value="" selected="">--- Silahkan Pilih ---</option>
                                <option value="O">
                                    O
                                </option>
                                <option value="A">
                                    A
                                </option>
                                <option value="B">
                                    B
                                </option>
                                <option value="AB">
                                    AB
                                </option>
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Riwayat Kesehatan
                            </label>
                            <textarea class="textarea" name="health_history" placeholder="Riwayat Kesehatan" rows="3" type="text"
                                value="" /></textarea>
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
                                <input class="input" name="emergency_contact_name" placeholder="Nama" type="text"
                                    value="" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Hubungan Kekerabatan
                                </label>
                                <input class="input" name="emergency_contact_relation"
                                    placeholder="Hubungan Kekerabatan" type="text" value="" />
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Alamat
                            </label>
                            <textarea class="textarea" name="emergency_contact_address" placeholder="Alamat tempat tinggal" rows="3"
                                type="text" value="" /></textarea>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                No. Handphone
                            </label>
                            <input class="input" name="emergency_contact_phone" placeholder="No. Handphone"
                                type="text" value="" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <form id="upload-form" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" id="support-files-input" name="files[]" multiple hidden>
        <!-- Unique id for file upload form -->
        {{-- <input type="hidden" id="uploaded-file-ids-upload" name="uploaded_file_ids"> --}}
    </form>


    <!-- End of Container -->
@endsection

@include('backoffice.workforce.employee.include.employee-crud-js')
@include('partials.advanced-selectbox')
@push('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setupLinkedCombobox('.combobox[data-options]', '#department_combobox', 'division_id');
        });
    </script>
@endpush
