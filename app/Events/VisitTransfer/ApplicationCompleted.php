<?php

namespace App\Events\VisitTransfer;

use App\Events\VisitTransfer\ApplicationStatusChanged;
use Illuminate\Queue\SerializesModels;
use App\Models\VisitTransfer\Application;

class ApplicationCompleted extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}