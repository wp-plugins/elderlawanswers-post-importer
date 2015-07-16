jQuery(document).ready(function () {

	jQuery('#save_and_import').on('click', function () {
		jQuery('#save_to_db').val('true');
	});

	jQuery('a.load-log').on('click', function () {
		jQuery('#main_ui').hide();
		jQuery('.ajax_content').html('<img src="/wp-admin/images/wpspin_light.gif" alt="" class="loader" />');
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_load_log'
			}),
			success: function (data) {
				jQuery('.ajax_content').html(data);
			}
		});
		return false;
	});

	jQuery('#ela-rss-pi-button-accept').on('click', function () {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_accept_terms',
        accepted_terms: true
			}),
			success: function (data) {
        tb_remove();
			}
		});
		return false;
	});

	jQuery('#ela-rss-pi-button-decline').on('click', function () {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_accept_terms',
        accepted_terms: false
			}),
			success: function (data) {
        tb_remove();
			}
		});
		return false;
	});


	jQuery('body').delegate('a.show-main-ui', 'click', function () {
		jQuery('#main_ui').show();
		jQuery('.ajax_content').html('');
		return false;
	});

	jQuery('body').delegate('a.clear-log', 'click', function () {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_clear_log'
			}),
			success: function (data) {
				jQuery('.log').html(data);
			}
		});
		return false;
	});

	if ( jQuery("#rss_pi_progressbar").length && feeds !== undefined && feeds.count ) {
		var import_feed = function(id) {
			jQuery.ajax({
				type: 'POST',
				url: rss_pi.ajaxurl,
				data: {
					action: 'rss_pi_import',
					feed: id
				},
				success: function (data) {
					var data = data.data || {};
					jQuery("#rss_pi_progressbar").progressbar({
						value: feeds.processed()
					});
					jQuery("#rss_pi_progressbar_label .processed").text(feeds.processed());
					if ( data.count !== undefined ) feeds.imported(data.count);
					if (feeds.left()) {
						jQuery("#rss_pi_progressbar_label .count").text(feeds.imported());
						import_feed(feeds.get());
					} else {
						jQuery("#rss_pi_progressbar_label").html("Import completed. Imported posts: " + feeds.imported());
					}
				}
			});
		}
		jQuery("#rss_pi_progressbar").progressbar({
			value: 0,
			max: feeds.total()
		});
		jQuery("#rss_pi_progressbar_label").html("Import in progres. Processed feeds: <span class='processed'>0</span> of <span class='max'>"+feeds.total()+"</span>. Imported posts so far: <span class='count'>0</span>");
		import_feed(feeds.get());
	}

});

jQuery(window).load(function() {

  if(jQuery("input#ela-rss-user-has-not-accepted").length) {
    tb_show("User Agreement", "#TB_inline?width=600&height=200&inlineId=user-agreement-modal&title=UserAgreement");
  }
});

function update_ids() {

	ids = jQuery('input[name="id"]').map(function () {
		return jQuery(this).val();
	}).get().join();

	jQuery('#ids').val(ids);

}

var feeds = {
	ids: [],
	count: 0,
	imported_posts: 0,
	set: function(ids){
		this.ids = ids;
		this.count = ids.length;
	},
	get: function(){
		return this.ids.splice(0,1)[0];
	},
//	has: function(){
//		return !!this.ids.length;
//	},
	left: function(){
		return this.ids.length;
	},
	processed: function(){
		return this.count - this.ids.length;
	},
	total: function(){
		return this.count;
	},
	imported: function(num){
		if ( num !== undefined && !isNaN(parseInt(num)) ) this.imported_posts += parseInt(num);
		return this.imported_posts;
	}
};
