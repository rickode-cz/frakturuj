# Frakturuj

/app

- npm install
- npm run build

## Doporučení pro zabezpečení WP

- Cloudflare Turnstile (captcha pro zabezpečení přihlašovací obrazovky) - https://cs.wordpress.org/plugins/simple-cloudflare-turnstile/
- Na hostingu nainstalovat SSL certifikát (Let's Encrypt zdarma), každý hosting bude mít svůj návod. Potom přidat a nastavit plugin - https://cs.wordpress.org/plugins/really-simple-ssl/
- Případně přidat do souboru .htaccess (přístup přes FTP)

```
# HTTP Security headers
<IfModule mod_headers.c>
	Header always set Strict-Transport-Security: "max-age=31536000" env=HTTPS
	Header always set Content-Security-Policy "upgrade-insecure-requests"
	Header always set X-Content-Type-Options "nosniff"
	Header always set X-XSS-Protection "1; mode=block"
	Header always set Expect-CT "max-age=7776000, enforce"
	Header always set Referrer-Policy: "no-referrer-when-downgrade"
	Header always set X-Frame-Options: "SAMEORIGIN"
	Header always set Permissions-Policy: "" 
</IfModule>
```