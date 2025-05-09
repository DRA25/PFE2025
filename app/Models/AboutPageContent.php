<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPageContent extends Model
{
protected $fillable = [
'section_title',
'section_content',
'image_path',
'order'
];
}
