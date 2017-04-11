<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Models\Mship\Account;
use App\Modules\Visittransfer\Notifications\ApplicationReview;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ApplicationUnderReview;

class NotifyCommunityOfUnderReviewApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationUnderReview $event)
    {
        // TODO: Use the staff services feature to choose recipient
        $account = Account::find(1002707);
        $account->notify(new ApplicationReview($event->application));
    }
}
