<?php

namespace App\Models\MongoTemplate;

use Jenssegers\Mongodb\Eloquent\Model;

class UserTemplate extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'userTemplates';

    protected $fillable=['userId','templateId','type','content'];
}
