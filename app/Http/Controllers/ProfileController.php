<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTeleponRequest;
use App\Services\AesGcm;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilan profil pengguna dan status nomor telepon terenkripsi (Canvas Node 08).
     */
    public function show(AesGcm $aes): View
    {
        $user = auth()->user();
        $teleponPlaintext = null;

        if ($user->telepon_enc !== null) {
            $aad = "anggota:{$user->id}:telepon:v1";
            $teleponPlaintext = $aes->decrypt($user->telepon_enc, $aad);
        }

        return view('profil.show', compact('user', 'teleponPlaintext'));
    }

    /**
     * Perbarui nomor telepon pengguna dengan enkripsi AES-256-GCM.
     */
    public function updateTelepon(UpdateTeleponRequest $request, AesGcm $aes): RedirectResponse
    {
        // SEC-10: User pemilik selalu diambil dari sesi aktif
        $user = auth()->user();
        $inputTelepon = $request->validated('telepon');

        if (empty($inputTelepon)) {
            $user->update(['telepon_enc' => null]);

            return redirect()
                ->route('profil.show')
                ->with('success', 'Nomor telepon berhasil dihapus.');
        }

        // SEC-03: Enkripsi dengan AES-256-GCM dan AAD anggota:{id}:telepon:v1
        $aad = "anggota:{$user->id}:telepon:v1";
        $envelope = $aes->encrypt($inputTelepon, $aad);

        $user->update([
            'telepon_enc' => $envelope,
        ]);

        return redirect()
            ->route('profil.show')
            ->with('success', 'Nomor telepon berhasil disimpan dengan enkripsi aman AES-256-GCM.');
    }
}
