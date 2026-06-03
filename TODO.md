# TODO

Solo task list for the BigCats project. Newest items at the top of each section.

## Now
<!-- Things actively being worked on -->

## Next
<!-- Up next, in rough priority order -->

- Audit **all** species articles (existing and new) and add informational images where useful — e.g. species population/range maps
- Filter the news widget on root species article pages to show only news about that species
- Add a gallery widget below the news widget on root species article pages
  - Fills the remaining right-column space
  - Shows pictures of that species
- Add a "species" widget at the top of the right column on news article pages
  - One item per species mentioned in the news article (can be multiple)
  - Each item: picture + link to that species' root article

## Someday
<!-- Ideas, nice-to-haves, no commitment -->

- Library section: catalogue of books about big cats
- Movies section: catalogue of films, TV shows, and documentaries featuring big cats

## Done
<!-- Completed items, most recent first. Trim periodically. -->

- Published 6 remaining big cat species articles with CC photos, captions, and summaries: snow leopard, puma, ocelot, serval, caracal, clouded leopard
  - Sourced CC-licensed Flickr photos (landscape, full-body, adult, natural settings, no man-made objects) — mostly Tambako The Jaguar; puma uses wild trail-cam shots from Santa Monica Mountains NRA
  - Photos uploaded to API project prod (`~/api/storage/app/public/news/`), `media`/`filename` set on `news` rows (direct `_k` 2048px Flickr URLs)
  - Per-article: species-led `image_caption` with CC attribution + `resume` summary matching the existing 5 articles' style
  - **Note:** melanism/leucism angle not achievable — no CC-licensed melanistic/leucistic photos exist on Flickr for these species (all "All Rights Reserved")
- **Bug:** YouTube videos not displaying on news article pages — fixed embed rendering (override Filament's HTML sanitizer to allow iframes from YouTube/Vimeo/Dailymotion; CSS to keep wrapper full-width inside Filament's `inline-flex` text-entry container)
