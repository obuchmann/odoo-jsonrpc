# Changes

## Connection Pool Exhaustion Fix

### Issue
The application was experiencing connection pool exhaustion errors with PostgreSQL when running in CLI environments (like Laravel Horizon queue workers):

```
Odoo Server Error: FATAL: remaining connection slots are reserved for non-replication superuser connections
```

This occurred because database connections weren't being properly closed after use, particularly in long-running CLI processes.

### Solution
Modified the `getClient` method in `Endpoint.php` to detect CLI environments and always create fresh client instances in those contexts:

```php
public function getClient(bool $fresh = false): Client
{
    // Always create a new client when running in CLI mode
    if ($fresh || null == $this->client || php_sapi_name() === 'cli') {
        // In CLI mode, don't cache the client to prevent connection pool exhaustion
        if (php_sapi_name() === 'cli') {
            return new Client($this->getConfig()->getHost(), $this->service, $this->getConfig()->getSslVerify());
        }
        
        // In non-CLI mode, cache the client as before
        $this->client = new Client($this->getConfig()->getHost(), $this->service, $this->getConfig()->getSslVerify());
    }
    return $this->client;
}
```

### Benefits
1. **Prevents Connection Leaks**: By creating a new client instance for each operation in CLI mode, we ensure that connections are properly closed after use.
2. **Maintains Performance for Web Requests**: The existing caching behavior is preserved for non-CLI environments, maintaining performance for web requests.
3. **No Configuration Required**: The solution automatically detects the environment and adjusts behavior accordingly.

### Impact
This change should eliminate the need to restart the Odoo database or queue workers due to connection pool exhaustion errors.