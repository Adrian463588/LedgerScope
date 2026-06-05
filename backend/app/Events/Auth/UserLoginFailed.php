<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\Events\BaseAuditableEvent;

final class UserLoginFailed extends BaseAuditableEvent {}
