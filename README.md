# custom-posts-importer

## Custom Posts Importer

Import posts from an external API and display them via a shortcode.

- fetches posts using the API
- checks for existing posts with the same title to avoid duplicates
- creates necessary categories
- sets the first admin as the author
- handles the image upload
- assigns meta values for 'site_link' and 'rating'.

### Custom shortcode that fetches posts from the database and displays them in a custom format.

Example usage:
```
[custom_posts title="Latest Posts" count="5" sort="date"]
```

This shortcode will display the latest 5 posts in the default format.
You can customize the shortcode attributes to change the title, number of posts, and sort order.
