# Dundee MakerSpace Website

This is the repo for the Dundee MakerSpace website. It includes the WordPress theme and custom plugins that make up the site.

The this is based on [Terminally Pixelated](https://github.com/terminalpixel/terminally-pixelated), developed by [Grant Richmond](https://grant.codes)

## Running locally

This repo contains the contents of the `wp-content` folder of our site (excluding 3rd party plugins and media uploads). In order to run it locally for development you must first have a fresh WordPress install running. I recommend using [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV) as it's easy to set up and inculdes the build tool requirements.

To get started clone this repo into your `wp-content` folder. You then require npm, gulp and composer installed and then run `npm install`, `composer install` and `gulp build` in the content directory to install the required dependencies. You can also edit `src/config.json` with your development url to get browser sync working.

Run `gulp watch` in the content directory to watch for file changes and start a browser sync server.

Run `gulp build` to fully compile the theme.

## Diving in

### Font sizing

Font sizing is easiest to achieve using the `tp-fs` mixin.

    @include tp-fs(1);

### Spacing

Spacing is best done with the `tp-space` mixin which creates a value based off of your base font size;

    @include tp-space(padding-bottom, .5);

This will create `padding-bottom` on an element with the value of half of your base line height.

There is also `tp-leader`, `tp-trailer`, `tp-padding-leader` and `tp-padding-trailer` to easily add vertical margins and paddings.

### Enqueuing resources

There is a little wrapper to be able to quickly register or enqueue JavaScript and CSS files from your theme:

	TPHelpers::register( 'js/app.js', array( 'jquery' ) );
	TPHelpers::enqueue( 'css/style.css' );

And an accompanying helper for getting the url of a theme resource:

	TPHelpers::get_theme_resource_uri( '/path/to/file.txt' );

### Svg icons

Svg files that are placed in the `src/svgs/` folder are automatically squashed into a single svg and retrieved via svg symbols.

To use an icon in a twig template you can simply use the `icon` function. For example to use an icon saved at `src/svgs/facebook.svg` you simply add `{{icon('facebook')}}` to your template at the appropriate point.

There is also a function to grab the html for an icon in PHP. The previous example would then become:

    $icon = TPHelpers::icon('facebook');
