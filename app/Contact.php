<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Contact extends Model
{
    use Searchable;
    protected $guarded = [];
    protected $dates = ['birthday'];
    public function path()
    {
        return '/contacts/'.$this->id;
    }

    public function scopeBirthdays($query)
    {
        return $query->whereRaw('birthday like "%-' . now()->format('m') . '-%"');
    }
    public function setBirthdayAttribute($value)
    {
        // $data = Carbon::parse($value)->format('Y-m-d');
        // $date = date($value);
        // $birthdayy = date('Y-m-d', strtotime($value));
        // dd($birthday);
        $birthday = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        $this->attributes['birthday'] = $birthday;
    }

    public function getBirthdayAttribute($value)
    {
        $birthday = Carbon::create($value)->format('d/m/Y');
        // dd($date3);
        // $birthday = date('d/m/Y', strtotime($value));

        // $birthday = Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y');
        return $birthday;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
