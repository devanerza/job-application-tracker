 <x-mail::message>
# Assalamu alaikum, {{ $user->name }}

Thank you for joining our platform. We are excited to help you build better software. Your account is now active and ready for use.

<x-mail::button :url="$url">
Go to Dashboard
</x-mail::button>

### What's next?
* Complete your profile.
* Explore our documentation.
* Reach out to support if you have any questions.

Thanks,<br>
The {{ config('app.name') }} Team

<x-mail::panel>
**Note:** If you did not sign up for this account, please ignore this email or contact our support team.
</x-mail::panel>

</x-mail::message>