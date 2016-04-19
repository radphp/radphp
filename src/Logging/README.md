# RadPHP Logging Component
[![License](https://img.shields.io/github/license/radphp/logging.svg)](https://github.com/radphp/logging) [![Total Downloads](https://img.shields.io/packagist/dt/radphp/logging.svg)](https://github.com/radphp/logging)


The Logging library provides multiple logging adapters using a simple interface. With the `Logger` class it is
possible to send a single message to multiple logging adapters at the same time.

By default you can use File or Null as logging adapters, but you can use any
object implementing `Rad\Logging\AdapterInterface` as an adapter for the `Logger` class.

## Usage

You can create new instance adapter and attached to the `Logger` class. An example would be:

```php
use Rad\Logging\Logger;
use Rad\Logging\Adapter\FileAdapter;

// Attach single or multiple adapters
$logger = new Logger();

$logger->attachAdapter(new NullAdapter());
$logger->attachAdapter(new FileAdapter('/path/to/file.log'));
// Or attaching your adapter
$logger->attachAdapter(new MyAdapter());

// You can pass message to these log levels
$logger->emergency('Something did not work');
$logger->alert('Something did not work');
$logger->critical('Something did not work');
$logger->error('Something did not work');
$logger->warning('Something did not work');
$logger->notice('Something did not work');
$logger->info('Something did not work');
$logger->debug('Something did not work');
```
The log output is below:

```
09/Apr/2016 11:18:55 UTC [EMERGENCY] Something did not work
09/Apr/2016 11:18:55 UTC [ALERT] Something did not work
09/Apr/2016 11:18:55 UTC [CRITICAL] Something did not work
09/Apr/2016 11:18:55 UTC [ERROR] Something did not work
09/Apr/2016 11:18:55 UTC [WARNING] Something did not work
09/Apr/2016 11:18:55 UTC [NOTICE] Something did not work
09/Apr/2016 11:18:55 UTC [INFO] Something did not work
09/Apr/2016 11:18:55 UTC [DEBUG] Something did not work
```

The log message may contain placeholders. Placeholder names must correspond to keys in the context array.
Placeholder names must be delimited with a single opening brace `{` and a single closing brace `}`. There must not be any whitespace between the delimiters and the placeholder name.
Placeholder names should be composed only of the characters `A-Z`, `a-z`, `0-9`, underscore `_`, and period `.`. The use of other characters is reserved for future modifications of the placeholders specification.

The following is an example uses placeholder in log message:

```PHP
use Rad\Logging\Logger;
use Rad\Logging\Adapter\FileAdapter;

$logger = new Logger();
$logger->attachAdapter(new FileAdapter('/path/to/file.log'));
$logger->info('User "{user_id}" successfully logged in.', ['user_id' => 2]);
```
### Transactions

Transactions store log data temporarily in memory and later on write the data to all adapters.

```php
use Rad\Logging\Logger;
use Rad\Logging\Adapter\FileAdapter;

$logger = new Logger();
$logger->attachAdapter(new FileAdapter('/path/to/file.log'));

$logger->begin();
try {
    //Code here
    $logger->commit();
} catch (\Exception $e) {
    $logger->rollback();
    throw $e;
}
```

### Formatter

You can use formatter for format log lines or implementing your own formatter. Your formatter must be implemented `Rad\Logging\FormatterInterface`, Default formatter is `LineFormatter`

```PHP
use Rad\Logging\Logger;
use Rad\Logging\Adapter\FileAdapter;

$logger = new Logger();

$fileAdapter = new FileAdapter('/path/to/file.log');
$fileAdapter->setFormatter(new LineFormatter("%time% :: {%level%} :: %message%\n", 'G:i:s T'));
$logger->attachAdapter($fileAdapter);

$logger->emergency('Something did not work');
$logger->alert('Something did not work');
```

The log output with new format:

```
11:18:55 UTC :: {EMERGENCY} :: Something did not work
11:18:55 UTC :: {ALERT} :: Something did not work
```

These variables available for `LineFormatter`:

Variables | Description
----------|------------
%time%    | Log time
%level%   | Log level
%message% | Log message

