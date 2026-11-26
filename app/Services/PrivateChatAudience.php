<?php

namespace App\Services;

use Illuminate\Support\Collection;

// The chat audience of a private Task: only the listed usernames, with no way
// in for project admins or the creator. See PmChatAccess::audiences().
class PrivateChatAudience extends Collection
{
}
