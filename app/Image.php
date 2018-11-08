<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'images';

    /**
     * The model's settable properties.
     *
     * @var []string
     */
    protected $fillable = [
        'hash',
        'ext'
    ];
}
