<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $appends = ['time', 'photo_url'];

    public $table = 'messages';
    protected $fillable = ['id', 'user_id', 'text','chat_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getPhotoUrlAttribute()
{
    return $this->photo ? asset('storage/' . $this->photo) : null;
}

    
}
