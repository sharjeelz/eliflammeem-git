# Security Policy

## Reporting a vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Email reports to **security@eliflammeem.com** with:

- A description of the vulnerability
- Steps to reproduce (proof-of-concept where appropriate)
- Your name / handle for acknowledgment (optional)

We'll respond within 5 business days and work with you on a coordinated disclosure.

## Supported versions

Only the latest `main` branch receives security fixes at this stage. Once we cut tagged releases, the support matrix will be updated here.

## Scope

In scope:
- Remote code execution, SQL injection, XSS, CSRF in Eliflammeem itself
- Tenant isolation bypass (one school reading/writing another's data)
- Access code / authentication bypass
- Privilege escalation (staff → admin, etc.)
- Sensitive data exposure (tenant data, access codes, attachments)

Out of scope:
- Issues in third-party dependencies (please report upstream)
- Social engineering / physical access scenarios
- DoS / volumetric attacks
- Self-XSS or issues requiring an already-compromised admin account
