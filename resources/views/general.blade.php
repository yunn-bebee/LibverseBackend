<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        /* Button hover effects */
        .action-button:hover {
            background-color: #02d4b1 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -1px rgba(1, 237, 196, 0.3) !important;
        }

        .secondary-button:hover {
            background-color: #e100b5 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -1px rgba(255, 0, 200, 0.3) !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: 'Inter', Arial, Helvetica, sans-serif; background-color: #ffffff; color: #333333; line-height: 1.6;">
    <!-- Main container -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #ffffff; padding: 40px 0;">
        <tr>
            <td align="center">
                <!-- Content container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); overflow: hidden; border: 1px solid #eaeaea;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #23085A; padding: 32px 32px 24px; text-align: center; border-bottom: 2px solid #01EDC4;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <h1 style="margin: 0; color: #01EDC4; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">
                                            Libiverse
                                        </h1>
                                        <p style="margin: 12px 0 0; color: #FF00C8; font-size: 16px; font-weight: 500; letter-spacing: 1px;">
                                            CONNECT • READ • DISCOVER
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <!-- Title -->
                                        <h2 style="margin: 0 0 20px; font-size: 24px; font-weight: 600; color: #121212; letter-spacing: -0.25px;">
                                            {{ $title }}
                                        </h2>

                                        <!-- Content -->
                                        <p style="margin: 0 0 28px; font-size: 16px; color: #333333; line-height: 1.7;">
                                            {{ $content }}
                                        </p>

                                        <!-- Action Button -->
                                        @if(isset($actionUrl) && isset($actionText) && is_string($actionUrl) && is_string($actionText))
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 32px;">
                                            <tr>
                                                <td align="center">
                                                    <a href="{{ $actionUrl }}" class="action-button" style="display: inline-block; padding: 14px 32px; background-color: #01EDC4; color: #121212; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 700; text-align: center; box-shadow: 0 4px 12px rgba(1, 237, 196, 0.3); transition: all 0.3s ease;">
                                                        {{ $actionText }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif

                                        <!-- Secondary Action (if needed) -->
                                        @if(isset($secondaryUrl) && isset($secondaryText) && is_string($secondaryUrl) && is_string($secondaryText))
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 32px;">
                                            <tr>
                                                <td align="center">
                                                    <a href="{{ $secondaryUrl }}" class="secondary-button" style="display: inline-block; padding: 12px 28px; background-color: #FF00C8; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 600; text-align: center; box-shadow: 0 4px 12px rgba(255, 0, 200, 0.2); transition: all 0.3s ease;">
                                                        {{ $secondaryText }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif

                                        <!-- Divider -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 28px;">
                                            <tr>
                                                <td style="border-bottom: 1px solid #eaeaea;"></td>
                                            </tr>
                                        </table>

                                        <!-- Additional info -->
                                        <p style="margin: 0 0 16px; font-size: 14px; color: #666666;">
                                            If you have any questions or need assistance, our support team is here to help.
                                        </p>

                                        <p style="margin: 0; font-size: 14px; color: #666666;">
                                            Happy reading!<br>
                                            <strong style="color: #FEE400;">The Libiverse Team</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #23085A; padding: 28px 32px; text-align: center; border-top: 1px solid #eaeaea;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 0 0 20px;">
                                        <!-- Footer links -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                            <tr>
                                                <td style="padding: 0 12px;">
                                                    <a href="#" style="display: inline-block; color: #01EDC4; text-decoration: none; font-size: 14px; font-weight: 500;">Help Center</a>
                                                </td>
                                                <td style="padding: 0 12px;">
                                                    <a href="#" style="display: inline-block; color: #01EDC4; text-decoration: none; font-size: 14px; font-weight: 500;">Privacy</a>
                                                </td>
                                                <td style="padding: 0 12px;">
                                                    <a href="#" style="display: inline-block; color: #01EDC4; text-decoration: none; font-size: 14px; font-weight: 500;">Unsubscribe</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 12px; font-size: 14px; color: #FF00C8;">
                                            &copy; {{ date('Y') }} Libiverse. All rights reserved.
                                        </p>
                                        <p style="margin: 0; font-size: 13px; color: #888888;">
                                            British Council Library Social Platform
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Company info -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 20px auto 0;">
                    <tr>
                        <td style="text-align: center; padding: 16px;">
                            <p style="margin: 0; font-size: 12px; color: #888888;">
                                Libiverse • British Council Library Network<br>
                                123 Knowledge Street, London, UK • info@libiverse.com
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
