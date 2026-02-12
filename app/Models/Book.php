<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title', 'author', 'isbn', 'publisher', 'publish_year',
        'book_category_id', 'total_copies', 'available_copies',
        'shelf_location', 'description', 'is_active',
    ];

    protected $casts = [
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }

    public function activeIssues()
    {
        return $this->issues()->where('status', 'issued');
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0)->where('is_active', true);
    }
}
