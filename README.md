# Proxyflare
![Proxyflare Logo](https://github.com/whitelabelltd/proxyflare/raw/master/assets/images/logo-wp-options.png)\
\
Proxy for handling Cloudflare functions for Whitelabel Digital Clients.\
Also restores visitor IP's when using Cloudflare.

### Why

Avoids having to put your Global Cloudflare API Key on end-user sites. Requires the domain added to the main Whitelabel Digital dashboard before it can be used.

### Functions

**Current**
- Cache Clear

**Roadmap**
- Development Mode Toggle

## Install
1. Intsall Plugin
2. Under Settings > Proxyflare
3. Enter the API Credentials and Save ( credentials can be obtained from Whitelabel Digital )
4. Done

## How it works
#### Manually 
In the admin bar the user can clear the cache.\
Or in the settings page

#### Automatic
When WP-Rocket is installed it will clear the Cloudflare cache each time WP-Rocket clears the site cache.

## Support
Any issues, please open a pull request or contact Whitelabel Digital
