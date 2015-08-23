JonTag Sample Plugin for Wordpress

This is a simple sample WordPress plug-in to demonstrate the capability of adding a featured tag to each post and include that tag in the title of the post.


Core Actions:

	- add_action('add_meta_boxes', [ &$this, 'meta_box_init'] );  // ties into the add_meta_boxes action to create a new meta box for this plugin
	- add_action('save_post', [ &$this, 'save_featured_tag_box'] ); // ties the saving functionality  for the meta box to the post save
	- add_filter('the_title', [ &$this, 'tag_the_title'], 1, 2 ); // adds the formatter for adding the featured tag to the title output when in single post mode
