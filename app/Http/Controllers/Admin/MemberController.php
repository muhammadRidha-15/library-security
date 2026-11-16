<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMemberRequest;
use App\Http\Requests\Admin\UpdateMemberRequest;
use App\Models\Anggota;
use App\Services\AesGcm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of library members (Canvas Node 13).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Anggota::class);

        $search = $request->query('q');
        $status = $request->query('status', 'semua'); // 'aktif' | 'nonaktif' | 'semua'

        $query = Anggota::query()->withCount([
            'peminjaman as pinjaman_aktif_count' => function ($q) {
                $q->where('status', 'dipinjam');
            },
        ]);

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $anggota = $query->orderBy('nama')->paginate(15)->withQueryString();

        $totalUser = Anggota::where('role', 'user')->count();
        $totalUserAktif = Anggota::where('role', 'user')->where('is_active', true)->count();
        $totalUserNonaktif = Anggota::where('role', 'user')->where('is_active', false)->count();

        return view('admin.anggota.index', compact('anggota', 'search', 'status', 'totalUser', 'totalUserAktif', 'totalUserNonaktif'));
    }

    /**
     * Show the form for creating a new member (Canvas Node 14).
     */
    public function create(): View
    {
        Gate::authorize('create', Anggota::class);

        return view('admin.anggota.create');
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(StoreMemberRequest $request, AesGcm $aes): RedirectResponse
    {
        Gate::authorize('create', Anggota::class);

        $validated = $request->validated();

        // SEC-02: Hash::make Bcrypt default cost 12
        // SEC-10: Akun baru dari admin selalu role 'user'
        $member = Anggota::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_active' => true,
            'telepon_enc' => null,
        ]);

        // SEC-03: Enkripsi nomor telepon jika diberikan
        if (! empty($validated['telepon'])) {
            $aad = "anggota:{$member->id}:telepon:v1";
            $envelope = $aes->encrypt($validated['telepon'], $aad);
            $member->update(['telepon_enc' => $envelope]);
        }

        return redirect()
            ->route('admin.anggota.index')
            ->with('success', "Akun anggota \"{$member->nama}\" berhasil dibuat.");
    }

    /**
     * Display the specified member details in admin area.
     */
    public function show(Anggota $anggota): RedirectResponse
    {
        Gate::authorize('view', $anggota);

        return redirect()->route('admin.anggota.edit', $anggota);
    }

    /**
     * Show the form for editing the member (Canvas Node 15).
     */
    public function edit(Anggota $anggota, AesGcm $aes): View
    {
        Gate::authorize('update', $anggota);

        $teleponPlaintext = null;
        if ($anggota->telepon_enc !== null) {
            $aad = "anggota:{$anggota->id}:telepon:v1";
            $teleponPlaintext = $aes->decrypt($anggota->telepon_enc, $aad);
        }

        $borrowedLoans = $anggota->peminjaman()->with('buku')->where('status', 'dipinjam')->get();

        return view('admin.anggota.edit', compact('anggota', 'teleponPlaintext', 'borrowedLoans'));
    }

    /**
     * Update member information in storage.
     */
    public function update(UpdateMemberRequest $request, Anggota $anggota, AesGcm $aes): RedirectResponse
    {
        Gate::authorize('update', $anggota);

        $validated = $request->validated();

        $updateData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
        ];

        if (array_key_exists('telepon', $validated)) {
            if (empty($validated['telepon'])) {
                $updateData['telepon_enc'] = null;
            } else {
                $aad = "anggota:{$anggota->id}:telepon:v1";
                $updateData['telepon_enc'] = $aes->encrypt($validated['telepon'], $aad);
            }
        }

        $anggota->update($updateData);

        return redirect()
            ->route('admin.anggota.edit', $anggota)
            ->with('success', "Data anggota \"{$anggota->nama}\" berhasil diperbarui.");
    }

    /**
     * Toggle active/inactive status of the member (FR-12).
     */
    public function toggleStatus(Request $request, Anggota $anggota): RedirectResponse
    {
        // Deaktivasi dicek melalui AnggotaPolicy::deactivate
        if ($anggota->is_active) {
            if (! Gate::allows('deactivate', $anggota)) {
                if ($request->wantsJson()) {
                    abort(422, 'Admin terakhir yang aktif tidak dapat dinonaktifkan.');
                }

                return back()->withErrors([
                    'status' => 'Admin terakhir yang aktif tidak dapat dinonaktifkan demi keselamatan operasional sistem.',
                ]);
            }
        }

        $newStatus = ! $anggota->is_active;
        $anggota->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Akun \"{$anggota->nama}\" berhasil {$statusText}.");
    }
}
