# Logo and background slider fix

## Root cause handled

The static image files were valid, but cached JavaScript/CSS or older CMS image paths could override the working fallback paths after page load.

## Fix

- The first background is now visible without JavaScript.
- JavaScript controls a 6-second infinite cross-fade slider.
- CMS logo/banner URLs are applied only after the files load successfully.
- Missing CMS media no longer replaces working static files.
- Shared CSS and JavaScript references include a cache-busting version.
- Logo loading falls back to `assets/img/logo.jpg`.
