# Pilulka cache packages
Pilulka cache packages are very simple caching services which are strongly inspired by Laravel cache contracts.

* **RedisCache** - use with bigger projects (if you need to cache a lot of data)
* **IncludeCache** - very fast with php accelerators (e.g. opcache)
* **FileCache** - use php serialization 
* **ArrayCache** - useful for per-request caching (e.g. for simple database layers)
