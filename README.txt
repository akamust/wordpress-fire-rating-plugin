🔥 FIRE Plugin – Overview

Plugin Purpose
FIRE (Field-based Individual Rating Engine) is a custom WordPress plugin that enables you to:

- Define your own editorial star rating criteria (like Games, Payments, Support…)
- Assign weights to each criterion (e.g. Games = 30%)
- Automatically calculate an overall score
- Display ratings via shortcodes or custom templates
- Output structured data (JSON-LD) for Google
- Customize the layout with a template editor
- Add a rating column to the post list view in the WP admin

✅ What FIRE Can Do Today

🧭 Admin Settings Panel
- Located at: Settings > FIRE Ratings
- General tab: select which post types to enable
- Dynamic tabs for each post type to define criteria:
  - Each field includes: Label, Slug, Weight (must total 100%)
  - Slugs are auto-prefixed with `fire-editorial-stars-`
- Template tab: write your custom output template using placeholders:
  - `{fields}`, `{field:slug}`, `{overall}`, `{stars}`
- Help tab: built-in documentation and usage examples

📝 Editor Experience
- Custom meta box on selected post types
- Input scores per field (0.0–5.0, supports decimals)
- Auto-calculates weighted average
- Overall score is saved to `_fire_editorial_stars_overall`

🔠 Shortcodes
- `[fire_total]` – Show overall score
- `[fire_field slug="fire-editorial-stars-games"]` – Show single field
- `[fire_template]` – Render full layout from template

📦 Schema Output
- JSON-LD injected automatically on single post pages
- Includes itemReviewed, reviewRating, and optional reviewAspect for Google

📊 Admin Post List Column
- Adds a "FIRE Rating" column to post list views
- Sortable by score
- Shows current average

🛠 All Known Issues Fixed
1. ✅ Ghost Fields: Resolved via corrected input structure with indexed naming
2. ✅ Slug Generation: Auto-fills slug on blur, supports override
3. ✅ Field Cleanup: Validation in save function discards invalid or blank rows

📅 Development Completed Through:
✔ Post editor integration
✔ Frontend display via shortcodes and template system
✔ Schema integration
✔ Template layout editor
✔ Admin help documentation

✅ Summary

| Area                    | Status     |
|-------------------------|------------|
| General Settings Tab    | ✅ Working |
| Post Type Tabs          | ✅ Working |
| Add/Edit Fields         | ✅ Working |
| Auto-Prefixed Slugs     | ✅ Working |
| Field Validation        | ✅ Working |
| Slug Auto-Fill          | ✅ Working |
| Save/Reload             | ✅ Working |
| Post Editor UI          | ✅ Working |
| Weighted Total Score    | ✅ Working |
| Shortcodes              | ✅ Working |
| Custom Template Editor  | ✅ Working |
| Schema Markup Output    | ✅ Working |
| Admin Column View       | ✅ Working |
| Built-in Help Tab       | ✅ Working |
