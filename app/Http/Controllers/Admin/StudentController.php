<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\ImportStudentRequest;
use App\Services\StudentManagementService;
use App\Models\StudentAcademic;
use App\Models\SchoolYear;
use App\Imports\StudentImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;  
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index()
    {
        $year = SchoolYear::current();

        if (!$year) {
            return view('admin.students.index', [
                'year' => null,
                'students' => collect(),
            ]);
        }

       $students = StudentAcademic::with('user')
        ->where('school_year_id', $year->id)
        ->orderBy('grade')
        ->orderBy('class_label') 
        ->paginate(10);

        return view('admin.students.index', compact('students', 'year'));
    }

    public function store(
        StoreStudentRequest $request,
        StudentManagementService $service
    ) {
        $service->createManual($request->validated());

        return back()->with('success', 'Siswa berhasil ditambahkan');
    }

    public function import(ImportStudentRequest $request)
    {
        Excel::import(new StudentImport, $request->file('file'));

        return back()->with('success', 'Import siswa berhasil');
    }

 public function update(Request $request, StudentAcademic $student)
{
    $request->validate([
        'name'        => 'required|string|max:255',
        'gender'      => 'required|in:L,P',
        'class_label' => 'nullable|string|max:10',
        'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $user = $student->user;

    if (!$user) {
        abort(404, 'User tidak ditemukan');
    }

    // Update Data User & Akademik
    $user->update([
        'name'   => $request->name,
        'gender' => $request->gender,
    ]);

    $student->update([
        'class_label' => $request->class_label,
    ]);

    // Handle Upload Foto Baru
    if ($request->hasFile('photo')) {
        // Hapus foto lama dari storage jika ada
        if ($user->photo) {
            Storage::disk('public')->delete('students/' . $user->photo);
        }

        $file = $request->file('photo');
        
        // Gunakan NISN + Timestamp + Random Int agar file unik 100%
        $photoName = $user->nisn . '_' . time() . '_' . random_int(100, 999) . '.' . $file->getClientOriginalExtension();

        $file->storeAs('students', $photoName, 'public');

        $user->update([
            'photo' => $photoName
        ]);
    }

    return back()->with('success', 'Data siswa berhasil diperbarui');
}


    public function deactivate(Request $request, StudentAcademic $academic)
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors([
                'admin_password' => 'Password admin salah'
            ]);
        }

        if ($academic->academic_status !== 'active') {
            return back()->withErrors([
                'status' => 'Siswa sudah nonaktif'
            ]);
        }

        $academic->user->update([
            'is_active' => 0,
        ]);

        $academic->update([
            'academic_status' => 'inactive',
        ]);

        return back()->with('success', 'Siswa berhasil dinonaktifkan');
    }

    public function importPhotos(Request $request)
{
    $request->validate([
        'zip_file' => 'required|file|mimes:zip',
    ]);

    $zipFile = $request->file('zip_file');
    $zipPath = $zipFile->getRealPath();
    $zip = new \ZipArchive;

    if ($zip->open($zipPath) !== TRUE) {
        return back()->withErrors([
            'zip_file' => 'ZIP tidak valid atau corrupt'
        ]);
    }

    $success = 0;
    $skipped = 0;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);

        // 1. Skip jika itu folder atau file sistem tersembunyi (seperti __MACOSX)
        if (substr($entry, -1) == '/' || str_contains($entry, '__MACOSX')) {
            continue;
        }

        $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));

        // 2. Validasi ekstensi gambar
        if (!in_array($extension, ['jpg','jpeg','png'])) {
            $skipped++;
            continue;
        }

        $nisn = pathinfo($entry, PATHINFO_FILENAME);
        $user = \App\Models\User::where('nisn', $nisn)->first();

        // 3. Skip jika NISN tidak terdaftar di database
        if (!$user) {
            $skipped++; 
            continue;
        }

        $fileContent = $zip->getFromIndex($i);

        if (!$fileContent) {
            $skipped++;
            continue;
        }

        // 4. Hapus foto lama sebelum ganti baru
        if ($user->photo) {
            Storage::disk('public')->delete('students/' . $user->photo);
        }

        // 5. Penamaan unik: nisn_waktu_random.ext
        $newName = $nisn . '_' . time() . '_' . random_int(100, 999) . '.' . $extension;

        Storage::disk('public')->put(
            'students/' . $newName,
            $fileContent
        );

        $user->update([
            'photo' => $newName
        ]);

        // 6. Optimasi Memory: Hapus variabel konten setelah disimpan
        unset($fileContent);
        
        $success++;
    }

    $zip->close();

    return back()->with(
        'success',
        "Import selesai. Berhasil: {$success}, Dilewati: {$skipped}"
    );
}

}
