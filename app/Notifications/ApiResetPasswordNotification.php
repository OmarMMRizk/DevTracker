<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApiResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        // بناء رابط إعادة تعيين كلمة المرور
        $resetUrl = url(config('app.url') . '/api/auth/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));

        return (new MailMessage)
            ->subject('🔐 إعادة تعيين كلمة المرور')
            ->greeting('مرحباً ' . $notifiable->name . '!')
            ->line('تلقيت هذا البريد الإلكتروني لأنك طلبت إعادة تعيين كلمة المرور لحسابك.')
            ->line('') // سطر فارغ
            ->action('إعادة تعيين كلمة المرور', $resetUrl)
            ->line('') // سطر فارغ
            ->line('**أو انسخ الرابط التالي في متصفحك:**')
            ->line($resetUrl)
            ->line('') // سطر فارغ
            ->line('⏰ رابط إعادة تعيين كلمة المرور سينتهي خلال **60 دقيقة**.')
            ->line('') // سطر فارغ
            ->line('⚠️ إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء.')
            ->salutation('مع تحيات فريق ' . config('app.name'));
    }
}
