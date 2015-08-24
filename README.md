# The Wikipedia Rating Project

The Wikipedia Rating Project (WRP) provides an easy way to rate the quality of Wikipedia pages.

Combining the plugin and theme is all that is needed to have a fully functioning site.

## How the plugin works

The plugin creates a custom post type called reviews.  A review has several components: Review text, Wikipedia page title, lastrevid, rating, and disciplines.


If a page title and lastrevid a given, the plugin will ensure that that information is correct before saving the post.  If only a title is given, the plugin will veriffy that the title exists and then grab the lastrevid of the current edit.


### Review

A short review of the Wikipedia article.  There is now hard limit to a review, but it is meant to be a short overfew, perhaps several hundred words.

### Page title

The title of the Wikipedia page being reviewed.  It is important that this title be an exact match of the Wikipedia page being reviewed.  The WRP plugin will attempt to fix capitalization and redirect errors, but this is not always possible.


### Lastrevid

Every version of every Wikipedia page is given a unique identifying number called a lastrevid.  Since Wikipedia pages are constantly changing, a review needs to reference the particular version of a Wikipedia page that is being reviewed.


### Ratings

Ratings mirror the ratings that Wikipedia uses internally: A, B, C, Start, and Stub.  These ratings are coded into the plugin and will appear automatically for the users.  A rating is required in order for a post to be published.


### Disciplines

Disciplines are not prepopulated.  An admin needs to login and manually add them.  There is no minimum or maximum number of disciplines, but the plugin and theme were designed with around 10-30 in mind.  More than one disipline can be used on a review.  Disciplines are not required for a post to be published.


## Known Issues

Every WordPress post is given a unique post slug.  However, if a page is deleted, that slug is made available for reuse again.  Suppose that the page Albert-Einstein-3 is deleted.  The next review for Albert Einstein will take on the old slug term.  This is potentially problematic if permalinks are set to post_name.

## Pull Reqeusts

At the moment we are focusing on refining the current plugin and theme by fixing bugs, simplifying the code, and improving the user experience.  We welcome any contributions in this vein.  Once we feel these factors have been achieved we will consider adding additional features.

## Future possibilities

Creating some sort of notification system to allow Wikipedia editors to be notified when a page they follow has been rated.


## Current Status

This is a working beta version.  Changes are to be expected, including the possibility of breaking changes in the future.


## PHP and WordPress Versions

The pluin has been tested with php 5.5 and 5.6 and WordPress version 4.2.  It may work with other versions, but it has not been tested.

