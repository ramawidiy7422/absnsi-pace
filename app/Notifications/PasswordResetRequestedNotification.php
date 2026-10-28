<?php
namespace App\Notifications;

use App\Models\PasswordResetRequest;
use Illuminate\Notifications\Notification;

class PasswordResetRequestedNotification extends Notification
{
    public function __construct(public PasswordResetRequest $request) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Permintaan Reset Password',
            'body'  => "{$this->request->user->name} mengajukan reset password.",
            'icon'  => 'fas fa-key',
            'bg'    => '#fff5f5',
            'color' => '#c53030',
            'url'   => '/admin/reset-password-requests',
        ];
    }
}
