<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierReview extends Model
{
    use HasFactory;

    /** Étiquettes proposées au client au moment de la notation. */
    public const TAGS = ['Ponctuel', 'Bien emballé', 'Poli', 'A prévenu avant d\'arriver', 'Discret'];

    protected $fillable = ['order_id', 'courier_id', 'client_id', 'rating', 'comment', 'tags'];

    protected $casts = [
        'rating' => 'integer',
        'tags'   => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id')->withTrashed();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withTrashed();
    }

    /** « ★★★★☆ » */
    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
