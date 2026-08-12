# Logo and Full-Width Hero Final Fix

- Rebuilt `assets/img/logo.jpg` on a square white canvas with safe padding.
- Enlarged the logo without clipping its top or bottom.
- Removed duplicate hero override CSS and updated the original selectors.
- Added a two-layer background treatment:
  - a full-width `cover` backdrop fills the entire banner;
  - a centered `contain` layer preserves the complete source image.
- Kept the existing three-layer JavaScript fade slider and six-second interval.
- Updated asset version strings to prevent stale browser/CDN cache.
- Kept the project structure, PHP API, JSON storage schema, and admin functionality unchanged.
