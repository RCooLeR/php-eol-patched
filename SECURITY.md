# Security

All PHP versions in this repository have reached end of life. Migrate applications to a supported official PHP release as soon as possible.

Only the changes listed in [PATCHES.md](PATCHES.md) are included. The project does not provide comprehensive security maintenance, a patch schedule or production support. Unknown and unpatched vulnerabilities may remain in PHP, system libraries and applications.

The allocation-failure mitigations deliberately terminate affected workers. Session upload progress is disabled in source. Test application behavior, failure handling and resource limits before use.

For a problem with a published patch or this build, provide the exact image/version, a small reproducible example and the expected behavior. Do not put credentials, customer data or an undisclosed vulnerability's exploit details in a public issue. New vulnerabilities in supported PHP should follow the [PHP security reporting process](https://www.php.net/security/).
