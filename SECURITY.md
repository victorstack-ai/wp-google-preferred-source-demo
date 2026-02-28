# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

**Do not report security vulnerabilities through public GitHub issues.**

### How to Report

1. Email victorstackai@gmail.com with the subject "Security Vulnerability Report".
2. Include a description of the vulnerability and its potential impact.
3. Provide detailed steps to reproduce the issue.
4. Include any proof-of-concept code if applicable.

### What to Expect

- Acknowledgement within 48 hours.
- Assessment within 7 business days.
- Fix released within 30 days of confirmation, depending on complexity.
- Coordinated disclosure timing.

## Security Best Practices

- All user inputs are sanitized using WordPress sanitization functions.
- All output is escaped using WordPress escaping functions.
- Nonces are used for form submissions and AJAX requests.
- User capabilities are checked before privileged actions.
