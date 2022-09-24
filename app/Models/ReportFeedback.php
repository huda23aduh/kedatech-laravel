<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportFeedback extends Model
{
    protected $table = 'report_feedbacks';

    protected $fillable = [
        'feedback_id',
        'reported_by',
    ];
}
