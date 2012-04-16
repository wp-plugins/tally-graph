=== Tally Graph ===
Contributors: cyberhobo
Donate link: http://www.cyberhobo.net/downloads/wordpress-tally-graph-plugin/
Tags: graphs, charts, google, mashup, visualization, custom fields
Requires at least: 2.5.1
Tested up to: 3.3.1
Stable tag: 0.4.1

Add Google charts and graphs to your WordPress site based on tallies of any
numeric custom field over time. Visualize progress toward any goal. 

== Description ==

WordPress already lets you attach custom data to posts. Tally Graph lets you 
plot that data over time using the Google Chart API. Any numeric data will 
work, whether it is related to athletic training, profits, environmental footprint, 
weight loss, or any topic you care about.  

= Features =

*   Tallies data from any numeric value you enter under "Custom Field" in the WordPress post editor.
*   Provides daily, weekly, monthly, or yearly tallies.
*   Tally either data that accumulates, like donations, or just track a number like current weight.
*   You can make basic use of Tally Graph without any knowlege of the Google Chart API, but you can 
also use nearly any [Google Chart API parameters][gapi].
*   Rarely requires ugrading

= Limitations =

*   Easier than using the Google Chart API directly, but still far short of a point-and-click solution
*   A post for each data point is too cumbersome for some graphs
*   Doesn't yet use time intervals shorter than a day

Move on to the Other Notes tab for details.

Some features are available to you because others have [hired me][hire] to add them.

[gapi]: http://code.google.com/apis/chart/
[hire]: http://www.cyberhobo.net/hire-me/

== Installation ==

Installation should be the same as any WordPress hosted plugin from the Plugins / Add New administration interface, or:

1. Click the download button and save the `tally-graph.zip` file.
2. Expand the ZIP file to create the `tally-graph` directory.
3. Upload the `tally-graph` directory and all the files in it to the 
`wp-content/plugins` directory on your server.
4. Activate the plugin in the "Plugins" administration tab of WordPress.

== Frequently Asked Questions ==

= Do I have to know about this Google API thing, or anything else techy? =

You can get by with very little techyness, just WordPress custom fields
and shortcodes. They're really not bad - look over the Usage section under Other Notes. 
It's not a point-and-click interface either, but it is easier than building 
your charts from scratch. 

If you do want to get adventurous, you can have fancier charts in more places.

= Are there any examples? =

Check the [forum][forum], there's at least one [oldie-but-goodie][eg].

[forum]: http://wordpress.org/tags/tally-graph?forum_id=10
[eg]: http://wordpress.org/support/topic/plugin-tally-graph-example-implementation-fishing-stats?replies=1

= Where are the custom fields? =

As of WordPress 3.3 you must check a box in the "Screen Options" area in the upper right of the post editor page
to make the custom field interface appear.

= Can I skip learning to configure Tally Graph and just hire you to put charts on my site? =

Sure, just send email to <cyberhobo@cyberhobo.net>.

== Usage ==

You'll want to do two things to use Tally Graph:

*   *Enter Data* - put the numbers you want to track in a WordPress custom field.
*   *Visualize Data* - tally and plot your numbers over time in a Google Chart.

= Enter Data =

Tally Graph looks in WordPress [custom fields][] for data to pass on to the
Google Chart API. As indicated by those instructions, a custom field consists
of a key and value. You'll make up the key. The value must be some kind of
number. In the next step you'll use the key name to tell Tally Graph which
custom field data to use.

[custom fields]: http://codex.wordpress.org/Using_Custom_Fields

If you want to enter data without publishing a post, just put the custom fields 
on a dummy post and check "Keep this post private". You may still want to edit the
post date.

= Visualize Data =

Say you have a bunch of posts with the custom field key "Marbles Lost". You may
feel like you've been losing your marbles faster recently, but you're not sure,
so you write a new post (or a page) containing this shortcode:

`[tally_graph key="Marbles Lost"]`

The Tally Graph plugin will replace that shortcode with a bar chart of how many
marbles you've lost each month for the past six months. Those are default settings
you can change with some more parameters. 

`[tally_graph key="Marbles Lost" tally_interval="day" interval_count="14" 
to_date="2008-05-01" chs="300x220" chtt="Marbles Lost"]`

That shortcode results in a 300 pixel wide, 220 pixel high graph of your marbles 
lost in the 14 days prior to May 1st, 2008.

Shortcodes are great in post and page content, but if you want a graph somewhere
else on your site, like in a sidebar, you'll need a [template tag][]. Aside from the 
different format, it works the same:

[template tag]: http://codex.wordpress.org/Template_Tags 

`<?php echo tally_graph('key=Marbles Lost&tally_interval=week&interval_count=4'); ?>`

That makes a nice sidebar graph of marbles lost over 4 weeks, up to the date of 
the last post displayed.

== Screenshots ==

1. Some examples of custom fields attached to a post. They'll be tallied by the
   post date.
2. Some different kinds tally graphs in a page.
3. Some monthly tally graphs in a sidebar.

== Changelog ==

= 0.4.0 =
* New feature: the label_interval parameter

== Tag Reference ==

Read the Usage section first. 

You type a tag directly into a post or page using WordPress [shortcode format][]. 
To put a tag in a theme template, use [template tag with querystring parameters][1].
Both formats take the same parameters listed below.

[shortcode format]: http://codex.wordpress.org/Shortcode_API
[1]: http://codex.wordpress.org/Template_Tags/How_to_Pass_Tag_Parameters#Tags_with_query-string-style_parameters

= tally_graph =

This tag is replaced with an image created with the Google Chart API.

Shortcode: `[tally_graph key="My Key"]`

Template Tag: `<?php echo tally_graph('key=My Key'); ?>`

Parameters:

*   **key** - Required. 

    The key name of the custom field to use for the graph.  Multiple keys can
    be included, separated by a comma.

*   *tally_interval* 

    valid values: `day`, `week`, `month`, or `year`.  default is `month`.  

    this is the interval of time over which the custom field values are 
    tallied.

*   *label_interval* 

    valid values: `day`, `week`, `month`, or `year`.  default is tally_interval.  

    this is the interval of time that is labeled at the bottom of the graph

*   *interval_count* 

    Default is `6`. 

    This is the number of intervals to include in the graph.

*   *to_date* 

    Valid values include several date formats, like `2007-10-31`, 
    `October 31, 2007`, `today`, or `yesterday`. Default is the date of the 
    most recent post displayed. 

    The graph is constructed backward in time from this date. 

*   *method* 

    Valid values are `cumulative`, `track`, or `delta`. Default is `cumulative`.

    The `cumulative` method totals custom field values for each interval. 

    The `track` method doesn't total field values, but fills in time gaps 
    between values. This is good for tracking numbers like current weight that you 
    just want to track without tallying. 

    The `delta` method computes a running total, adding changes to the total
    for each interval. This can be used for data where only losses and gains are
    entered.  One entry is necessary to establish a starting value, then gains and
    losses are recorded as positive and negative values. 

*   *no_cache* 

    Valid values: `true` or `false`. Default is `false`. 

    Turn off URL caching. Rarely used - only when the same graph appears more than
    once on a page.

*   *chs* 

    Default is `200x200`. 

    This is a [Google Chart API][gapi] parameter, the chart size in pixels. 

*   *cht* 

    Default is `bvs`. 

    This is a [Google Chart API][gapi] parameter, the chart type. `bvs` 
    is a vertical bar chart.

*   Any other [Google Chart API parameters][gapi] are passed along, so you 
    can go nuts with all the options. You'll probably use at least `chtt`, 
    the chart title.

[gapi]: http://code.google.com/apis/chart/

= tally_graph_url =

If you want to create your own image tag in a template, this tag will give 
you only the URL for the chart.

Template Tag: `<?php echo tally_graph_url(); ?>`

Parameters are the same as the `tally_graph` tag.
