<?php

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $nama_pelanggan = '';
    public string $tanggal = '';
    public string $keterangan = '';
    public string $nominal = '';
    public string $metode_pembayaran = 'tunai';
    public string $status = 'belum_lunas';

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    public function simpan(): void
    {
        // Ambil pengguna yang sedang login
        $user = auth()->user();

        abort_unless($user, 403);

        // Gunakan tim aktif milik pengguna
        $teamId = $user->current_team_id;

        abort_unless($teamId, 403);

        // Pastikan pengguna benar-benar anggota tim
        abort_unless(
            $user->teams()
                ->whereKey($teamId)
                ->exists(),
            403
        );

        // Validasi seluruh input
        $data = $this->validate([
            'nama_pelanggan' => [
                'required',
                'string',
                'max:255'
            ],
            'tanggal' => [
                'required',
                'date'
            ],
            'keterangan' => [
                'required',
                'string',
                'max:1000'
            ],
            'nominal' => [
                'required',
                'integer',
                'min:1'
            ],
            'metode_pembayaran' => [
                'required',
                Rule::in(['tunai', 'transfer'])
            ],
            'status' => [
                'required',
                Rule::in(['belum_lunas', 'lunas'])
            ],
        ]);

        // Simpan transaksi ke database
        $transaksi = DB::transaction(function () use (
            $data,
            $user,
            $teamId
        ) {
            $transaksi = Transaksi::create([
                'team_id' => $teamId,
                'user_id' => $user->id,
                'nama_pelanggan' =>
                    $data['nama_pelanggan'],
                'tanggal' => $data['tanggal'],
                'keterangan' => $data['keterangan'],
                'nominal' => $data['nominal'],
                'metode_pembayaran' =>
                    $data['metode_pembayaran'],
                'status' => $data['status'],
            ]);

            // Nomor kuitansi hanya untuk pembayaran lunas
            if ($transaksi->status === 'lunas') {
                $nomor = 'KWT-'
                    . now()->format('Y')
                    . '-'
                    . str_pad(
                        (string) $transaksi->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );

                $transaksi->update([
                    'nomor_kuitansi' => $nomor
                ]);
            }

            return $transaksi;
        });

        session()->flash(
            'success',
            'Transaksi berhasil disimpan!'
        );

        // Kosongkan formulir
        $this->reset([
            'nama_pelanggan',
            'keterangan',
            'nominal'
        ]);

        $this->tanggal = now()->toDateString();
        $this->metode_pembayaran = 'tunai';
        $this->status = 'belum_lunas';
    }
};

?>

<div class="mx-auto max-w-3xl p-6">

    <h1 class="mb-2 text-2xl font-bold">
        Tambah Transaksi
    </h1>

    <p class="mb-6 text-gray-500">
        Silakan isi data pembayaran untuk membuat kuitansi.
    </p>

    @if(session()->has('success'))
        <div class="mb-5 rounded-lg bg-green-100 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="simpan" class="space-y-5">

        <!-- Nama pelanggan -->
        <div>
            <label class="block font-medium">
                Nama Pelanggan
            </label>

            <input
                type="text"
                wire:model="nama_pelanggan"
                class="w-full rounded-lg border p-3"
                placeholder="Masukkan nama pelanggan"
            >

            @error('nama_pelanggan')
                <p class="text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Tanggal -->
        <div>
            <label class="block font-medium">
                Tanggal Pembayaran
            </label>

            <input
                type="date"
                wire:model="tanggal"
                class="w-full rounded-lg border p-3"
            >

            @error('tanggal')
                <p class="text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Keterangan -->
        <div>
            <label class="block font-medium">
                Keterangan Pembayaran
            </label>

            <textarea
                wire:model="keterangan"
                class="w-full rounded-lg border p-3"
                placeholder="Masukkan keperluan pembayaran"
            ></textarea>

            @error('keterangan')
                <p class="text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Nominal -->
        <div>
            <label class="block font-medium">
                Nominal Pembayaran (Rp)
            </label>

            <input
                type="number"
                min="1"
                step="1"
                wire:model="nominal"
                class="w-full rounded-lg border p-3"
                placeholder="Contoh: 1500000"
            >

            @error('nominal')
                <p class="text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Metode pembayaran -->
        <div>
            <label class="block font-medium">
                Metode Pembayaran
            </label>

            <select
                wire:model="metode_pembayaran"
                class="w-full rounded-lg border p-3"
            >
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer Bank</option>
            </select>

            @error('metode_pembayaran')
                <p class="text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Status pembayaran -->
        <div>
            <label class="block font-medium">
                Status Pembayaran
            </label>

            <select
                wire:model="status"
                class="w-full rounded-lg border p-3"
            >
                <option value="belum_lunas">
                    Belum Lunas
                </option>
                <option value="lunas">
                    Lunas
                </option>
            </select>

            @error('status')
                <p class="text-sm text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Tombol simpan -->
        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="simpan"
            class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="simpan">
                Simpan Transaksi
            </span>

            <span wire:loading wire:target="simpan">
                Menyimpan...
            </span>
        </button>

    </form>
</div>
