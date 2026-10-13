
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    /**
     * Nama tabel database.
     */
    protected $table = 'transaksis';

    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'nomor_kuitansi',
        'nama_pelanggan',
        'tanggal',
        'keterangan',
        'nominal',
        'metode_pembayaran',
        'status',
    ];

    /**
     * Konversi tipe data.
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'integer',
        ];
    }

    /**
     * Hubungan transaksi dengan tim.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Hubungan transaksi dengan pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Memeriksa status lunas.
     */
    public function getIsLunasAttribute(): bool
    {
        return $this->status === 'lunas';
    }
}
