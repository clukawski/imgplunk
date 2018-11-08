<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImageTag extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'image_tags';

    /**
     * The model's settable properties.
     *
     * @var []string
     */
    protected $fillable = [
        'tag_id',
        'image_id'
    ];
}
