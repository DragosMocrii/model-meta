# Model Meta Change Log

## Version 0.2

- Added configuration setting to define a morph map to decouple application internal structure from database
- Fetching a single meta will preload all meta for the model, so that fetching subsequent single metas will use the cache instead executing a DB query 
- Added configuration setting to enable/disable Preload on Single Meta fetch.

## Version 0.1.1

- Minor changes to documentation

## Version 0.1

- Initial version