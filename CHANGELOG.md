# Guozhan Release History

## guozhan-2026.07.13.4 - 2026-07-13

- Restore the versioned Guozhan login stylesheet that was missing from the live server.
- Redesign desktop login around the same ink-green gallery visual, cream surface, and seal-red actions as the public website.
- Add a compact mobile layout that keeps the full login form in the first screen without horizontal overflow.
- Force a stable light form surface to prevent browser dark-mode distortion.
- Replace the retired competition wording with current works, categories, homepage carousel, and contact management copy.
- Add a responsive login-design contract test to guard the stylesheet and breakpoints.

## guozhan-2026.07.13.3 - 2026-07-13

- Restrict all password logins and administrator sessions to the configured administrator whitelist.
- Cap remembered administrator authentication at an absolute seven days without rolling renewal.
- Force pre-release, expired, mismatched, or permission-revoked sessions to authenticate again.
- Show the verified administrator identity and expiry time in the custom dashboard.
- Add explicit desktop and mobile logout controls and clearer administrator-only login messaging.
- Add a versioned authentication contract test for the complete session behavior.

## guozhan-2026.07.13.2 - 2026-07-13

- Make each displayed customer WeChat ID directly clickable to copy.
- Support keyboard activation with Enter or Space.
- Add a legacy/mobile clipboard fallback and copied-value toast message.
- Refresh public JavaScript and CSS cache versions on all six pages.

## guozhan-2026.07.13.1 - 2026-07-13

- Baseline of the validated static frontend and Guozhan admin plugin 0.6.1.
- Includes the complete public assets and the 20-50 image sequential upload flow.
