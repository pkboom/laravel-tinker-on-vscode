<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Pkboom\ModelImage\Images;

class Ad extends Model
{
    use Images;

    protected $table = 'ads';

    protected $guarded = [];
}
