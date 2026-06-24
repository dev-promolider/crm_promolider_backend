<?php

namespace App\Models\MongoTemplate;


use Jenssegers\Mongodb\Eloquent\Model;

class Template extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'templates';

    protected $fillable=['name','type','price','content'];
}
