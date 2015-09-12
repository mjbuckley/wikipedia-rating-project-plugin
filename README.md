# The Wikipedia Rating Project Plugin

This is the repository for the Wikipedia Rating Project Plugin.  It is meant to be paired with the [Wikipedia Rating Project Theme](https://github.com/mjbuckley/wikipedia-rating-project-theme).  Installing this plugin along with the theme will give you are working reviews site with almost no setup required.  To learn more about the Wikipedia Rating Project, please see the project [home page](http://mjbuckley.github.io/wikipedia-rating-project).


### Installation Requirements

1. A working WordPress installation.
  * [Guide to installing WordPress](https://codex.wordpress.org/Installing_WordPress)
2. An admin username and password for your WordPress site.
3. Your server should be running PHP version 5.5 or 5.6: The plugin was developed and tested on PHP version 5.5 and 5.6.  It is possible that it will work on versions 5.3 and 5.4, but this is untested.  It will definitely NOT work on versions 5.2 and older.


### Installation

1. Download the [latest release](https://github.com/mjbuckley/wikipedia-rating-project-plugin/releases/latest) of the plugin.  The file will be downloaded in a compressed .zip format.  Do not unzip the file as WordPress will expect the files to be in a compressed format.
2. Login to your WordPress site as a user with admin privileges.
3. On the left hand side of the admin dashboard, click on the "Plugins" link.
4. Click on the  "Add New" button located near the top of the page.
5. Click on the "Upload Plugin" button, also near the top of the page.
6. Click on "Choose File."  Navigate to and then select the .zip plugin file that you just downloaded.
7. Click on the "Install Now" button.
8. Once the plugin installs successfully, click on the "Activate Plugin" link.


### Setup

1. Enter disciplines: You are free to enter as many disciplines as you chose, but the site was designed with approximately 10 to 20 in mind.  You can always add more in the future, but you should probably add at least one to start.
  1. Click on "Reviews" in the left side of the admin dashboard and then click on the "Disciplines" sub heading.
  2. Enter a discipline name.  There are three fields, but only the name is required.  A slug will automatically be generated for you, and the description is not used by the plugin.
2. Add users: The plugin creates a "Reviewer" role.  A Reviewer only has the ability to edit their own reviews, they cannot edit anything else on the site, and they must submit their reviews for review by an admin before publication.  Unless you have a specific reason to use another role, this should be the default role of any new users that you add.
  1. Click on "Users" in the left side of the admin dashboard.
  2. Click on the "Add New" button near the top of the screen.
  3. Enter the required information.  Choose the "Reviewer" role from the dropdown Roles menu.  Then click "Add New User."


## How reviews work

The plugin creates a custom post type called reviews.  A review has several components: Review text, Wikipedia page title, lastrevid, rating, and disciplines.  If a required field is missing or invalid, the plugin will refuse to publish the review.  Instead, the review will be saved as a draft and an explanation of the problem will passed on to the user.

  * **Review:** A review of the Wikipedia article.  This was originally intended to be a short review of around 100 words, but there is no hard limit to a review's length.  (Required)
  * **Page title:** The title of the Wikipedia page being reviewed.  It is important that this title be an exact match of the Wikipedia page being reviewed.  The plugin will attempt to fix capitalization, redirect, and others errors, but this is not always possible. (Required)
  * **Lastrevid:** Every revision of every Wikipedia page is given a unique identifying number called a lastrevid.  The plugin uses this number to link a review to the version of the Wikipedia article being reviewed.  If you know the lastrevid of the article that you are reviewing, you can enter it.  If you don't, leave this section blank and the plugin will grab the lastrevid of the current version of the article.  If you enter a lastrevid it must match the page title that you enter.  If not, a warning will be given and the review saved as a draft. (Optional.  You can leave it blank and one will be grabbed for you)
  * **Rating:** Ratings mirror the ratings that Wikipedia uses internally: A, B, C, Start, and Stub.  These ratings are coded into the plugin and will appear automatically for the users.  (Required)
  * **Disciplines:** You can optionally assign a page as belonging to one or more disciplines.  This is to allow visitors to the site to sort the reviews that they want to see by discipline.  (Optional)

To read more about the suggested structure for reviews and how the rating terms work, see the [review guidelines](http://mjbuckley.github.io/wikipedia-rating-project/review-guidelines) on the Wikipedia Rating Project website.


## Known Issues

Every WordPress post is given a unique post slug.  However, if a post is deleted, that slug is made available for reuse again.  For example, suppose that a review with a post slug of Middle-Ages-3 is deleted.  The next time someone reviews the Middle Ages Wikipedia page, that review will take on the old post slug of Middle-Ages-3.  Two live reviews will never have the same post slug, but it is possible that a review could have the same slug as a deleted review.  This is an issue with how WordPress assigns post slugs, but we are working on a solution for this in a future release.


### Notes

Before considering additional features, the immediate plan for the plugin is to improve code quality and remove bugs.  We welcome suggestions for the future, but for now we want to focus on improving the current code base.

Automatic plugin updates are currently not enabled, so be sure to check back to find future improvements.