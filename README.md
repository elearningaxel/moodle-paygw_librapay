# LibraPay Payment Gateway for Moodle

A Moodle payment gateway plugin for [LibraPay](https://www.librapay.ro/) (Libra Internet Bank), enabling card payments in RON (Romanian Leu) for course enrolments and other Moodle payment areas.

## Features

- Accept card payments via LibraPay (Libra Internet Bank)
- Support for RON currency
- Test mode for development/testing
- Automatic payment notifications (email + Moodle notifications)
- GDPR compliant with full privacy provider implementation
- Support for both synchronous (BACKREF) and asynchronous (IPN) callbacks
- Secure P_SIGN signature validation using HMAC-SHA1
- Multi-language support (English, Romanian)

## Requirements

- Moodle 4.0 or higher
- PHP 7.4 or higher
- LibraPay merchant account

## Installation

1. Download the plugin
2. Extract to `/payment/gateway/librapay/`
3. Visit Site Administration > Notifications to complete installation
4. Configure the plugin (see Configuration below)

### Using Git

```bash
cd /path/to/moodle
git clone https://github.com/axelelearning/moodle-paygw_librapay.git payment/gateway/librapay
```

## Configuration

### 1. Enable the Payment Gateway

1. Go to **Site Administration > Plugins > Payment gateways > Manage payment gateways**
2. Enable **LibraPay**

### 2. Create a Payment Account

1. Go to **Site Administration > Payments > Payment accounts**
2. Create a new payment account
3. Configure the LibraPay gateway with your credentials:

| Setting | Description |
|---------|-------------|
| Test mode | Enable for testing with LibraPay sandbox |
| Terminal ID | 8-digit terminal identifier from LibraPay |
| Merchant ID | 15-digit merchant identifier (format: 0000000 + Terminal) |
| Merchant name | Your business name as registered with LibraPay |
| Merchant URL | Your website URL as registered with LibraPay |
| Email | Email for payment notifications |
| Encryption key | 32-character hex key for P_SIGN computation |

### 3. Add Payment to Course Enrolment

1. Go to your course > **Participants > Enrolment methods**
2. Add **Enrolment on payment**
3. Select your payment account
4. Set the enrolment fee and currency (RON)

## Testing

Use LibraPay's test environment:

- **Test URL**: `https://merchant.librapay.ro/pay_auth.php`
- **Test Card**: `4111111111111111`
- **Expiry**: Any future date
- **CVV**: `123`
- Select "Tranzactie Aprobata" for approved transaction

## LibraPay API Documentation

This plugin implements the LibraPay payment gateway according to their official documentation:
- Authorization request via POST form submission
- P_SIGN signature using HMAC-SHA1
- BACKREF callback for synchronous response
- IPN endpoint for asynchronous notifications

## Privacy

This plugin stores the following user data:
- User ID
- Payment amount
- Order ID
- Transaction timestamp
- Payment status

All data handling is GDPR compliant. See `classes/privacy/provider.php` for details.

## Support

- **Issues**: [GitHub Issues](https://github.com/axelelearning/moodle-paygw_librapay/issues)
- **Documentation**: [Moodle Docs](https://docs.moodle.org/en/Payment_gateways)

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.

## Author

**Axel eLearning**
Copyright © 2026
