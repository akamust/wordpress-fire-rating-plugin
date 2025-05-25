🔥 FIRE Plugin – Overview (as it stands)
Plugin Purpose
FIRE (Field-based Individual Rating Engine) is a custom WordPress plugin that enables you to:

Define your own editorial star rating criteria (like Games, Payments, Support…)

Assign weights to each criterion (e.g. Games = 30%)

Automatically calculate an overall score

Display ratings via shortcodes or templates (coming soon)

Output proper structured data (JSON-LD) for Google (coming soon)

✅ What FIRE Can Do Today (Confirmed Working)
Admin Settings Panel:
Available under Settings > FIRE Ratings

A General tab lets you select which post types should support FIRE (e.g. casinos, bonuses, games)

When post types are selected:

Dynamic tabs appear for each one (e.g. “Casinos Rating Fields”)

You can add any number of rating criteria

Each criterion has: Label, Slug, and Weight

Save Behavior:
The plugin saves all criteria per post type to sypesr_criteria_config

You can remove or reset the configuration via the Reset button

🛠 What You're Trying to Fix Now (Open Issues)
1. ❌ Extra Ghost Fields Appearing
Problem:
When you add 5 fields, 10+ rows appear on save or reload.

Cause:
The current input names are incorrectly set like this:

html
Copy
Edit
name="fire_criteria[casino][][label]"
name="fire_criteria[casino][][slug]"
name="fire_criteria[casino][][weight]"
This creates 3 separate arrays instead of 1 grouped object.

Fix Plan:

Input names must be indexed consistently:

html
Copy
Edit
fire_criteria[casino][0][label]
fire_criteria[casino][0][slug]
fire_criteria[casino][0][weight]
2. ❌ No Slug Auto-Generation
Problem:
Users are expected to enter a slug manually, or leave it empty.

Fix Plan:

When the user types a label, JS should:

Automatically populate the slug field with a “slugified” version (e.g. Game Selection → game-selection)

Still allow manual override

3. 🧼 Empty Fields Being Saved
Problem:
Even if you leave fields blank, they are stored on save.

Fix Plan:

Before saving:

Discard any row where label is empty

Auto-generate slug if missing

Discard if weight is 0 or missing

🔜 Next Steps in Plugin Development
Phase 2: Post Editor Meta Box
Show rating inputs (stars or number dropdowns) on posts of selected types

Pull fields from the saved config

Store values in custom fields

Auto-calculate average based on weights

Show score preview in the editor

Phase 3: Frontend Output
Shortcodes:

[fire_total] – show overall score

[fire_criteria field="games"] – show individual score

[fire_template] – display all rating fields

Option to show stars or numbers

Customize star styles (color, shape, count)

Phase 4: Structured Data
Output proper JSON-LD schema.org/Review markup

Include:

itemReviewed: Post title or custom label

reviewRating: average score

reviewAspect: each rating criterion

✅ Summary
Area	Status
General Settings Tab	✅ Working
Post Type Tabs	✅ Working
Add Fields	✅ UI works
Save/Reload	✅ Working
Input structure bug	❌ Needs fix
Slug generation	❌ Needs JS
Field cleanup on save	❌ Needs validation