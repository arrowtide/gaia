<?php

return [
    'select_placeholder' => 'Please select an option...',
    'forgot_password_link_text' => 'Forgot Password?',
    'login_no_account_link_text' => 'Don\'t have an account?',
    'register_has_account_link_text' => 'Already have an account?',

    'label' => [
        'email' => 'Email',
        'password' => 'Password',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
    ],

    'message' => [
        'success' => [
            'default' => 'You\'ve submitted the form successfully.',
            'password_reset' => 'Your password has been reset successfully.',
            'recover_password' => 'Please check your email and follow the instruction in the email to reset your password. If you can\'t see your email, please check your junk folder.',
        ],
        'error' => [
            'default' => 'There are issues with your form. Please check and try again.',
            'recover_reset_error_url_invalid_text' => 'There are issues with your form. Please check and try again. If the token is invalid, <a href="/account/recover">please try again.</a> If the issue persists, please contact support.',
        ],
    ],
];
